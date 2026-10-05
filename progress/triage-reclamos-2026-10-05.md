# Triage de reclamos de producción — 2026-10-05

> Auditoría + triage + planificación + respuestas propuestas. **No se modificó código, datos,
> estados ni `tasks.json`; no se publicó ninguna respuesta.**
> Fuente: tabla `issue_reports` + `issue_report_replies` de prod (`sigp`, HEAD `b9644f9`, rama
> `feat/reportes-problemas`), consultas de solo lectura vía `ssh techo-actividades`, logs
> `storage/logs/laravel-*.log` (retención: solo hay desde 2026-09-29) y el código del HEAD.
> Datos estructurados de este triage (para automatizar): `progress/triage-reclamos-2026-10-05.json`.

**Limitaciones de esta corrida**
- **No pude ver las capturas** (`storage/app/bug_reports/*`, 12 de los 14 reclamos tienen): la descarga
  fue bloqueada por permisos (PII). Varios diagnósticos mejorarían mirándolas desde la bandeja
  `/admin/reportes` (sobre todo #5, #8, #12, #14).
- Logs de Laravel solo desde 2026-09-29 → no hay rastro server-side de #3, #4, #5, #6 (y los 403/404
  no se loguean). nginx no es legible por `techo`.
- Ningún reclamo abierto tiene comentarios: los 2 únicos `issue_report_replies` son pruebas de Agus
  sobre el #1 (ya descartado). **Nadie respondió todavía a ningún usuario.**
