# Auditoría exhaustiva — Duplicados de `Persona` y sistema de emails

Fecha: 2026-09-22. Alcance: análisis únicamente, ningún cambio de código/BD aplicado. Investigación hecha por SSH de solo-lectura contra prod (`sigp`) + lectura de código en `/Users/agus/Projects/actividades` (rama `feat/reportes-problemas`).

---

## 1. Executive Summary

El sistema tiene duplicados reales de `Persona` (misma persona real, dos `idPersona`) generados por **al menos 4 mecanismos independientes**, no solo la race condition de `api/PersonasController::register` reportada inicialmente:

1. **Race condition estructural** en el patrón `SELECT (unique) → INSERT` — sin `UNIQUE` a nivel de BD en `Persona.mail`, existe desde 2019 y hoy se manifiesta casi exclusivamente vía mobile (3/3 casos nuevos desde que existe `registro_origen`).
2. **Bug concreto (no-race) en login social**, encontrado en esta auditoría: `UsuarioController::registrarPersona()` (usado por **web y mobile**) valida la unicidad del mail sobre el campo que manda el cliente, pero el mail que termina guardándose es el que llega del proveedor social — **si difieren, la validación no protege nada**, no hace falta ninguna concurrencia. Esto probablemente explica el caso real detectado (`gpedeamerica@gmail.com`: cuenta app + cuenta web con Google, 51 min de diferencia).
3. **Apple: bug estructural, no probabilístico.** Apple solo manda el mail la PRIMERA vez que un usuario autoriza la app; en cualquier login posterior no lo manda. `providerLogin` exige mail sí o sí → todo re-login con Apple después del primero falla y empuja al cliente hacia `/api/create`, que es exactamente el endpoint con el bug #2. Esto garantiza duplicados recurrentes para usuarios de "Continuar con Apple", no es una casualidad de timing.
4. **`CampanasController::convertir`** (conversión de leads de captación) no chequea si ya existe una `Persona` con ese mail — ni con concurrencia, sencillamente nunca. Bajo volumen (43 conversiones históricas) pero 100% reproducible.
5. **`UserService::crearUsuario`** (alta manual desde backoffice) excluye explícitamente las cuentas soft-deleted de su chequeo de unicidad (`...,deleted_at,NULL`) — un admin puede duplicar sin ninguna concurrencia.

El pool legado grande (4.308 grupos / 9.731 personas activas por mail duplicado) sigue congelado, como ya se había determinado. Pero al ampliar la búsqueda con DNI y con IDs de proveedor social (Google/Facebook/Apple) aparecieron señales mucho más fuertes y de mayor volumen que no se habían mirado antes (ver §2).

**Ya existe en el código un mecanismo de merge** (`Persona::fusionar()`, `app/Persona.php:339-376`, con UI en backoffice) — no hay que construirlo desde cero, hay que **extenderlo**: hoy sólo migra ~10 de las ~35 tablas que referencian `idPersona` encontradas en esta auditoría, y no está en una transacción.

---

## 2. Hallazgos confirmados vs. hipótesis

### Confirmado con evidencia directa (prod, vía SQL, o código leído línea por línea)

- 4.308 grupos / 9.731 personas activas comparten mail (excluyendo `mail=''`); estático desde hace 4 días (antes: 9.729).
- 3.266 personas activas tienen `mail=''` (secretarías de colegio, placeholders) — **no es el mismo problema**, hay que excluirlas de cualquier conteo/limpieza de "duplicados de identidad".
- DNI duplicado: 24.649 grupos crudos, pero mayormente basura de un campo de texto libre histórico (`ife`=7.062 filas, `INE`=2.767, `-`=2.571, `pasaporte`=2.146, `111111111`=873, etc.). Filtrando a valores numéricos plausibles (6-12 dígitos): **17.117 grupos / 37.943 personas** — sigue siendo ruido en su mayoría, ver §12.
- IDs de proveedor social duplicados en personas ACTIVAS (señal de alta confianza, un `google_id`/`facebook_id`/`apple_id` es único por diseño del proveedor): **google_id: 181 grupos** (de 64.249 personas con Google vinculado) · **facebook_id: 26 grupos** (de 3.201) · **apple_id: 6 grupos** (de 291).
- `Persona` no tiene NINGÚN `UNIQUE` a nivel de BD en `mail`, `dni`, `google_id`, `facebook_id`, `apple_id` ni `unsubscribe_token` — solo `KEY` no-únicas (y `google_id`/`facebook_id`/`apple_id` ni siquiera tienen índice, full-scan en cada login social sobre ~869k filas).
- Collation de `Persona` es `utf8mb4_unicode_ci` (case-insensitive) → `Juan@MAIL.com` y `juan@mail.com` YA se tratan como iguales en cualquier comparación/validación `unique:Persona,mail` actual. La variación de mayúsculas **no es** una vía de evasión hoy.
- Patrón race (mismo mail+nombre+apellido+DNI, ≤5s de diferencia): **65 pares desde 2019-02-05**. Por año: 2019=2, 2021=10, 2022=3, 2023=3, 2024=11, 2025=3, **2026=18 (parcial, ya el peor año)**.
- Desde que existe `registro_origen` (26-ago-2026): exactamente 3 pares nuevos con ese patrón, **3/3 tageados `app` en ambos lados, 0 `web`**.
- `CampanasController::convertir` no compara contra `Persona` existente antes de crear (línea 211-232 del archivo, confirmado leyendo el código completo); solo 43 conversiones históricas, 4 en 30 días.
- `Persona::fusionar()` ya existe y ya tiene UI (`backoffice/ajax/UsuariosController::fusionar`, ruta `POST /admin/usuarios/{persona}/fusionar`, gateada `role:admin`), pero cubre solo `Inscripcion`, `GrupoRolPersona`, `Actividad.idCoordinador/idPersonaCreacion/idPersonaModificacion`, `Coordinador`, `PuntoEncuentro`, `EvaluacionActividad`, `EvaluacionPersona` — **~25 tablas relevantes quedan afuera** (ver §12/§13 del mapa de dependencias, o el detalle completo en el hallazgo del agente de FKs).
- `password_resets` está keyado por `email` puro (columna de Laravel por defecto), **no por `idPersona`** — con mails duplicados, un pedido de reset puede aplicarse a la fila "equivocada" de las dos.
- `recibirMails`/`unsubscribe_token`/`email_verified_at` son por-fila, no por-mail — desuscribirse o verificar una de las cuentas duplicadas no afecta a la otra.

