# Historial de sesiones

---

## 2026-06-17 — Cobertura API mobile #14–15 + más bugs reales

**Agente:** Claude (Opus 4.8) · **Branch:** `claude/goofy-mclaren-e7f8eb`

- `tasks.json` al día: #9 (baseline) y #14, #15 marcados como done.
- Runner directo: `./test.sh` + config autocontenida en `phpunit.xml` (`vendor/bin/phpunit` corre sin flags). CI (`.travis.yml`) ahora corre PHPUnit contra MySQL.
- **#14** `tests/Feature/api/AuthApiTest.php` (6 tests): login válido/ inválido, register válido/duplicado, providerLogin (validación + proveedor inválido).
- **#15** `tests/Feature/api/ActividadesApiTest.php` (6 tests): listado paginado, detalle con campos esperados, **contrato de fechas dd-mm-aaaa** (ancla anti-regresión del upgrade), 404 inexistente, filtro por categoría.

**Bugs reales destapados y corregidos:**
- `/api/register` daba **403 siempre** — `CrearPersona::authorize()` estaba vacío (devolvía null). Corregido a `return true` (registro público).
- `GET /api/actividades/{id}` inexistente daba **500** (`find()` + acceso a propiedad sobre null). Corregido a 404 con `findOrFail`.

**Hallazgos anotados (no corregidos):**
- La ruta `POST /api/socialLogin` apunta a `PersonasController@socialLogin`, método que **no existe** (ruta muerta). El social login real es `providerLogin`.

Suite: **116/116 verde**.

---

## 2026-06-17 — Suite 100% verde (97/97) + bugs reales corregidos

**Agente:** Claude (Opus 4.8) · **Branch:** `claude/goofy-mclaren-e7f8eb`

Partiendo del baseline (~19/97), se llevó la suite a **97/97 en verde** (349 assertions).

### Bugs reales de producción corregidos (encontrados por los tests)
1. **Fuga de locale** — `PushNotificationService::enviarLocalizado()` "restauraba" el idioma leyendo `config('app.locale')` *después* de que `App::setLocale()` ya la había sobreescrito → dejaba el locale del usuario pegado al resto del request. Fix: guardar `App::getLocale()` antes. (Hacía fallar 5 tests de inscripción.)
2. **Filtro de país inconsistente** — `ActividadesSearch::newQuery()` filtraba por `Session::get('pais')` mientras el resto de la app usa `config('app.pais')`; además devolvía listado vacío para usuarios autenticados sin país. Fix: leer `config('app.pais')` con guard de null. (4 tests.)
3. **`count()` sobre null** — `InscripcionesController@create` contaba `json_decode(jornadas)` que podía ser null (rompía en PHP 7.2 y rompería en 8). Fix: `is_array()` guard.
4. **`array_column()` sobre no-array** — `Search/filters/Tipos.php` asumía siempre un JSON string; lo hice robusto a ambos formatos.
5. **`Persona::fusionar()` incompleto** — no migraba la tabla `coordinadores` (relación `actividades()`) → la fusión de cuentas perdía membresías de coordinador y dejaba huérfanos. Fix: agregada la migración de `Coordinador`.
6. **Factories desactualizados** — `ActividadFactory` no seteaba columnas agregadas por migraciones 2023 (`ficha_medica_campos`, `requiere_estudios`, `show_dates`, `show_location`); `PersonaFactory` generaba `email_verified_at` inválido para TIMESTAMP.

### Fixes de wiring/datos en tests (no bugs de app)
- Fix central en builder `ActividadFactory`: sincroniza `idPaisPermitido` del creador/coordinador con el país de la actividad (los FormRequests `CrearInscripcion`/`CrearPunto`/`CrearActividad` lo exigen).
- País explícito en tests de crear-actividad y puntos; `where()` malformado en EvaluacionesTest; teléfono internacional y `acepta_marketing` entero en UsuariosTest; redirect y acceso-por-array al mail en MailingTest.

### Deuda / a decidir (anotado, no bloqueante)
- `backoffice\ActividadesController@destroy` redirige a `/admin/actividades` (admin-only). Un coordinador no-admin que borra su actividad caería en una página 403. Posible bug de UX — el test esperaba `/admin/actividades/usuario`. Pendiente decisión de producto.
- `CancelacionActividad` (mailable) accede `$this->actividad->nombreActividad` (sintaxis de objeto), pero `enviarNotificaciones()` le pasa un array (a propósito, por serialización de modelos borrados). El nombre saldría en null en el mail real. A revisar.