- Todos los reclamos (salvo #16) se crearon con `release=5f5871e`; entre ese release y prod hay 7
  commits (`49512cd2`…`b9644f9`), relevantes: `269fc354` (403/404 ya no se ven como 500 + link de
  pago con otra cuenta) y `b6dda839` (duplicados de cuentas).

---

## A. Resumen ejecutivo

| Métrica | Cantidad | Reclamos |
|---|---|---|
| Reclamos analizados (abiertos, `status=nuevo`) | **14** | #3–#16 (#1 y #2 ya `descartado`, fuera de alcance) |
| Resueltos / caso puntual ya destrabado | **3** | #6 (`RESUELTO_RECIENTEMENTE`, a confirmar), #13 y #16 (caso resuelto a mano por la propia admin; queda causa estructural) |
| Requieren implementación | **5** | #3, #7, #8, #11, #15 |
| Requieren investigación | **1** | #5 |
| Necesitan información del usuario | **3** (+1 confirmación) | #5/#12, #14 (+ #6 confirmar) |
| Sugerencias de producto | **3** | #4, #9, #10 |
| Duplicados | **1** (+1 misma causa) | #12 ≈ #5; #7 comparte causa con #11 |
| **Tareas técnicas reales** | **7** (1 bloqueada por aclaración) | T1–T7 (sección E) |

**Lo más importante que salió del triage (no estaba en ningún reclamo literal):**
1. **Integridad de grupos rota a escala** (T1): `GruposActividadesController::delete()` borra la
   membresía `Grupo_Persona` de una persona **en todas sus actividades** (falta filtro por actividad),
   y el grupo raíz se resuelve **por nombre**, lo que crea raíces duplicadas en el **51% de las
   actividades desde junio** (1.504/2.962). Esto explica directamente #7 y #11 (66 errores 500 en 4
   días en `GruposController@incluirInscripto`) y degrada las evaluaciones (#5/#12).
2. **Una cuenta "eliminada" sigue pudiendo loguearse e inscribirse** (T4, #15): la anonimización deja
   `google_id`; hay casos de auto-inscripción días después de borrar la cuenta. Es tema de privacidad.
3. Hallazgos fuera de alcance (sección G), incluido uno que **bloquea el propio flujo de respuestas
   de Techita**: `Call to undefined method App\Jobs\EnviarMailTransaccionalSes::dispatch()` hoy en prod.

---

## B. Matriz de reclamos

| ID | Título (descripción) | Fecha | Tipo | Estado | Diagnóstico | Clasificación | Evidencia | Cambios recientes | ¿Código? | ¿Info usuario? | Acción | Task | Respuesta |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 3 | No exporta a Excel en vista por sede (Salta) | 09-24 | bug | nuevo | El botón arma `/admin/actividades/oficina/15/exportar` → **ruta inexistente (404)**; además el export no filtra oficina | REQUIERE_IMPLEMENTACION | `FilterBar.vue:44` + `routes/web.php:440,446` | `5ace9f08` (vista por oficina, sin export) — causa | Sí | No | Implementar T2 | T2 | Sí |
| 4 | Papelera de actividades eliminadas | 09-25 | suggestion | nuevo | No existe restore; al borrar se pierden grupos (borrado físico) y se manda mail de cancelación | SUGERENCIA_PRODUCTO | `ActividadesController.php:396-420` | — | Sí (si se aprueba) | No | Backlog producto | — | Sí |
| 5 | Link de evaluación lleva a "inscripción" | 09-25 | bug | nuevo | Varias causas parciales; ninguna ruta server redirige a inscripción. Probable: sin sesión → `/login` = listado de actividades + redirect post-login roto. 403 se veía como 500 (ya corregido) | REQUIERE_INVESTIGACION | Policy `evaluar`, `Handler.php` after_login, datos de presentes | `269fc354` corrige 403→500 | Parcial | **Sí** | Pedir ejemplos + T7 | T7 (+T1) | Sí |
| 6 | Botón azul del mail de confirmación → "error 505" (MX) | 09-26 | bug | nuevo | Botón "CONFIRMAR MI LUGAR" → `/confirmar/donacion`. Actividad Abierta y config de pago OK → caso más probable: **logueado con otra cuenta** → 500, **corregido el 10-03** | RESUELTO_RECIENTEMENTE (confirmar) | 497 confirmadas, 122 pagas (el flujo funciona); 5 inscriptos con mail repetido en otra cuenta | `269fc354` | No | Confirmar | Responder + pedir ejemplo si persiste | — | Sí |
| 7 | No puedo agregar gente de la oficina (ni a mí) a evaluaciones | 09-29 | bug | nuevo | Misma causa que #11 (staff con muchas actividades queda sin `Grupo_Persona`) + /grupos solo opera con inscriptos (form "no inscripto" sin botón desde 2019) | REQUIERE_IMPLEMENTACION | 24 errores `Undefined variable: grupo` de la reportante el 09-29 | ninguno | Sí | No | T1 + workaround | T1 | Sí |
| 8 | "Inscripciones Cerradas" cuando están abiertas (CR) | 09-29 | bug | nuevo | Etiqueta calculada solo en `mounted()`; vuetable reutiliza el componente por índice → **etiqueta congelada** al paginar/filtrar | REQUIERE_IMPLEMENTACION | `estadoActividad.vue:64`, `Vuetable.vue:76` | ninguno | Sí | No (opcional) | T3 | T3 | Sí |
| 9 | Autoasignar roles postulados y confirmar | 09-29 | suggestion | nuevo | `roles_aplicados` (multi) se guarda pero no se muestra en el listado; `rol` (único) es el confirmado | SUGERENCIA_PRODUCTO | `InscripcionesController.php:131-173`, `config/datatables.php:315` | — | Sí (si se aprueba) | No | Backlog producto | — | Sí |
| 10 | "Monitor" → "Capataz" en Costa Rica | 09-29 | suggestion | nuevo | Texto fijo en lang; CR usa locale `es_AR` en prod → cambiarlo afecta Argentina | SUGERENCIA_PRODUCTO | `lang/es_AR/backend.php:649`, `atl_pais.locale` | — | Sí (si se aprueba) | No | Decisión de producto | — | Sí |
| 11 | No me deja agregar muchos voluntarios para evaluaciones | 09-30 | bug | nuevo | Inscriptos sin fila `Grupo_Persona` → `incluirInscripto` tira 500 silencioso. 9/112 en la actividad; huérfanos por borrado cross-actividad | REQUIERE_IMPLEMENTACION | 66 errores 500 (09-29→10-02); patrón 17/21, 5/5 actividades huérfanas por persona | ninguno | Sí + reparación de datos | No | T1 + workaround | T1 | Sí |
| 12 | Link de evaluación: a algunos funciona y a otros no | 10-01 | bug | nuevo | Mismo síntoma que #5 (sin id de actividad: reportado desde `/admin/reportes`) | DUPLICADO (#5) + ACLARACION_NECESARIA | — | `269fc354` | Parcial | **Sí** | Pedir actividad + ejemplos | T7 (+T1) | Sí |
| 13 | Voluntario de Alemania no puede registrarse (sin provincia/localidad) | 10-01 | bug | nuevo | Alemania no tiene provincias cargadas (222/241 países no tienen). La admin le cambió el país a Perú y **se inscribió 8 min después** | RESUELTO (caso) + mejora estructural | auditoría 17:09; inscripción 17:17 | ninguno | Sí (menor) | No | Responder + T5 | T5 | Sí |
| 14 | Cantidad de inscripciones de las personas no es correcta | 10-01 | bug | nuevo | Hay 3 métricas distintas que no coinciden; la de "cantidad de actividades" cuenta bajas y actividades borradas (bug confirmado). No sabemos cuál mira | ACLARACION_NECESARIA | 4 inscriptos; los 4 tienen inscripciones dadas de baja | ninguno | Probable | **Sí** | Pedir qué número/persona | T6 (bloqueada) | Sí |
| 15 | Aparece inscripto un "usuario eliminado" | 10-02 | bug | nuevo | La cuenta se auto-eliminó el 09-27 y **se auto-inscribió el 10-02**: la anonimización no corta el login (conserva `google_id`) | REQUIERE_IMPLEMENTACION | auditoría: creador = la propia persona; 3+ casos similares | ninguno | Sí | No | T4 | T4 | Sí |
| 16 | Voluntaria no aparece por nombre ni para equipo (MX) | 10-05 | bug | nuevo | Tenía `idPais=241` ("Latam") → invisible para búsquedas por país; por mail sí (cross-país). La admin le corrigió el país a las 14:23 | RESUELTO (caso) + estructural | auditoría 14:13/14:23 | ninguno | Sí | No | Responder + T5 | T5 | Sí |

---

## C. Análisis individual

### #3 — Exportar Excel en la vista por sede (Martina, admin AR, `/admin/actividades/oficina/15` = Salta)

- **Qué reportó:** desde que hay desplegable por sedes no puede exportar el historial de actividades de Salta.
- **Qué pide:** un Excel con las actividades de su sede.
- **Código:** el botón "Exportar a Excel" (`resources/js/components/backoffice/datatable/FilterBar.vue:44`)
  hace `location.href + '/exportar?filter=…'`. En `/admin/actividades` funciona (`routes/web.php:446`),
  pero en `/admin/actividades/oficina/15` genera `/admin/actividades/oficina/15/exportar`, **ruta que no
  existe → 404**. Aunque existiera, `ReportController::exportarActividades` no pasa la oficina a
  `ActividadesExport` (el filtro de oficina solo lo usa el listado ajax). Bug adicional:
  `ActividadesExport::headings()` tiene 9 columnas y `map()` devuelve 12 en otro orden (columnas
  corridas, "País" vacío). `MisActividadesExport` igual.
- **Git:** `5ace9f08` (filtro por oficina) agregó la vista y la ruta ajax pero no la de export → el
  reclamo es **regresión directa** de ese commit.
- **Estado real:** sigue ocurriendo en prod.
- **Acción:** T2. Workaround inmediato: exportar desde `/admin/actividades` (trae todo el país) y filtrar
  Salta en el Excel — con la salvedad de que las columnas están desalineadas.
- **Info faltante:** ninguna.

### #4 — Papelera de actividades (Jimena, coordinadora MX)

- **Pide:** poder recuperar actividades eliminadas.
- **Código:** `Actividad` tiene SoftDeletes, pero no hay ruta/vista de restore. Precedente:
  restaurar usuarios (`24b92a44`). Al borrar (`backoffice/ActividadesController.php:396-420`): grupos y
  `Grupo_Persona` se **borran físicamente**, se envía **mail + push de cancelación** a inscriptos, y no
  queda registro de quién borró. Cualquier coordinadora con `borrar_actividad` puede borrar.
- **Diagnóstico:** sugerencia válida; un restore "completo" no es trivial (grupos perdidos, mails ya
  enviados). Hoy un admin técnico podría restaurar una actividad puntual a mano (soft delete), sin grupos.
- **Acción:** backlog de producto. Quick win sugerido: confirmación más fuerte al borrar + registrar
  quién borró. No crear task hasta decisión de producto.

### #5 y #12 — Link de evaluación "lleva a la inscripción" / "a algunos sí, a otros no"

- **Reportaron:** Luisina (#5, act. 40484 La Plata) y Albertina (#12, sin actividad: reportó desde
  `/admin/reportes`).
- **Qué piden:** que todos los voluntarios que participaron puedan abrir y completar la evaluación.
- **Código (flujo real):** el link es `/actividades/{id}/evaluaciones` (mail `invitacionEvaluacion`, botón
  "Copiar link" para WhatsApp, y push). La ruta exige `auth` + policy `evaluar`
  (`ActividadesPolicy.php:37-48`): inscripción viva con `presente=1` y `fechaInicioEvaluaciones <= now`.
  **Ninguna ruta del servidor redirige a la inscripción.** Lo que sí ocurre:
  1. **Sin sesión** → `/login`, que es el **listado de actividades con el modal de login** (lo que un
     voluntario puede leer como "me mandó a inscribirme"). Tras loguearse, `after_login_url` toma el
     **Referer** si existe (p.ej. el webmail) y la cookie vence a los 10 min → muchos no vuelven a la
     evaluación (`app/Exceptions/Handler.php` ~L170-183, `LoginController.php:73-80`).
  2. **No presentes** (en 40484: 47 de 151 inscripciones sin `presente=1`) reciben 403; entre
     2026-09-20 y 2026-10-03 se veía como **"Error 500"** (corregido en `269fc354`). Si el link se
     comparte por WhatsApp a todos, los no marcados presentes fallan.
  3. **Logueado con otra cuenta** (duplicadas) → 403.
  4. Presentes **sin `Grupo_Persona`** entran pero **no aparecen para ser evaluados** (ver T1).
- **Hipótesis descartada con datos:** deep link iOS (el AASA cubre `/actividades/*`). En 40484 la tasa
  de evaluación es pareja: iOS 8/25 (32%), Android 6/18 (33%), sin app 21/61 (34%) → no hay evidencia
  de que la app intercepte el link.
- **Git:** `269fc354` (10-03) resolvió la parte "pantalla de error 500" para no presentes; el resto sigue.
- **Estado real:** parcialmente vigente; no reproducible con certeza sin ejemplos.
- **Clasificación:** #5 `REQUIERE_INVESTIGACION` (con un bug concreto ya identificado → T7);
  #12 `DUPLICADO` de #5 + `ACLARACION_NECESARIA`.
- **Info que falta (concreta):** actividad (para #12), 2–3 nombres de voluntarios a los que no les
  funcionó, si abrieron el link desde el celular o la compu, si ya tenían la sesión iniciada, y qué ven
  exactamente (captura de la pantalla a la que llegan).

### #6 — "Error 505" al confirmar lugar desde el mail (Mildred, admin MX, act. 40045)

- **Reportó:** al confirmar voluntarios, el mail que les llega tiene un botón azul que da error 505.
- **Código:** al confirmar una inscripción con pago, se envía `MailInscripcionFaltaPago`, cuyo botón azul
  "CONFIRMAR MI LUGAR" apunta a `GET /inscripciones/actividad/{id}/confirmar/donacion`
  (`auth` + `can:confirmar`). Escenarios de 500 en el release del reclamo: logueado con **otra cuenta**
  (ModelNotFound → 500), actividad no `Abierta` (→ 500), `config_pago` inválido, etc.
- **Datos:** 40045 está `Abierta` todo el período (auditoría), `config_pago` de México válido (Stripe),
  método solo transferencia; 497 confirmadas, 122 pagas y 89 con comprobante → **el flujo funciona para
  la mayoría**. 5 inscriptos confirmados comparten mail con otra cuenta. Encaja con el caso "otra
  cuenta" (u otra sesión abierta en el navegador).
- **Git:** `269fc354` (10-03): ahora redirige a la actividad con un aviso en vez de 500; los 403/404
  dejaron de verse como 500. "505" es casi seguro el 500 mal leído.
- **Estado real:** el escenario más probable **ya está corregido**. Quedan escenarios residuales (config
  inválida, `fechaFin` NULL) que no aplican a esta actividad.
- **Clasificación:** `RESUELTO_RECIENTEMENTE` (con confirmación del usuario).
- **Info que falta:** solo si vuelve a pasar: nombre del voluntario y captura.

### #7 y #11 — No se puede agregar gente a los grupos de evaluación (Delfina, admin AR, act. 41095)

- **Reportó:** #7 no puede agregar "gente de la oficina" ni a sí misma; #11 no puede agregar a muchos
  voluntarios (lista de nombres).
- **Qué pide:** armar los grupos/cuadrillas para que se evalúen entre pares.
- **Logs:** **66 errores** `Undefined variable: grupo` en `backoffice/ajax/GruposController.php:82`
  (`incluirInscripto`) entre 09-29 y 10-02 — 49 de la reportante, el resto de otros coordinadores.
- **Código:** `incluirInscripto` solo **mueve** una fila existente de `Grupo_Persona`; si la persona no la
  tiene ejecuta `return response($grupo, 500)` con `$grupo` indefinida → 500, y `Miembros.vue` solo
  maneja 428 → **falla en silencio**. Si ya está en un subgrupo → 428.
- **Por qué faltan filas `Grupo_Persona`:**
  1. **Bug de borrado cross-actividad** — `GruposActividadesController::delete()` L147:
     `GrupoRolPersona::whereIn('idPersona', $idsPersona)->delete()` **sin `idActividad`**: sacar a alguien
     de un grupo en una actividad lo saca de **todas**. (L139 tampoco filtra `Grupo` por actividad.)
     Desde 2018. Evidencia: los huérfanos de 41095 están huérfanos en casi todas sus actividades desde
     agosto (17/21, 5/5, 5/5, 5/5…) — típico de staff con muchas actividades = **"gente de la oficina"**.
  2. **Raíz resuelta por nombre** — `InscripcionesController::incluirEnGrupoRaiz` (L620-636) y
     `EvaluacionesController::index` (L32-37) hacen `Grupo::firstOrCreate([... 'nombre' => nombreActividad])`.
     Al **clonar** (`clonarGrupo` copia el raíz con el nombre de la actividad original) o **renombrar**, se
     crea un **segundo raíz**. 41095 tiene 2 raíces (el árbol de escuelas/cuadrillas cuelga de la raíz
     vieja "3 y 4 de septiembre"); 40484 también. **51% de las actividades desde junio tienen ≥2 raíces.**
- **Volumen:** inscripciones vivas sin `Grupo_Persona`: 780 (mar), 726 (abr), 515 (may), 522 (jun), 631
  (jul), 370 (ago), 179 (sep), 8 (oct). En 41095: 9 de 112.
- **Sobre "ni yo misma":** la reportante está inscripta y en el subgrupo "Escuela Maria Elena" → el
  endpoint devuelve 428 ("ya pertenece"); para el staff **no inscripto**, /grupos no tiene camino (el
  formulario "voluntario no inscripto" existe en `btnGrupoPersona.vue` pero ningún botón lo abre).
- **Git:** ningún cambio reciente en estos archivos. Vigente.
- **Workaround inmediato (sin código):** desde la pestaña **Inscripciones**, seleccionar a las personas y
  usar **"Asignar grupo"** (`asignarGrupo` sí crea la fila si falta). Para staff no inscripto: inscribirlo
  manualmente, marcarlo presente y asignarle grupo.
- **Clasificación:** ambos `REQUIERE_IMPLEMENTACION` → T1. #7 no es duplicado literal (pide además
  agregar staff no inscripto), pero la causa principal es la misma.

### #8 — "Inscripciones Cerradas" cuando están abiertas (Voluntariado TECHO Costa Rica)

- **Código:** la etiqueta la calcula `resources/js/components/backoffice/datatable/estadoActividad.vue`
  **solo en `mounted()`** (L64-65), sin `watch` ni computed. vuetable-2 renderiza filas con
  `:key="itemIndex"` (`node_modules/vuetable-2/src/components/Vuetable.vue:76`) → al paginar, filtrar u
  ordenar reutiliza el componente y **muestra el estado de la fila que ocupaba esa posición en la primera
  carga**. Además: fechas NULL → "Cerradas" (el servidor las considera abiertas) y "todavía no abrió" se
  muestra igual que "Cerradas".
- **Datos:** la cuenta tiene 191 actividades en "Mis actividades"; el 09-29 había 2 abiertas (39136 y
  41279), ambas dentro de su ventana → al estar lejos de la primera página, es muy probable que se
  vieran con etiqueta congelada.
- **Impacto:** solo visual (el servidor sí permite inscribirse). Puede generar decisiones equivocadas.
- **Clasificación:** `REQUIERE_IMPLEMENTACION` → T3. Sin info adicional necesaria (opcional: qué actividad).

### #9 — Roles postulados → confirmar (Costa Rica)

- **Código:** en la inscripción el voluntario elige **varios** roles (`Inscripcion.roles_aplicados`, JSON);
  el coordinador asigna **uno** (`Inscripcion.rol`) con la acción masiva. `roles_aplicados` **no aparece
  en el listado** (solo en el Excel, como JSON crudo). `TagField.vue` (`roles_asignados`) está roto para el
  formato actual. Bug lateral: el filtro `filters/inscripciones/Rol.php` usa `Rol.rol` sin join.
- **Workaround parcial:** columna de seguimiento tipo "etiquetas" (pero arranca vacía).
- **Clasificación:** `SUGERENCIA_PRODUCTO`. Mínimo viable si se aprueba: columna "Roles postulados" +
  precargar `rol` cuando hay uno solo / editor inline limitado a los postulados.

### #10 — "Monitor" → "Capataz" (Costa Rica)

- **Código:** el rol es un slug (`monitor`); el texto sale de `resources/lang/*/backend.php`
  (`roles_actividad_options.monitor`). **En prod Costa Rica, México y Perú tienen `atl_pais.locale = es_AR`**
  (contradice CLAUDE.md, que dice `es_CH` para LatAm) → cambiar el texto cambia también Argentina.
  Precedente de textos por país: `suscribe.secundario_by_country`.
- **Clasificación:** `SUGERENCIA_PRODUCTO`. Opción barata: "Monitor/a / Capataz" global (decisión de
  producto). Opción correcta: override por país (~4 puntos de cambio).

### #13 — Voluntario en Alemania sin provincia/localidad (Martha, admin Perú)

- **Datos:** el voluntario se registró el 09-25 con país **Alemania** (id 4, sin provincias cargadas;
  222 de 241 países no tienen) y `idProvincia/idLocalidad = 1/1` (valores basura). La admin, a las
  17:09 del 10-01 (un minuto antes de reportar), le cambió el país a **Perú** con provincia/localidad
  válidas; **a las 17:17 se inscribió** en una actividad de Perú.
- **Código:** web (registro/perfil) tolera países sin provincias; la **API mobile** las exige
  (`app/Http/Requests/CrearPersona.php:50-51`, `api\PersonasController@update` L296-297:
  `required|integer`) → desde la app no se puede completar.
- **Clasificación:** caso `RESUELTO` (workaround manual de la admin); causa estructural → T5 (menor).
- **Respuesta:** confirmar que ya puede inscribirse y que vimos el problema de fondo.

### #14 — "La cantidad de inscripciones de las personas no es correcta" (Andrea, admin AR, act. 41683)

- **Código:** hay **tres números distintos**:
  1. Columna "Participaciones"/"Nivel" (`EnriquecedorFilas::inyectarMetricasVoluntario` L74-80): solo
     `presente=1`, incluye actividades borradas, todos los países, la actual, `COUNT(*)`.
  2. "Cantidad de actividades" (filtro + Excel; `filters/inscripciones/IdActividad.php` L25-30): **no
     excluye inscripciones dadas de baja ni actividades borradas** (bug), cuenta futuras y no confirmadas.
  3. Totales de la actividad: incluyen personas soft-borradas que el listado no muestra.
- **Datos:** 41683 tiene 4 inscriptos, sin duplicados ni fichas duplicadas; los 4 tienen inscripciones
  dadas de baja en su historial (que el número 2 cuenta) y para 1 de ellos la métrica 1 difiere de la
  cuenta limpia.
- **Clasificación:** `ACLARACION_NECESARIA` — no sabemos qué número mira ni qué esperaba.
- **Info que falta:** qué columna o número (Participaciones en pantalla / Excel / total), de qué persona,
  y cuánto esperaba que diera.

### #15 — "Aparece inscripto un supuesto usuario eliminado" (Ana Catalina, admin AR, act. 41724)

- **Datos:** inscripción 1540115: la persona **se registró el 09-26, eliminó su cuenta el 09-27**
  (anonimizada: "Usuario eliminado", `Desvinculado`) y **se inscribió ella misma el 10-02 09:41**
  (auditoría: `idPersonaModificacion` de creación = la propia persona). El coordinador la dio de baja el
  10-03. La cuenta anonimizada **conserva `google_id` y password**. Mismo patrón en al menos otras 2
  cuentas (una se auto-inscribió 13 min después de borrarse).
- **Código:** `ajax\UsuarioController@delete` (~L471-503) anonimiza nombres/mail/dni/teléfono y borra
  solo inscripciones futuras, pero **no limpia `google_id`/`facebook_id`/`apple_id` ni invalida el
  password** → el login social (`LoginController` ~L211-216, match por social id) sigue entrando a la
  cuenta "eliminada". Además, 1.103 inscripciones históricas de cuentas anonimizadas siguen vivas (por
  diseño, para reporting) y se ven como "Usuario eliminado".
- **Clasificación:** `REQUIERE_IMPLEMENTACION` → T4 (privacidad: una baja de cuenta no es efectiva).
- **Info faltante:** ninguna.

### #16 — Voluntaria no aparece por nombre ni para equipo (Elena, admin MX)

- **Datos:** la voluntaria se registró el 09-24 con `idPais=241` ("Latam"). El buscador de
  `/admin/usuarios` filtra por el país del admin **salvo cuando el término es un email** (cross-país) →
  por mail aparecía, por nombre no. El de integrantes de equipo (`/ajax/coordinadores` →
  `CoordinadoresSearch`) **siempre** filtra país y además usa `concat` (NULL-inseguro). La admin la editó
  a las 14:13 y 14:23 (después de reportar) y **hoy tiene país México** → ya debería aparecer en ambos.
- **Estructural:** desde septiembre hay 177 personas nuevas con país 241 "Latam" y 162 con país 1
  ("Afganistán", default sospechoso del selector) → invisibles para su sede. Relacionado con
  `personas-invisibles-pais-softdelete` (memoria del proyecto).
- **Clasificación:** caso `RESUELTO` (workaround de la admin) + T5.

---

## D. Respuestas de Techita

> Todas: **RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**. Ninguna fue publicada.
> ⚠️ Antes de publicar ver G.1: hoy hay un error de prod al despachar el mail de respuesta
> (`EnviarMailTransaccionalSes::dispatch()`); verificar que el aviso por mail realmente sale.
> Tono neutro (los reclamos vienen de AR, MX, CR y PE).

**#3 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Martina! Gracias por avisar. Lo pudimos reproducir: en la vista filtrada por sede el botón
> "Exportar a Excel" no funciona. Es un error nuestro que apareció con el nuevo desplegable de sedes y ya
> está en la lista para corregir. Mientras tanto, se puede exportar desde el listado general de
> Actividades (trae todas las sedes) y filtrar Salta en el Excel. Te avisamos por acá cuando esté
> resuelto.

**#4 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Jimena! Gracias por la sugerencia, tiene mucho sentido. Hoy no existe una papelera para recuperar
> actividades eliminadas; lo sumamos a las mejoras a evaluar. Si ahora mismo necesitas recuperar una
> actividad en particular, contanos cuál es (nombre o enlace) y vemos si podemos restaurarla.

**#5 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Luisina! Gracias por reportarlo. Estamos revisando el link de evaluación. Encontramos que, si la
> persona no tiene la sesión iniciada, el sistema la lleva primero a la página de actividades para
> ingresar, y después no siempre la devuelve a la evaluación; además, solo pueden evaluar quienes figuran
> como **presentes** en la actividad. Para confirmar qué les pasó, ¿nos pasarías 2 o 3 nombres de
> voluntarios a los que no les funcionó, si lo abrieron desde el celular o la compu, y si puedes, una
> captura de la pantalla a la que llegan?

**#6 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Mildred! Gracias por avisar. Revisamos el caso: el error aparecía, por ejemplo, cuando la persona
> abría el botón del correo teniendo iniciada la sesión con otra cuenta distinta a la de su inscripción.
> El 3 de octubre publicamos una corrección: ahora, en vez del error, el sistema le muestra un aviso para
> que entre con la cuenta correcta. Si a alguien le vuelve a pasar, ¿nos compartes su nombre y una
> captura de lo que ve?

**#7 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Delfina! Gracias por reportarlo. Encontramos la causa: a algunas personas (sobre todo del equipo,
> que participan en muchas actividades) se les pierde la asignación de grupo, y por eso la pantalla de
> Grupos no las deja agregar. Ya está en la lista para corregir. Mientras tanto, se puede hacer desde la
> pestaña **Inscripciones**: seleccionar a las personas y usar **"Asignar grupo"**. Si alguien del equipo
> no está inscripto en la actividad, primero hay que inscribirlo y marcarlo como presente para que pueda
> evaluar.

**#8 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola! Gracias por avisar. Es un error de visualización del listado "Mis actividades": al cambiar de
> página o filtrar, la etiqueta de "Inscripciones cerradas/abiertas" puede quedar desactualizada. Las
> inscripciones siguen funcionando normalmente; para verificar el estado real, puedes abrir la actividad.
> Ya está en la lista para corregir.

**#9 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola! Gracias por la propuesta y por la captura, se entiende muy bien. Hoy los roles que cada persona
> elige al inscribirse se guardan, pero no se ven en la columna de rol del listado (sí en la exportación a
> Excel). Lo sumamos a las mejoras a evaluar: mostrar los roles elegidos y poder confirmar uno desde ahí.

**#10 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola! Gracias por la sugerencia. Hoy el nombre de los roles es el mismo para varios países, por eso no
> podemos cambiarlo solo para Costa Rica de inmediato. Lo dejamos registrado para evaluar cómo adaptar los
> nombres por país.

**#11 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Delfina! Es el mismo problema que el otro reporte que enviaste: a esas personas les falta la
> asignación de grupo y la pantalla de Grupos falla sin mostrar el error. Ya está en la lista para
> corregir. Mientras tanto, desde la pestaña **Inscripciones** puedes seleccionarlas (incluso varias a la
> vez) y usar **"Asignar grupo"**; eso sí funciona.

**#12 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Albertina! Gracias por avisar. Estamos revisando un problema parecido con el link de evaluación.
> Para entender tu caso, ¿nos dices de qué actividad es, 2 o 3 nombres de voluntarios a los que no les
> funcionó y qué les aparece al abrirlo (si puedes, una captura)? Algo útil para revisar: solo pueden
> evaluar quienes figuran como **presentes** en la actividad, y conviene que abran el link con la sesión
> ya iniciada.

**#13 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Martha! Gracias por avisar. Vimos que el voluntario ya pudo inscribirse después de que actualizaste
> su país en el perfil. El problema de fondo es que para algunos países no tenemos provincias y localidades
> cargadas; lo tenemos anotado para que eso no bloquee a nadie.

**#14 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Andrea! Gracias por reportarlo. Para revisarlo bien necesitamos saber qué número estás mirando:
> ¿la columna de participaciones del listado, el dato del Excel o el total de la actividad? Si puedes,
> dinos de qué persona se trata y cuánto esperabas que diera.

**#15 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Ana Catalina! Gracias por avisar, el reporte fue muy útil. Esa persona había eliminado su cuenta y
> aun así pudo volver a ingresar e inscribirse, algo que no debería pasar. Ya identificamos la causa y está
> en la lista para corregir. Esa inscripción ya fue dada de baja.

**#16 — RESPUESTA PROPUESTA — REQUIERE APROBACIÓN**
> ¡Hola Elena! Gracias por el detalle. El perfil tenía cargado un país distinto de México, y por eso no
> aparecía al buscarlo por nombre ni al agregarlo a un equipo (por correo sí, porque esa búsqueda incluye
> todos los países). Vimos que ya actualizaste el país, así que ahora debería aparecer. Revisaremos por qué
> algunos registros quedan con el país incorrecto.

---

## E. Trabajo técnico

**Sistema de tareas existente:** no hay `task.md`; el proyecto usa **`tasks.json`** (+ `AGENTS.md`,
`.claude/agents/{leader,implementer,reviewer}.md`, `progress/current.md`). Estructura por tarea: `id`,
`group`, `name`, `title`, `description`, `acceptance[]`, `risk`, `status` (`pending|in_progress|done|blocked`),
`blockedBy`, `branch`, `notes`. Reglas: una tarea a la vez; backward compat; hoy el roadmap prioriza
CI (29) → upgrade Laravel (19-25) → seguridad. **Ninguna tarea existente cubre estos reclamos** (la más
cercana, 8 "getMiembrosAttribute con efecto secundario", toca `Actividad::getMiembrosAttribute` y se
relaciona con T1 pero no la cubre).

**Recomendación: no editar `tasks.json` todavía.** Agregar 7 tareas cambia la priorización de un roadmap
con orden explícito (`orden_seguridad_vs_upgrade`) y eso es decisión del dueño. Abajo quedan listas en el
formato exacto para pegar (ids 47-53, grupo nuevo `reclamos-prod`). Sugerencia de prioridad: T1 y T4
antes que el upgrade (impacto en datos/privacidad); el resto puede intercalarse.

### T1 (id 47) — Integridad de grupos: borrado cross-actividad, raíz por nombre y alta silenciosa — **ALTA**
- **Reclamos:** #7, #11 (causa directa); #5/#12 (presentes sin grupo no aparecen para ser evaluados).
- **Problema/causa (confirmada):** (a) `GruposActividadesController::delete()` L147 borra `Grupo_Persona`
  sin `idActividad` (y L139 borra `Grupo` sin filtrar actividad); (b) raíz resuelta con
  `firstOrCreate(nombre = nombreActividad)` en `InscripcionesController::incluirEnGrupoRaiz` (L620-636) y
  `EvaluacionesController::index` (L32-37), mientras `clonarGrupo` copia el raíz con el nombre de la
  original y `getMiembrosAttribute`/`getGrupoRaizAttribute` toman el primero con `idPadre=0`; (c)
  `GruposController::incluirInscripto` (L56-83) devuelve 500 con variable indefinida si no hay fila y
  `Miembros.vue` no muestra error.
- **Solución propuesta:** filtrar por `idActividad` en `delete()`; resolver la raíz **por `idPadre=0`
  (la de menor `idGrupo`)**, nunca por nombre, en un único helper (p.ej. `Actividad::grupoRaiz()`
  sin crear duplicados); en `incluirInscripto`, si la persona tiene inscripción viva y no tiene fila,
  crearla en el grupo destino; si no está inscripta, 422 con mensaje; `Miembros.vue` muestra el error.
- **Datos (requiere aprobación aparte):** script/command idempotente con `--dry-run` que (1) cree la fila
  raíz faltante para inscripciones vivas sin `Grupo_Persona` (~3.7k desde marzo) y (2) reporte (no
  fusione automáticamente) actividades con ≥2 raíces; la fusión de raíces se decide caso a caso.
- **Archivos:** `app/Http/Controllers/backoffice/ajax/GruposActividadesController.php`,
  `app/Http/Controllers/backoffice/ajax/GruposController.php`, `app/Http/Controllers/InscripcionesController.php`,
  `app/Http/Controllers/EvaluacionesController.php`, `app/Http/Controllers/backoffice/ActividadesController.php`
  (`clonarGrupo`), `app/Actividad.php`, `resources/js/components/backoffice/grupos/Miembros.vue`.
- **Riesgos:** la app mobile consume `miGrupo`/evaluaciones vía API (`routes/api.php:103-109`) — no
  cambiar el contrato; el árbol de grupos de actividades con 2 raíces puede "reordenarse" al unificar.
- **Dependencias:** relacionada con task 8 (`getMiembrosAttribute` crea raíz como efecto secundario).
- **Criterios de aceptación:** quitar a una persona de un grupo en la actividad A no altera su membresía en
  B; renombrar o clonar una actividad e inscribir a alguien no crea un segundo raíz; agregar desde /grupos a
  un inscripto sin fila funciona; a un no inscripto devuelve 422 con mensaje visible; 0 ocurrencias de
  `Undefined variable: grupo` en logs tras el deploy.
- **Tests:** Feature para `delete` cross-actividad, `incluirInscripto` (con fila en raíz, en subgrupo→428,
  sin fila→crea, no inscripto→422), inscripción tras renombrar/clonar (una sola raíz), evaluación de una
  persona sin grupo no crea raíz nueva. Extender `tests/Feature/GruposTest.php`.

### T2 (id 48) — Export de actividades en vista por oficina — MEDIA
- **Reclamos:** #3.
- **Causa (confirmada):** falta `GET /admin/actividades/oficina/{idOficina}/exportar`; `FilterBar.vue:44` concatena
  a `location.href`; `ActividadesExport` no recibe oficina; `headings()` (9) vs `map()` (12) desalineados.
- **Solución:** ruta + pasar `idOficina` a `ActividadesExport`; `FilterBar` con `location.pathname` y
  `encodeURIComponent`; alinear headings/map (también `MisActividadesExport`).
- **Archivos:** `routes/web.php`, `app/Http/Controllers/backoffice/ReportController.php`,
  `app/Exports/ActividadesExport.php`, `app/Exports/MisActividadesExport.php`,
  `resources/js/components/backoffice/datatable/FilterBar.vue`.
- **Riesgos:** FilterBar es compartido por todos los listados `<datatable>` → revisar que cada `/exportar`
  existente siga resolviendo igual.
- **Aceptación:** exportar desde `/admin/actividades/oficina/15` descarga solo actividades de esa oficina;
  columnas con encabezado correcto; los demás exports siguen funcionando.
- **Tests:** Feature de la ruta nueva (200 + filtro de oficina + autorización `role:admin`); test de
  `ActividadesExport::headings()` vs `map()` con igual cantidad.

### T3 (id 49) — Etiqueta de estado de actividad congelada en listados — BAJA/MEDIA
- **Reclamos:** #8.
- **Causa (confirmada):** `estadoActividad.vue` calcula en `mounted()`; vuetable-2 reutiliza por índice.
- **Solución:** convertir los estados en `computed` sobre `rowData` (como se hizo en `actividad.vue`,
  commit `03a7001d`); tratar fechas NULL como "sin restricción" (igual que `Actividad::inscripcionesAbiertas`);
  distinguir "Aún no abren" de "Cerradas" (clave i18n en los 4 `backend.php` + regenerar vue-i18n).
- **Archivos:** `resources/js/components/backoffice/datatable/estadoActividad.vue`, `resources/lang/*/backend.php`.
- **Aceptación:** paginar/filtrar/ordenar "Mis actividades" muestra para cada fila su estado real.
- **Tests:** test Vue (mocha-webpack) que monta el componente, cambia `rowData` y verifica la etiqueta.

### T4 (id 50) — La baja de cuenta no corta el acceso (cuentas anonimizadas siguen operando) — **ALTA (privacidad)**
- **Reclamos:** #15.
- **Causa (fuerte, con evidencia de datos):** `ajax\UsuarioController@delete` anonimiza pero conserva
  `google_id`/`facebook_id`/`apple_id` y password; el login social matchea por social id.
- **Solución:** en la anonimización, limpiar social ids, invalidar password y `remember_token`, revocar
  **todos** los tokens Passport de la persona; bloquear login/inscripción de `estadoPersona='Desvinculado'`
  anonimizado. Revisar interacción con "restaurar cuentas borradas" (`24b92a44`/`0f1d9b77`/`7d094855`,
  que aplica a soft-deleted, no a anonimizadas) para no reabrirlas por esa vía.
- **Archivos:** `app/Http/Controllers/ajax/UsuarioController.php` (~L460-505),
  `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Controllers/api/PersonasController.php`
  (login social mobile), `app/Policies/ActividadesPolicy.php` (`inscribir`).
- **Datos:** decidir si se limpian los social ids de las 807 cuentas ya anonimizadas (migración de datos,
  requiere aprobación).
- **Riesgos:** si la persona vuelve a registrarse con el mismo Google debe crear una cuenta nueva limpia
  (no colisionar con la anonimizada).
- **Aceptación:** tras borrar la cuenta, ni login por password ni social ni token previo dan acceso; un
  nuevo login con ese Google crea cuenta nueva.
- **Tests:** Feature: borrar cuenta → login social con mismo `google_id` no entra a la anonimizada; token
  previo revocado (API); `Desvinculado` no puede inscribirse.

### T5 (id 51) — Costuras de país en personas: invisibles en buscadores y países sin provincias — MEDIA
- **Reclamos:** #16 y #13 (casos ya destrabados a mano; esto evita que se repitan).
- **Causa:** `CoordinadoresSearch`/`filters/Coordinador.php` (integrante de equipo) filtra siempre por
  `idPaisPermitido`, sin búsqueda cross-país por email exacto y con `concat` NULL-inseguro; personas que
  quedan con `idPais` 241 "Latam" (177 desde sep.) o 1 "Afganistán" (162 desde sep.); API exige
  `idProvincia/idLocalidad` aunque el país no tenga provincias (`CrearPersona.php:50-51`,
  `api\PersonasController@update` L296-297).
- **Solución:** replicar en `/ajax/coordinadores` el patrón de `/ajax/personas` (email exacto cross-país) y
  `CONCAT_WS`; `required` de provincia/localidad solo si el país tiene provincias; **investigar** (sin fix
  todavía) de dónde salen los registros con país 1/241 (¿default del selector?, ¿app?).
- **Riesgos:** cross-país en buscador de equipos expone personas de otros países → limitar a email exacto
  (mismo criterio ya aceptado en usuarios).
- **Dependencias:** coordinar el cambio de validación con la app MiTECHO (backward compatible: aflojar
  una regla no rompe clientes).
- **Aceptación:** una persona con país distinto se encuentra por email exacto al agregar integrante; nombres
  con campos NULL matchean; `/api/register` con país sin provincias y sin `idProvincia` → 200.
- **Tests:** Feature de `getCoordinadores` (email cross-país, NULL-safe) y de `/api/register`/`editPersona`
  con país sin provincias.

### T6 (id 52) — Métricas de "cantidad de inscripciones" inconsistentes — **BLOCKED (aclaración #14)**
- **Reclamos:** #14.
- **Confirmado en código:** `filters/inscripciones/IdActividad.php` L25-30 no excluye
  `Inscripcion.deleted_at` ni `Actividad.deleted_at`; precedencia rota en L44-45; "Participaciones"
  (`EnriquecedorFilas` L74-80) cuenta actividades borradas y la actual.
- **Siguiente paso:** con la respuesta de la usuaria, definir la métrica canónica (ver
  `docs/reporting.md` / glosario de métricas: no re-implementar definiciones fuera de las vistas
  `reporting_*`) y alinear las tres. El fix del filtro (bajas/borradas) puede hacerse antes si se aprueba.
- **Tests:** Feature sobre el filtro con inscripciones dadas de baja y actividades borradas.

### T7 (id 53) — Evaluación: llegar sin sesión / redirect post-login — MEDIA
- **Reclamos:** #5, #12.
- **Causa (confirmada en código, impacto a medir):** `Handler.php` (~L170-183) guarda `after_login_url`
  con el **Referer** antes que la URL pedida y la cookie dura 10 min; `/login` renderiza el listado de
  actividades. Login social sin `login_callback` deja página en blanco (`LoginController` L257-261).
- **Solución:** guardar siempre la URL pedida (`url()->full()`) para GET protegidos; para
  `/actividades/{id}/evaluaciones` sin sesión, mostrar login con contexto ("Inicia sesión para evaluar…");
  en 403 de `evaluar`, mensaje explicativo ("no figuras como presente…") en vez de pantalla genérica.
- **Dependencias:** idealmente después de recibir ejemplos de #5/#12 (puede haber otra causa).
- **Aceptación:** abrir el link sin sesión desde un webmail → login → aterriza en la evaluación; un no
  presente ve un mensaje claro.
- **Tests:** Feature: GET evaluaciones sin auth con Referer externo → tras login redirige a la evaluación.

**JSON listo para `tasks.json` (no aplicado):** ver `progress/triage-reclamos-2026-10-05.json`, clave
`proposed_tasks`.

**No crear tareas para:** #4, #9, #10 (sugerencias: requieren decisión de producto), #6 (resuelto), #13
y #16 como casos (cubiertos por T5).

---

## F. Oportunidades de automatización

Pipeline objetivo: `análisis → respuesta propuesta → aprobación → comentario de Techita`. La base ya
existe: `issue_reports` (estado/severidad/área), `issue_report_replies` (`is_internal` = nota interna vs.
visible, `notified_at`), endpoint `POST /admin/ajax/reportes/{id}/responder`, `GithubIssueService` +
`reportes:sync-github`, y el JSON de este triage como formato intermedio.

**Automatizable sin riesgo significativo** (solo lectura o notas internas):
1. Detectar reclamos nuevos y comentarios nuevos (polling de `issue_reports.created_at` /
   `issue_report_replies.created_at` > último corrido).
2. Enriquecer automáticamente cada reclamo: errores de `laravel-*.log` del `idPersona` reportante ±2 h
   (este triage encontró la causa de #7/#11 así), auditoría (`auditorias`) de la entidad de la URL,
   release vs. HEAD y commits que tocan los archivos de la ruta.
3. Análisis de código y cruce con commits posteriores al `release` del reclamo → marcar
   "posiblemente resuelto por `<sha>`" con la evidencia.
4. Detectar duplicados (misma ruta/área + texto similar + misma firma de error en logs).
5. Generar la respuesta propuesta de Techita y **guardarla como nota interna** (`is_internal=1`) —
   no notifica a nadie.
6. Generar el JSON de tareas propuestas.
7. Re-evaluar reclamos abiertos cuando se deploya (nuevo `release`): volver a correr el chequeo de
   evidencia y anotar internamente si cambió el diagnóstico.
8. Guardrails de consultas: timeouts (`MAX_EXECUTION_TIME`) en las queries de diagnóstico contra prod
   (en esta corrida una query de duplicados por nombre se colgó y la corté con `KILL QUERY`), o correrlas
   contra una réplica.

**Requiere aprobación humana:**
1. Publicar la respuesta visible (`is_internal=0`) → dispara mail al reporter. Flujo sugerido: botón
   "Aprobar y enviar" sobre la nota interna en `ReporteDetalle.vue` (convierte la nota en respuesta
   visible y notifica).
2. Cambiar estado (`resuelto`/`descartado`) — también notifica al reporter.
3. Crear/actualizar tareas en `tasks.json` o issues en GitHub (prioriza trabajo).
4. Cualquier reparación de datos (T1, T4) o cambio de código.
5. Pedidos de información al usuario (son comunicación; pueden ir por el mismo "aprobar y enviar").
6. Acceso a capturas (PII).

---

## G. Hallazgos fuera de alcance (de los logs de prod 09-29 → 10-05)

1. **⚠ `Call to undefined method App\Jobs\EnviarMailTransaccionalSes::dispatch()`** — 2 veces hoy (usuario
   540683, al responder el #1). El aviso por mail de las respuestas de la bandeja (`98e6ed29`) puede **no
   estar saliendo** aunque `notified_at` se complete. **Bloqueante para publicar respuestas de Techita.**
   No pude abrir el job en local para confirmar la causa (permiso denegado en esa lectura); probable falta
   del trait `Dispatchable` o desfase de bundle/opcache — verificar.
2. **`equipo_reunion.descripcion` Data too long**: 165 errores el 10-04 (+4 el 09-29) pese a `49512cd2`
   (09-22, pasa la columna a TEXT) → ¿la migración no corrió en prod? Verificar `migrations`.
3. `actividad…comentarios_adicionales` Data too long: 42 el 09-30 (convención: migrar a TEXT).
4. `EquipoReunionesController::delete does not exist`: 12 errores (10-02/10-04) → ruta apunta a método
   inexistente.
5. **Seguridad:** `auditorias.informacion` guarda el estado completo de `Persona`, **incluido el hash del
   password y `remember_token`**. Excluir campos sensibles del snapshot.
6. Calidad de datos: 162 personas nuevas desde septiembre con `idPais=1` (Afganistán) y 177 con 241
   ("Latam"); `atl_pais.locale='es_AR'` para CR/MX/PE (CLAUDE.md dice `es_CH` para LatAm).
7. 51% de actividades con ≥2 grupos raíz (ver T1).
8. Stripe: `No valid payment method types for this Payment Intent` (3, inscripción 1539227).
9. #1 y #2 siguen como `descartado`: #1 es prueba; #2 ("los leads tienen problemas para registrarse")
   quedó descartado sin respuesta — revisar si fue intencional (podría relacionarse con
   `campaign_id NULL` / duplicados de captación).