### Hipótesis razonable pero no 100% confirmable desde el código/BD solamente

- Que el "doble tap" o un retry automático del **cliente mobile** sea el mecanismo exacto que dispara la race condition — no tenemos acceso al código del cliente mobile (repo separado, no incluido en este proyecto). La evidencia server-side (3/3 casos recientes tageados `app`, gap de 0-1 segundo, mismo DNI/nombre exactos, distinto hash bcrypt) es consistente con esa hipótesis pero no la prueba de forma directa — igual de consistente sería un retry HTTP automático de la librería usada por el cliente, o un timeout que el usuario resuelve reintentando manualmente.
- Que el canal transaccional (Gmail SMTP) esté efectivamente siendo limitado/filtrado por Gmail — no hay bounce/complaint tracking habilitado (SES event publishing sigue apagado) para confirmarlo de punta a punta; ver diagnóstico previo en memoria del proyecto.

---

## 3. Todas las rutas que pueden crear/restaurar una Persona

| Ruta | Archivo:línea | Método | Origen/canal | Riesgo | Protección actual | Problema |
|---|---|---|---|---|---|---|
| `POST /api/register` | `app/Http/Controllers/api/PersonasController.php:204` | `register()` | Mobile, público | **Alto** | `unique:Persona,mail` (validación previa) | Race condition confirmada: SELECT-then-INSERT sin transacción ni lock. Sin UNIQUE en BD. |
| `POST /registro` (web) y `POST /api/create` (mobile) | `app/Http/Controllers/ajax/UsuarioController.php:154-179` (`registrarPersona()`, compartido por `create()` y `apiCreate()`) | `new Persona()->save()` | Web + Mobile, público | **Alto** | `unique:Persona,mail` — **pero solo si `$request->has('email')`** (línea 46) | Doble bug: (a) misma race SELECT-then-INSERT; (b) `aplicarSocialVerificado()` (línea ~137) **sobreescribe** `$persona->mail` con el mail verificado del proveedor social DESPUÉS de que la validación de unicidad ya corrió sobre el mail que mandó el cliente — si difieren, la unicidad no protege nada, sin necesidad de concurrencia. |
| `POST /admin/campanas/{id}/suscriptos/{id}/convertir` | `app/Http/Controllers/backoffice/ajax/CampanasController.php:198-247` | `convertir()` | Backoffice (admin, permiso) | **Alto** | Ninguna — cero chequeo de mail existente, ni siquiera secuencial | Duplica SIEMPRE si ya hay una Persona activa o borrada con ese mail. Transacción presente pero no protege esto (protege solo la atomicidad interna del propio insert). |
| `POST /admin/usuarios/registrar` | `app/Http/Services/UserService.php:17-23` (`crearUsuario()`) | `new Persona()->save()` | Backoffice (admin) | **Medio** | `unique:Persona,mail,...,idPersona,deleted_at,NULL` — **excluye explícitamente soft-deleted** | Un admin puede crear una Persona activa para un mail que ya pertenece a una cuenta borrada — sin ninguna concurrencia, es determinístico. |
| `POST /api/providerLogin` (Google/Facebook/Apple, cuenta ya existe) | `app/Http/Controllers/api/PersonasController.php:142-159` | vinculación automática de `google_id`/`facebook_id`/`apple_id` | Mobile | **Medio** (no crea fila nueva, pero vincula sin confirmación del usuario — inconsistente con la web) | Ninguna confirmación de usuario (a diferencia de la web) | No es un creador directo de duplicados, pero es la asimetría que hace que Apple/mobile terminen empujando al usuario hacia el bug de arriba. |
| Apple sign-in, 2do login en adelante | `app/Services/SocialAuth/AppleProvider.php:29-37` + `PersonasController.php:117-119` | `providerLogin()` exige mail, Apple no lo manda tras el 1er login | Mobile | **Alto (estructural, no probabilístico)** | Ninguna | Todo re-login con Apple falla (401) y empuja al cliente a `/api/create` → dispara el bug de arriba, de forma determinística, no ocasional. |
| `GET /auth/{google,facebook}/callback` (web, cuenta borrada) | `app/Http/Controllers/Auth/LoginController.php:195-201` | `$borrado->restore()` | Web | **Bajo-Medio** | Correcta (`onlyTrashed()->where('mail',...)`) pero SELECT-then-ACT sin lock | Race teórica de restore concurrente; no crea fila nueva. |
| `POST /api/providerLogin` (cuenta borrada) | `app/Http/Controllers/api/PersonasController.php:131-136` | `restore()` | Mobile | **Bajo-Medio** | Igual que arriba | Igual que arriba. |
| `POST /login`, `POST /api/login` (recuperación de cuenta borrada por contraseña) | `app/Persona.php:91-109` (`restaurarConCredencial`) | `restore()` | Web + Mobile | **Bajo-Medio** | SELECT-then-ACT | Igual patrón, menor severidad (requiere contraseña correcta). |
| `POST /password/email`, `/api/resetPassword`, `/api/password/reset` | `app/Http/Controllers/Auth/ForgotPasswordController.php:53` | `restore()` | Web + Mobile | **Bajo** | Throttled (6/min) + SELECT-then-ACT | Menor probabilidad por el throttle. |
| `POST /admin/usuarios/{id}/restaurar` | `app/Http/Controllers/backoffice/UsuariosController.php:110` | `restore()` por PK | Backoffice (admin, permiso) | **Bajo** | Acción manual sobre un `idPersona` específico | No es vector de duplicado (no crea fila, actúa sobre una fila puntual). |
| Seeders/factories (`UsuarioAdminSeeder`, `PersonaFactory`, etc.) | `database/seeds/*`, `database/factories/*` | `create()` | Artisan manual / tests | **Bajo** | N/A | Solo dev/CI, no alcanzable desde producción. |
| `routes/api.php:34` → `PersonasController@socialLogin` | — | — | Mobile | **N/A pero peligroso de dejar así** | — | El método fue **borrado del controller** (commit `c7c96089`) pero la ruta sigue registrada → hoy tira 500 (`BadMethodCallException`). Sacar la ruta muerta. |