---

## 2026-06-15 — Tarea 9 (baseline) + setup de entorno de tests

**Agente:** Claude (Opus 4.8)
**Branch:** `claude/goofy-mclaren-e7f8eb`

### Resultado del baseline

Suite corrida en Docker (`laravel_app`) contra **MySQL** (`laravel_test`), PHP 7.2, Laravel 5.7.

| Momento | Tests | Passed | Errors | Failures |
|---|---|---|---|---|
| Estado inicial (SQLite `:memory:`) | — | 0 | suite no migra (ver abajo) | — |
| Tras mover a MySQL | 97 | ~19 | 69 | 9 |
| Tras fix factory `ficha_medica_campos` | 97 | ~43 | 28 | 26 |
| Tras copiar `mix-manifest.json` | 97 | **~65** | 21 | 11 |

### Decisiones de entorno (no son código de producción)

1. **Tests corren sobre MySQL, no SQLite.** La suite estaba **rota sobre SQLite desde 2022**: 7 migraciones crean un índice llamado `idPersona` (`Inscripcion`, `ficha_medicas`, `estudios`, `integrantes`, etc.). SQLite trata los nombres de índice como globales; MySQL los scope-a por tabla. En SQLite la 2ª migración explota con "index idPersona already exists". MySQL es fiel a producción y es lo que implica CLAUDE.md ("los tests requieren base de datos activa"). Se creó la BD `laravel_test` y `.env.testing` del worktree apunta a MySQL.
2. **`vendor` copiado del repo principal** (el contenedor no tiene red para `composer install`). Un symlink rompía el aislamiento (cargaba clases de `main`, no del worktree); se copió como directorio real + `composer dump-autoload` para que el autoloader resuelva rutas del worktree. Verificado: `base_path()` = worktree.
3. **Artefactos de entorno copiados al worktree** (gitignored): `public/mix-manifest.json` (sin él, toda vista Blade con `mix()` da 500), claves OAuth de Passport en `storage/`.

### Fixes aplicados (sí son código)

- **`database/factories/ActividadFactory.php`**: agregado `requiere_ficha_medica => false` y `ficha_medica_campos => []`. La migración `2023_09_26_..._incluye_campo_requiere_ficha` agregó una columna `json` NOT NULL sin default (MySQL no permite default en JSON) y el factory nunca se actualizó → todo test que creaba una Actividad fallaba en MySQL estricto. Destrabó ~24 tests.

### Fallos restantes (~32) — categorizados por causa raíz

1. **`idPaisPermitido = 0` (gap de factory)** — varios de los 14 `AuthorizationException`. `idPaisPermitido` es columna real (migración 2023-04, default 0, NOT NULL) que `PersonaFactory` no setea. `CrearPunto::authorize()` compara `provincia.id_pais == user.idPaisPermitido` → 403. Afecta puntos, campañas y features con scope por país. Mismo patrón que `ficha_medica_campos`: factory desactualizado tras migración.
2. **`count()` sobre no-Countable (4)** — comportamiento de PHP 7.2; código que cuenta algo que puede ser null/int. Tests de preinscripción/inscripción.
3. **`where` malformado `Unknown column '0'` (1)** — `update Inscripcion ... where 0 = idPersona and 1 = = and 2 = ...`. Posible bug real en código de desinscripción (array pasado mal a `where()`).
4. **`array_column() expects array, integer given` (1)** — búsqueda de actividades por tipo.
5. **Faker `email_verified_at = '1970-01-01'` inválido para TIMESTAMP (1-2)** — flakiness de `PersonaFactory` (faker genera fecha bajo el epoch de TIMESTAMP).
6. **LoginSocial 500 (3)** — credenciales Google/Facebook vacías en `.env.testing` (entorno).

### Pendiente

- Decidir, por categoría, fix de datos de test vs. fix de código vs. reportar bug real (categorías 3 y 4 huelen a bug real, no a test).
- Tras suite verde: agregar cobertura faltante (API mobile, webhook Stripe, campañas, dispositivos/push, donations) — tareas 10-18 y los gaps de `docs/upgrade-review.md`.