---

## 4. Causas raíz, separadas por categoría

**Race conditions (SELECT-then-INSERT sin lock/transacción/constraint):** `api/PersonasController::register`, `ajax/UsuarioController::registrarPersona` (ambos canales), los 5 puntos de restore. Causa común: `Persona.mail` no tiene `UNIQUE` en BD desde que se creó la tabla (`database/migrations/2013_01_01_000002_create_Persona_table.php`), y ninguna migración posterior lo agregó.

**Doble submit / retry del cliente:** hipótesis para los 3 casos recientes tageados `app` (mismo DNI/nombre, gap de 0-1s). No verificable desde este repo (el cliente mobile vive en otro repo).

**Bug de validación en login social (no-race, determinístico):** `UsuarioController::registrarPersona()` valida unicidad sobre un campo que después se pisa con el mail real del proveedor. Éste es el hallazgo más nuevo y más accionable de esta auditoría — no depende de timing, depende de que el cliente no mande (o mande distinto) el campo `email` junto con el `provider`+`token`.

**Estructural de plataforma (Apple):** Apple solo entrega el email en el primer `authorize`; el diseño actual de `providerLogin` no contempla logins subsiguientes sin email, así que degrada siempre hacia el flujo de creación.

**Captación (secuencial, no race):** `CampanasController::convertir` — ausencia total de chequeo, no hace falta concurrencia.

**Admin (secuencial, no race):** `UserService::crearUsuario` excluye soft-deleted de su propio chequeo de unicidad.

**Jobs/imports/cron:** no se encontró ningún job, comando artisan, listener, observer o importador (Excel/CSV) que cree Personas en producción — la única superficie es HTTP (controllers), confirmado por búsqueda exhaustiva.

---

## 5. Impacto sobre emails

Separando expresamente los tres tipos de duplicación que pueden sumarse (no son la misma causa):

**(a) Duplicación por Persona:** dos `idPersona` con el mismo mail, cada uno con su propio historial de `Inscripcion`. El sistema no tiene ninguna noción de "estas dos filas son la misma bandeja" — cada una dispara sus propios mails de forma completamente independiente (`Mail::to($inscripcion->persona->mail)` resuelve por el dueño de la fila, no por el mail). Efecto: la misma persona real puede recibir 2 mails de bienvenida, 2 confirmaciones de una inscripción distinta cada una, etc. — percibido como "duplicados" pero en realidad son dos eventos de negocio distintos que casualmente caen en la misma bandeja.

**(b) Duplicación por evento/retry de infraestructura:** independiente de lo anterior. `BaseController::intentaEnviar()` (el helper usado por casi todos los envíos transaccionales) no tiene ningún guard de "ya se mandó este mail para este estado" — se vuelve a encolar cada vez que el código que lo llama se ejecuta. Confirmado sin protección en: `InscripcionesController::create()` (confirmación de inscripción), backoffice `ajax/InscripcionesController::update()`/`aprobarBeca()` (confirma/pago/beca), `PagosController::confirmation()` (webhook PayU — **sin ningún chequeo de "ya está pago"**, un retry del gateway reenvía el mail). Ya está bien resuelto (con guard explícito) en `StripeController` (`pago == 1 && metodo_pago === 'stripe'` antes de mandar). Sumado: `--tries=3 --delay=60` en el worker de colas puede reenviar un mail que en realidad ya salió, si la confirmación SMTP se pierde (mencionado explícitamente en un comentario del propio `supervisor-worker.conf` sobre fallas SSL de Gmail).

**(c) Duplicación específica SES vs Gmail:** no es duplicación de contenido, es un problema de canal — ya diagnosticado en una investigación previa de esta sesión: el canal `bulk` (SES) está sano; el canal `transaccional` (Gmail SMTP directo) es sospechoso de estar cerca de/superando límites de envío de una cuenta Gmail normal, sin visibilidad de bounce/queja.

**Otros efectos de la duplicación de Persona sobre el sistema de mail:**
- `password_resets` es por `email`, no por `idPersona` → un reset puede aplicarse a la fila equivocada de un par duplicado.
- `recibirMails`/`unsubscribe_token` son por fila → desuscribirse en una cuenta duplicada no calla a la otra.
- `email_verified_at` es por fila (correcto, no es confuso como el reset) — pero implica que una persona con 2 filas duplicadas debe verificar dos veces, sin que el sistema se lo explique.

---

## 6. Estrategia de prevención (qué debe cambiar, sin implementar todavía)

**Base de datos:**
- `UNIQUE` real en `Persona.mail`. Antes: 3.266 filas con `mail=''` + cientos de placeholders repetidos (`notiene@mail.com`×500, etc.) rompen un UNIQUE tal cual. Recomendación: la columna ya es case-insensitive por collation (`utf8mb4_unicode_ci`), así que no hace falta una columna normalizada aparte para eso — pero para permitir "sin mail" sin romper la unicidad, hace falta (a) volver la columna NULLABLE, (b) migrar `''` y los placeholders reconocibles a `NULL` (MySQL permite múltiples `NULL` bajo un índice `UNIQUE`), (c) recién ahí agregar el `UNIQUE`. Esto es un cambio de columna (`NOT NULL` → `NULL`), hay que revisar todo el código que asume `mail` no-nulo (`tieneMailValido()`, `scopeMailable()`, validaciones) antes de aplicarlo.
- Índices nuevos en `google_id`, `facebook_id`, `apple_id` (hoy sin índice — cada login social es full-scan sobre ~869k filas) y, sí, `UNIQUE` parcial-por-NULL en cada uno (incluida la misma consideración NULL-permite-repetir que en mail).
- `dni`: **no** recomendado un UNIQUE simple — hay colisión legítima entre países (mismo número de documento en distintos países) y el campo está lleno de basura histórica (ver §12). Si se quiere proteger, tendría que ser `UNIQUE(idPais, dni)` después de limpiar los valores placeholder, y es una decisión de producto (ver §14).

**Backend — el patrón correcto (no `if (!exists) create`):**
1. Normalizar (trim; el lowercase ya lo da la collation) el mail al recibirlo, en un único punto compartido (hoy hay 2+ implementaciones distintas de "crear Persona" con lógica de validación distinta — unificar es parte del fix).
2. Insertar directamente contra la BD (no chequear-antes-de-crear como único mecanismo).
3. Dejar que el `UNIQUE` constraint sea la fuente de verdad — capturar `QueryException` con código de MySQL `1062` (duplicate entry).
4. Ante duplicado: recuperar la Persona existente por mail y decidir la respuesta funcional (409 con mensaje "ya existe, iniciá sesión / recuperá tu contraseña" — ya existe el mensaje i18n `validation.custom.email.cuenta_existente`, reusar), **nunca** un 500 genérico.
5. Específicamente en `UsuarioController::registrarPersona()`: resolver primero cuál es el mail final (verificado por proveedor social si existe, si no el del request) y validar unicidad **sobre ese mismo valor final**, no sobre el del request antes de que se pise.
6. `CampanasController::convertir`: antes de `new Persona()`, buscar existente (usar el mismo helper cross-país + `withTrashed()` que ya usa `UsuariosController::esBusquedaCrossPais()`/`UsuariosSearch`) — si existe, vincular `Suscripciones.idPersona` a esa fila en vez de crear una nueva; si no, crear. Agregar `lockForUpdate()` sobre la fila de `Suscribe` para cerrar la ventana de doble-conversión del mismo lead.
7. `UserService::crearUsuario`: sacar el `,deleted_at,NULL` de la regla `unique` (que vuelva a contar soft-deleted, como ya se corrigió en el registro normal).
8. Apple: `providerLogin` no puede seguir exigiendo mail — para un login subsiguiente sin mail, identificar por `apple_id` (una vez que tenga índice) contra la fila que ya lo tiene guardado del primer login, sin pedir mail de nuevo.
9. Mobile `providerLogin` vinculando `google_id`/`facebook_id`/`apple_id` automáticamente sin confirmación (a diferencia de la web) — evaluar si se alinea con el flujo `linkear` de la web por consistencia, o si se documenta como decisión deliberada (el token del proveedor ya es prueba de dueño del mail, así que auto-linkear no es descabellado — pero la asimetría con la web debería ser una decisión explícita, no un accidente).
10. Sacar la ruta muerta `routes/api.php:34` (`socialLogin`, método ya no existe).

**Mobile / Web (mitigación UX, NO garantía de integridad — ver §9):** deshabilitar el botón de submit mientras la request está en vuelo; sin embargo esto reduce la frecuencia, no la elimina (el usuario puede cerrar y reabrir la app, el SO puede reintentar una request en background, etc.) — la garantía real la da el `UNIQUE` + manejo de conflicto del punto anterior.

**Jobs/emails:** agregar guard "ya se envió/ya está en este estado" antes de encolar en `InscripcionesController`, `backoffice/ajax/InscripcionesController` y `PagosController` (replicar el patrón que ya usa `StripeController`).

---

## 7. Estrategia de limpieza (detección, clasificación)

**Excluir primero, explícitamente, del pool de "duplicados de identidad":**
- `mail = ''` (3.266 filas) — dato faltante, no duplicado.
- Placeholders reconocidos por patrón (`notiene@%`, `noteien@%`, mails de secretarías institucionales con muchas personas detrás — `secundaria@...edu.ar` con decenas/cientos de personas legítimamente distintas).

**Señales de duplicado, de más a menos confiable:**

| Señal | Grupos (activos, filtrados de ruido) | Confianza |
|---|---|---|
| Mismo `google_id` / `facebook_id` / `apple_id` (proveedor ya garantiza unicidad) | 181 / 26 / 6 | **Muy alta** — el proveedor externo ya verificó que es la misma cuenta real. |
| Mismo mail + mismo DNI limpio (numérico 6-12 dígitos) exacto | 2.178 | **Alta** |
| Mismo mail + DNI con distancia de edición 1-2 (typo de dígito/transposición), mismo nombre/apellido | *(no cuantificado aún, ver muestra abajo)* | **Media-alta**, requiere revisión |
| Mismo mail + nombre/apellido iguales, DNI ausente en alguna de las dos filas | Parte de las 785 "sin DNI comparable" | **Media**, requiere revisión |
| Mismo mail, DNI **distinto** y limpio en ambas filas | 1.345 | **Bloquear fusión automática** — evidencia real de que suele ser un mail compartido (pareja, familia, secretaría), no la misma persona (ver muestra abajo) |
| Solo nombre/apellido similar, sin mail ni DNI ni ID social en común | — | **Posible coincidencia** — no tocar sin revisión humana, alto riesgo de falso positivo (nombres comunes) |

**Evidencia real de por qué hace falta la capa de "DNI con typo" (muestra de prod, grupos con mismo mail y DNI distinto):**
- `a_gzlifeisgood@hotmail.com`: DNI `11483864` vs `114830864` — el segundo tiene un `0` de más insertado en el medio → mismo DNI, error de tipeo → **duplicado probable**, no "distinto".
- `adrian85_trupa@hotmail.com`: mismo nombre completo, DNI `31634591` vs `31643591` (dígitos `34`/`43` transpuestos) → **duplicado probable**.
- `adrimantilla@gmail.com`: mismo nombre, DNI `95123256` vs `95132256` (`23`/`32` transpuestos) → **duplicado probable**.
- `adrianaguardiola@gmail.com`: mismo nombre, un DNI es `12345677` (placeholder tipo "12345678") → **duplicado con basura en un lado**, no persona distinta.
- Contraejemplo real en el mismo muestreo — `aagustindiazc@hotmail.com`: "Ignacio Diaz" DNI `34290961` vs "Agustín Díaz Cafferata" DNI `92482801` → nombres y documentos genuinamente distintos → **mail compartido, no fusionar**.

Conclusión metodológica: ni "mismo mail" solo, ni "mismo DNI exacto" solo, alcanzan. La clasificación automática necesita, como mínimo: mail + (DNI exacto O DNI a distancia de edición ≤2 CON nombre/apellido iguales o muy similares) para la categoría "segura/probable"; cualquier par con DNIs limpios y claramente distintos (y nombres distintos) debe quedar **excluido** de la fusión automática.

**Metodología ampliada pedida (teléfono, fecha de nacimiento, equipos/grupos en común, historial):** son señales de refuerzo, no de decisión por sí solas — usarlas para subir/bajar de categoría un par ya candidateado por mail+DNI+nombre, no como criterio de arranque (combinarlas desde cero multiplicaría falsos positivos sobre 800k+ filas). No se cuantificó esto en el alcance de esta auditoría; queda como trabajo de la Fase 1 (§9).

---

## 8. Riesgos del merge

- **Constraints únicas que pueden colisionar al reasignar `idPersona`** (rows que ambas cuentas duplicadas ya tienen independientemente para la misma clave secundaria) — hay que resolver el conflicto ANTES del `UPDATE`, no dejar que la BD lo rechace a mitad de camino:
  - `EvaluacionImpactoActividad`: `UNIQUE(idActividad, idPersona)` — dos evaluaciones de impacto para la misma actividad.
  - `stripe_customers`: `UNIQUE(person_id)` — **conflicto real de negocio**, no solo de BD: si ambas cuentas duplicadas tienen su propio cliente de Stripe, hay que decidir cuál conservar y probablemente coordinar del lado de Stripe también (no es solo un `UPDATE`).
  - `listado_preferencias`: `UNIQUE(persona_id, list_key, context_id)`.
  - `persona_paises_permitidos`: `UNIQUE(idPersona, idPais)`.
  - `equipo_reunion_persona`: PK compuesta `(idReunion, idPersona)`.
  - Sin UNIQUE pero con riesgo lógico: `Inscripcion` (ambas cuentas pueden tener su propia inscripción a la MISMA actividad — hay que decidir cuál priorizar, ej. la pagada/confirmada sobre la pendiente) y `EvaluacionPersona`.
- **Cascadas de borrado ya configuradas** en 9 tablas (`Jornada`, `coordinadores_comunidad`, `Coordinadores` ×2 constraints redundantes, `equipo_reunion_persona`, `Integrantes`, `EvaluacionImpactoActividad`, `Dispositivo`, `listado_preferencias`, `listado_vistas`) — si el proceso de merge borra la Persona perdedora ANTES de reasignar esas tablas, esos datos se pierden en cascada, silenciosamente. El orden importa: reasignar primero, borrar (soft) después.
- **`Persona::fusionar()` no está en una transacción** — si falla a la mitad (por ejemplo, por una de las colisiones de UNIQUE de arriba), deja el merge a medio hacer: algunas tablas ya apuntan al sobreviviente, otras no, y la Persona perdedora todavía no está borrada. Hay que envolver todo en `DB::transaction()`.
- **`reporting_person` no se limpia solo** — `SyncPersonKeys` únicamente inserta filas faltantes, nunca borra; sin limpieza explícita, la Persona perdedora queda huérfana para siempre en la capa de reporting/Power BI.
- **Tokens de Passport (`oauth_access_tokens`, etc.) de la cuenta perdedora** — más seguro revocarlos (Passport ya lo soporta) que reasignarlos silenciosamente; si el usuario tenía una sesión mobile activa en la cuenta perdedora, reasignar el token sin avisar puede ser confuso o inseguro.
- **Conflictos de valores concretos, campo por campo** (no hay regla universal, hay que clasificar):

| Campo | Regla propuesta |
|---|---|
| `password` | Conservar la de la cuenta con **login más reciente** (`ultimo_acceso_app`/uso real) — no la más nueva por `created_at`. |
| `nombres`/`apellidoPaterno`/`dni`/`telefono` | Preferir el valor **no-placeholder y más reciente**; si ambos son válidos y distintos → marcar para revisión humana, no adivinar. |
| `email_verified_at` | Tomar el que esté seteado (si solo uno lo está); si ambos → el más antiguo (primera verificación real). |
| `google_id`/`facebook_id`/`apple_id` | Combinar — llevar todos los que tenga cada fila al sobreviviente (no se pisan entre sí, son columnas distintas). |
| `recibirMails`/`acepta_marketing` | Si CUALQUIERA de las dos dice "no" → el sobreviviente queda en "no" (preferir la preferencia más restrictiva, nunca reactivar mails que alguien cortó). |
| `estadoPersona` | Requiere criterio funcional del equipo de producto — no es un dato technical-only (ver §14). |
| Roles/permisos (Spatie) | Unión de ambos conjuntos, no reemplazo. |
| Relaciones (`Inscripcion`, `Integrante`, etc.) | Reasignar todas al sobreviviente; de las que colisionan por UNIQUE, conservar la más "avanzada" en el flujo de negocio (pagada > confirmada > pendiente), marcar la descartada para revisión si hay ambigüedad. |

**Regla de supervivencia (no "más viejo" ni "más nuevo" per se):** puntaje compuesto — cuenta con más actividad real (`ultimo_acceso_app`, cantidad de `Inscripcion`/`Integrante`), con mail verificado, con relaciones no triviales (roles de coordinador, actividades creadas) pesa más que una cuenta "cascarón" (creada y nunca más usada, típico de la propia race condition: la fila duplicada suele ser justamente la que el usuario nunca volvió a tocar porque ya venía usando la otra). Si el puntaje empata o hay señales contradictorias (ej. la cuenta "menos activa" tiene el rol de coordinador) → **marcar para revisión humana**, no decidir en automático.

---

## 9. Migración / rollout — orden recomendado

El orden que planteaste en tu mensaje es correcto en general, con dos ajustes:

1. **Protección inmediata (Fase 0)** — antes que nada: el fix del bug de `registrarPersona()` (validar sobre el mail final, no el del request) y el guard de `CampanasController::convertir` no dependen de tener la limpieza histórica lista, y son la fuente de duplicados NUEVOS de mayor severidad encontrada. Esto va primero, en paralelo con el diagnóstico, no después.
2. **Backup no es una etapa aislada** — debe ser una condición previa a TODA operación destructiva desde el minuto uno (dump/snapshot antes de cualquier dry-run que vaya a promoverse a real, no solo antes de la Fase 5). Lo dejaría como una precondición transversal, no como el paso 4 de una secuencia lineal.

Con esos dos ajustes, tu secuencia (detección → clasificación → dry-run → [backup como precondición permanente] → merge automático de los inequívocos → revisión manual de los ambiguos → constraint → monitoreo) es el orden correcto. Ver el detalle fase-por-fase en §13.

---

## 10. Tests — matriz

| Área | Caso | Resultado esperado |
|---|---|---|
| Concurrencia | 2 requests simultáneos a `/api/register` con mismo mail | 1 sola `Persona` creada; la segunda request recibe una respuesta funcional (409/422 con mensaje), nunca un 500 ni una segunda fila |
| Concurrencia | 2 requests simultáneos a `/registro` (web) y `/api/create` (mobile) con mismo mail, uno de cada canal | Igual que arriba — cubre el bug compartido entre ambos canales |
| Mobile | Doble tap / doble submit del formulario de registro (2 requests idénticas, sin gap artificial) | 1 sola Persona |
| Mobile | Retry tras timeout de red (request original SÍ llegó al server, cliente reintenta) | 1 sola Persona, segunda respuesta informa "ya existe" en vez de error genérico |
| Login social — bug encontrado | `POST /api/create` con `provider`+`token` de Google pero SIN campo `email`, o con un `email` que no coincide con el del token, cuando YA existe una Persona activa con el mail real del token | Debe detectar el mail real (post-`aplicarSocialVerificado`) y rechazar/vincular contra la existente, NO crear una segunda fila — este es el test que hoy fallaría |
| Login social — Apple | Segundo login de un usuario con Apple (token sin email) | Debe encontrar la Persona por `apple_id`, no exigir mail ni caer a creación |
| Login social | Mail existente + proveedor nuevo (primera vez que usa Google en una cuenta que se registró por mail/password) | Web: pasa por `linkear` con confirmación explícita. Mobile: define si auto-linkea (documentar la decisión, testear que hace lo que se decidió) |
| Login social | Proveedor ya vinculado a una Persona pero el mail que reporta ahora es distinto (usuario cambió su mail en Google) | Definir comportamiento esperado (hoy: no se encuentra nada, cae a creación — test debería fijar el comportamiento correcto una vez decidido) |
| Captación | `convertir()` sobre un lead cuyo mail ya tiene una Persona activa | Debe vincular `Suscripciones.idPersona` a la existente, no crear una nueva |
| Captación | 2 conversiones simultáneas del mismo `Suscribe` (mismo `$suscripcionId`) | 1 sola Persona, 1 sola actualización de `convertido` |
| Admin | `UserService::crearUsuario` con mail de una cuenta soft-deleted | Debe rechazar o restaurar, no crear una segunda fila activa |
| Emails | Mismo evento de negocio disparado 2 veces (doble submit de confirmación de inscripción) | 1 solo mail encolado, no 2 |
| Emails | Retry de un job de mail (`--tries=3`) cuando el mail YA salió pero el ack se perdió | No debería reenviar (requiere agregar idempotencia — hoy reenviaría) |
| Emails | Dos `Persona` duplicadas, cada una con su propia `Inscripcion` a la misma actividad | Documentar que hoy son 2 mails reales (2 eventos de negocio distintos) — no es un bug de mail, es consecuencia de la Persona duplicada |
| Merge | Par con colisión de `UNIQUE` (ej. ambas tienen `stripe_customers`) | El merge debe fallar de forma controlada o resolver el conflicto explícitamente, nunca dejar el `UPDATE` a mitad de camino |
| Merge | Falla a mitad del merge (excepción simulada en la tabla N de 35) | Rollback completo vía transacción — 0 tablas modificadas, Persona perdedora sigue activa |

---

## 11. Monitoreo — qué medir después del fix

Queries/controles periódicos (candidatos a comando Artisan + alerta):

- `mail` duplicados NUEVOS por semana (excluyendo el pool legado ya conocido) — debería caer a ~0 apenas esté el `UNIQUE`; si el `UNIQUE` está y sigue habiendo intentos, contar los 1062/duplicate-entry capturados como métrica de "intentos de duplicado evitados" (útil para saber si el bug del cliente sigue existiendo aunque ya no genere filas).
- Pares con `google_id`/`facebook_id`/`apple_id` duplicado — debería ser HOY mismo (con índice) y quedar en 0 estable.
- `registro_origen` cruzado con "creado en los últimos N segundos de otra fila con mismo mail" — la query exacta que ya se usó en esta auditoría, como comando programado.
- Tasa de 409/422 por mail duplicado en `/api/register`, `/api/create`, `/registro` — si sube de golpe, señal de que el cliente (mobile o web) está reintentando de más.
- `failed_jobs` por Mailable, agrupado por tipo — ya existe `mail:resumen-envios`, extenderlo para que muestre también "mismo Inscripcion, mismo Mailable, más de un envío en <X minutos>" como proxy de reenvío duplicado.
- Conteo de filas en `reporting_person` sin `Persona` activa correspondiente (huérfanas post-merge).

**Observabilidad a nivel de request (para poder responder "¿por qué esta Persona se creó dos veces?" sin arqueología):** falta casi todo hoy. Agregar, en el punto único de creación una vez unificado (§6): un `request_id`/correlation id por request, IP, user agent, resultado (creado/rechazado-por-duplicado/error), y loguear explícitamente cuando la validación de mail y el mail final persistido difieren (eso hubiera detectado el bug de login social inmediatamente).

---

## 12. Archivos a modificar (cuando se pase a implementación — ninguno tocado en esta auditoría)

- `app/Http/Controllers/api/PersonasController.php` (register, providerLogin, Apple)
- `app/Http/Controllers/ajax/UsuarioController.php` (registrarPersona, cargar_cambios, aplicarSocialVerificado, validar)
- `app/Http/Requests/CrearPersona.php`
- `app/Http/Controllers/backoffice/ajax/CampanasController.php` (convertir)
- `app/Http/Services/UserService.php` (createValidator/crearUsuario)
- `app/Services/SocialAuth/AppleProvider.php`
- `app/Http/Controllers/Auth/LoginController.php` (por consistencia con mobile, si se decide alinear)
- `app/Persona.php` (fusionar — extender a las ~25 tablas faltantes, envolver en transacción, agregar métodos de normalización de mail)
- `app/Http/Controllers/backoffice/ajax/UsuariosController.php` (fusionar — auditoría universal, no solo cross-país)
- `app/Http/Controllers/InscripcionesController.php`, `backoffice/ajax/InscripcionesController.php`, `PagosController.php` (guard de idempotencia de mail)
- `routes/api.php` (sacar la ruta muerta de `socialLogin`)
- Nueva migración: `mail`/`google_id`/`facebook_id`/`apple_id` nullable + backfill de placeholders a NULL + índices + UNIQUE
- Nuevo comando Artisan de detección/clasificación de duplicados (Fase 1-2 de §13)
- Nuevo comando Artisan de merge en lote para duplicados "seguros" (Fase 5)
- Tests nuevos según §10 (`tests/Feature/...`)

---

## 13. Plan de implementación por fases

| Fase | Qué | Prioridad | Riesgo | Depende de | Requiere migración | Requiere deploy coordinado | Cómo verificar | Rollback |
|---|---|---|---|---|---|---|---|---|
| 0 — Protección inmediata ✅ HECHO (2026-09-23) | Fix `registrarPersona()` (validar mail final), guard+lock en `CampanasController::convertir` (vincula/restaura en vez de duplicar), excluir soft-deleted en `UserService`, sacar ruta muerta `socialLogin`. 6 tests nuevos/actualizados (`LoginSocialTest`, `CampanasConvertirTest`, `AdministrarUsuariosTest`) — suite completa 355/356 en verde (la única falla, `InscripcionesConPagoTest::plataforma_reenvia_a_pagina_fecha_limite_vencida`, es preexistente y no relacionada, confirmado con `git stash`). | **Crítica** | Bajo (son fixes acotados, testeables) | — | No | No | Tests nuevos de §10 + repetir el query de "pares creados en <5s" y confirmar que no crecen más | Revert simple del commit |
| 1 — Diagnóstico definitivo | Comando Artisan que corre las queries de clasificación de §7/§12 y las deja en una tabla de "candidatos a fusión" con su categoría | Alta | Bajo (solo lectura) | — | Sí (tabla nueva `persona_merge_candidatos` o similar) | No | Revisar manualmente una muestra de cada categoría | Borrar la tabla, no toca `Persona` |
| 2 — Clasificación | Afinar el algoritmo (distancia de edición en DNI, nombre similar) sobre los candidatos de la Fase 1 | Alta | Bajo | Fase 1 | No | No | Revisión humana de una muestra por categoría | — |
| 3 — Extender `fusionar()` | Cubrir las ~25 tablas faltantes, envolver en transacción, resolver colisiones de UNIQUE, limpiar `reporting_person`, revocar tokens Passport | Alta | Medio (toca ~35 tablas) | Mapa de dependencias (ya hecho en esta auditoría) | No (usa el `fusionar()` existente) | No | Test de merge completo en sandbox contra una copia de un par real | Transacción → rollback automático si falla a mitad |
| 4 — Dry-run | Correr el merge en modo "mostrar qué haría" sobre TODOS los candidatos "seguros" (Fase 2), sin escribir nada | Alta | Ninguno (no escribe) | Fase 3 | No | No | Revisar el reporte generado | N/A |
| 5 — Backup | Dump/snapshot de `Persona` + las ~35 tablas dependientes, ANTES de correr el merge real (no es una fase secuencial, es una condición) | Crítica | — | — | No | No | Verificar que el dump restaura en sandbox | Restaurar el dump |
| 6 — Merge automático | Correr el merge real SOLO sobre "duplicado seguro" (google_id/facebook_id/apple_id compartido + mail+DNI exacto) | Alta | Medio-alto (escribe en producción) | Fases 3, 4, 5 | No | Sí (ventana de mantenimiento corta recomendada, o correr fuera de horario pico) | Re-correr la Fase 1 y confirmar que esos pares desaparecieron sin generar huérfanos | Restaurar backup de la Fase 5 |
| 7 — Revisión manual | UI/proceso para que un humano revise "duplicado probable" y "posible coincidencia" uno por uno, usando el `fusionar()` ya existente en backoffice (extendido en Fase 3) | Media | Bajo (decisión humana caso a caso) | Fase 6 | No | No | — | Igual que cualquier merge individual |
| 8 — Constraint | Migración: nullable + backfill de placeholders a NULL + índices + `UNIQUE` en `mail`/`google_id`/`facebook_id`/`apple_id` | Crítica | Medio (bloquea altas si algo no se limpió bien) | Fases 6 y 7 completas (0 duplicados activos restantes) | Sí | Sí | Intentar un insert duplicado en staging, confirmar que falla con 1062 y que el flujo normal de registro sigue funcionando | Quitar el índice (no hay pérdida de datos en el rollback de un UNIQUE) |
| 9 — Backend definitivo | Reemplazar el patrón `exists→create` por `create→catch(1062)` en todos los puntos de §3, unificar validación | Alta | Medio | Fase 8 | No | Sí | Tests de concurrencia de §10 en CI | Revert del commit, el UNIQUE de BD sigue protegiendo aunque el manejo de error vuelva al anterior (peor UX, no vuelve el duplicado) |
| 10 — Apple / social | Fix de `AppleProvider`/`providerLogin` para no depender de mail en logins subsiguientes; decidir y alinear la política de auto-linkear mobile vs confirmación web | Alta | Medio (requiere coordinar con el cliente mobile) | — | No | Sí, coordinado con el equipo mobile | Test de "segundo login con Apple" | Revert |
| 11 — Emails idempotentes | Guard de "ya enviado/ya en este estado" en `InscripcionesController`, backoffice ajax, `PagosController` | Media | Bajo | — | No | No | Tests de §10 | Revert |
| 12 — Monitoreo | Comandos/alertas de §11 | Media | Bajo | Fase 8 | Posiblemente (tabla de métricas) | No | — | — |

---

## 14. Preguntas / decisiones que necesito tomar con el equipo de producto (no técnicas)

1. **¿El mail debe ser único GLOBALMENTE (todos los países) o por país?** La evidencia del código (tabla separada `persona_paises_permitidos` para permisos, más el histórico de bugs de "costura país") sugiere que la identidad (`Persona`) ya está pensada como global y el país es solo un permiso — pero es una definición de producto, no algo que deba asumir yo. Si la respuesta fuera "por país", el diseño del UNIQUE cambia (`UNIQUE(idPais, mail)` en vez de `UNIQUE(mail)`).
2. **¿Qué hacemos con `dni`?** ¿Vale la pena limpiarlo/normalizarlo (hay ~17k grupos "duplicados" que son mayormente basura de un campo de texto libre histórico) y eventualmente restringirlo por país, o se deja como dato informativo sin garantía de unicidad? Esto no bloquea el fix de `mail` pero es un problema de calidad de datos igual de grande que se detectó de paso.
3. **Auto-linkear en mobile vs. confirmación en web (login social):** ¿mantenemos la asimetría (mobile confía en el token del proveedor y vincula solo, web pide confirmación explícita) o se unifica un solo comportamiento? Tiene implicancia de seguridad (posible confusión de cuenta) y de UX.
4. **Criterio de `estadoPersona` al fusionar:** cuando las dos cuentas duplicadas tienen estados de negocio distintos (ej. una "activo" y otra con algún estado especial), ¿cuál gana? Es una regla de negocio, no técnica.
5. **Alcance de la Fase 7 (revisión manual):** ¿quién la hace — un rol específico de backoffice, o el mismo equipo técnico? Afecta si hace falta UI nueva o alcanza con extender la pantalla de fusión que ya existe.
6. **Ventana de mantenimiento para la Fase 6 (merge automático) y la Fase 8 (constraint):** ¿se puede coordinar un horario de bajo tráfico, o tiene que ser 100% online?
