# Plan de upgrade: Laravel 5.7 → 11.x

> Fecha de análisis: 2026-06-11 · Correcciones aplicadas: 2026-09-02  
> Estado actual: Laravel 5.7, PHP 7.2, Passport 7.5.1  
> Objetivo: Laravel 11.x, PHP 8.2+, dependencias modernas  
> Condición de éxito en cada fase: **todos los tests pasan en verde**

---

## ⚠️ Correcciones tras la revisión crítica (leer antes de arrancar Fase 1)

Este plan fue auditado en [`docs/upgrade-review.md`](upgrade-review.md) (versiones verificadas contra Packagist). Veredicto: **REQUIERE AJUSTES**. Las correcciones ya reflejadas en este doc:

**Estado de ejecución (act. 2026-09-07):**
- ✅ **Fase 0 COMPLETA** (tasks 9, 14–18, 45, 46 `done`; commit `56f8af6b` en `develop`, deployado a sandbox). Criterios de aceptación cumplidos:
  - Suite **300/300 verde** contra MySQL (incluye las 6 fallas pre-existentes reparadas: 3 de `BelongsToCountryScope`, 2 de `register`, 1 de `SocioExencion`, + `MailingTest` que quedó desactualizado por el refactor de mailing).
  - Tests de la API mobile en `tests/Feature/api/` (Auth, Actividades, Inscripciones, Stripe, Donaciones/webhooks, Perfil, Registro, Dispositivos, SocioExención).
  - Tests de contrato agregados por la revisión: `ContratoFechasApiTest` (formato de fecha JSON por defecto, ancla anti-ISO8601 de L7) y `VerificacionEmailWebTest` (flujo de verify por link firmado).
  - `init.sh` pasa.
- ✅ **Limpieza pre-Fase 1 hecha y committeada** — helpers globales `str_*`/`studly_case` → `\Illuminate\Support\Str::` (20 Search objects, controllers, `UserService`, 4 vistas de email); `webpatser/laravel-uuid` eliminado (→ `Str::uuid()`) de código, `composer.json` **y** `composer.lock` (sincronizados). Verde en 5.7 y ya no revienta en L6 por esos helpers.
- ⏳ **Fases 1–6 pendientes** (tasks 19–25). No arrancan hasta: (a) cerrar CI con gate de merge (task 29, `in_progress`), y (b) incorporar la §1.2 de la revisión al composer.json de Fase 1 (deps que hacen fallar `composer update`).

**Tres afirmaciones del plan original eran FALSAS (corregidas abajo):**
1. Los helpers `str_*`/`array_*` **no** están "deprecados" en L6 — fueron **eliminados**. Sin el reemplazo previo, Fase 1 explota con `Call to undefined function`. → Ya reemplazados (ver arriba).
2. `php artisan passport:install` por fase **crearía clientes/claves nuevos**. Lo correcto es `php artisan migrate` (agrega columnas/tablas) y **no tocar** claves ni clientes existentes.
3. `php artisan queue:flush` **borra los failed jobs**, no la cola pendiente. Para deployar: drenar la cola con los workers viejos, deployar, `php artisan queue:restart`.

**Breaking changes con impacto en producción que el plan original omitía** (detallados en sus fases):
- **Fase 2 (L7):** serialización de fechas JSON pasa a ISO-8601 → rompe el contrato de la app MiTECHO. Necesita `serializeDate()` legacy + tests de contrato.
- **Fase 1 (L6):** verificación de email rota por el campo `mail` vs `email` de `Persona`.
- **Fase 4 (Passport 11):** `Passport::routes()` desaparece (`AuthServiceProvider:38`).

**Mapa de dependencias incompleto:** el plan cubría 8 de 25 paquetes. La tabla de dependencias que hacen fallar `composer update` está en §1.2 de la revisión — **adoptarla en el composer.json de cada fase**. Correcciones clave: `laravel/socialite ^5` (no ^4, revienta en F3), `sentry/sentry-laravel ^4`, `laravel/telescope`, `laravel/tinker`, `rap2hpoutre/fast-excel`, `fzaninotto/faker` → `fakerphp/faker`. Y **`unisharp/laravel-filemanager` NO es código muerto** (la revisión se equivocó): lo usan los editores TinyMCE como image-picker (`/laravel-filemanager?editor=tinymce5` en `actividad.vue`, `invitacion-actividad-form.vue`, `reunion-modal.vue`). Hay que **actualizarlo** a `^2.x`, no eliminarlo.

**Regla operativa agregada:** correr `composer update --dry-run` con el composer.json target de la fase **antes** de tocar código (paso 2.a del protocolo).

---

## Contexto del sistema

Este es un monolito en producción que sirve tres roles simultáneamente:
- API mobile (app MiTECHO) — autenticada con Laravel Passport
- Backoffice web (Blade + Vue 2)
- Frontend público web (Blade + Vue 2)

Cualquier regresión en la API mobile afecta usuarios reales inmediatamente. La migración debe ser incremental, verificada con tests en cada paso, y ejecutada en branches separados que se mergean solo cuando todo está verde.

---

## Estado de los tests (baseline)

| Suite | Archivos | Líneas | Cubre |
|-------|---------|--------|-------|
| Feature/core | 10 archivos | ~2,314 líneas | Inscripciones, actividades, usuarios, grupos, mailing, evaluaciones |
| Feature/ajax | 6 archivos | ~871 líneas | Endpoints ajax del frontend web |
| Unit | 2 archivos | ~50 líneas | LoginSocial, Example |
| Vue | 4 archivos | — | Componentes Vue |
| **Total PHP** | **~18 archivos** | **~3,185 líneas** | — |

### Gaps críticos (deben cubrirse en Fase 0)
- ❌ **API mobile** (`app/Http/Controllers/api/`) — sin tests. Afecta: PersonasController, InscripcionStripeController, DonationController, CampanasController, DispositivoController.
- ❌ **Push notifications** — sin tests (documentado en CLAUDE.md como deuda conocida)
- ⚠️ **Flujo de pago Stripe** — parcialmente cubierto en InscripcionesConPagoTest pero sin mock de webhook

---

## Mapa de dependencias críticas por fase

| Dependencia | Actual | L6.x | L7.x | L8.x | L9.x | L10.x | L11.x |
|-------------|--------|------|------|------|------|-------|-------|
| PHP | 7.2 | 7.4 | 7.4 | 8.0 | 8.0 | 8.1 | 8.2 |
| laravel/framework | 5.7.* | 6.x | 7.x | 8.x | 9.x | 10.x | 11.x |
| laravel/passport | 7.5.1 | 8.x | 9.x | 10.x | 11.x | 12.x | 12.x |
| spatie/laravel-permission | 2.11 | 3.x | 3.x | 4.x | 5.x | 5.x | 6.x |
| lcobucci/jwt | 3.3.3 (pinned) | 3.x | 3.x | 4.x | 4.x | 4.x | 4.x |
| doctrine/dbal | 2.7 | 2.x | 2.x | 2.x | 3.x | 3.x | 3.x |
| maatwebsite/excel | 3.0 | 3.1 | 3.1 | 3.1 | 3.1 | 3.1 | 3.1 |
| phpunit/phpunit | 7.x | 8.x | 8.x | 9.x | 9.x | 10.x | 10.x |
| stripe/stripe-php | >=7 <9 | >=7 <9 | >=10 | >=10 | >=12 | >=12 | >=12 |

---

## Protocolo del agent loop para cada fase

Cada fase es una tarea en `tasks.json`. El ciclo es:

```
1. LÍDER lee:
   - Esta doc (sección de la fase)
   - La tarea en tasks.json (acceptance criteria)
   - CLAUDE.md
   → Escribe plan en progress/current.md
   → Crea branch: git checkout -b upgrade/laravel-Nx

2. IMPLEMENTADOR ejecuta:
   a. Actualiza composer.json según las versiones target de la fase
   b. docker compose exec app composer update
   c. Corre tests: docker compose exec app vendor/bin/phpunit
   d. Itera: fix breaking changes → run tests → repeat
   e. Actualiza phpunit.xml si el formato cambió en esta versión
   f. NO mergea hasta que tests estén verdes

3. REVISOR valida:
   - Tests 100% verdes (no skip silenciosos)
   - No hay deprecated notices que silencien errores reales
   - Backward compat de API: respuestas JSON iguales
   - init.sh pasa
   → APROBADO: merge a main + marcar task done
   → REQUIERE CAMBIOS: devuelve al Implementador
```

**Regla de oro:** si los tests no pasan en verde al final de la fase, **no se mergea**. El branch vive hasta que esté listo.

---

## Fase 0: Pre-upgrade — baseline y cobertura de API mobile
**Branch:** `pre-upgrade/api-tests`  
**PHP:** 7.2 (sin cambiar)  
**Objetivo:** tener tests verdes confirmados + cubrir los endpoints de la API mobile antes de tocar nada.

### Paso 0.1 — Confirmar baseline
```bash
docker compose exec app vendor/bin/phpunit --testdox
```
Documentar en `progress/current.md` cuántos tests pasan, cuántos fallan, cuántos se saltean.

### Paso 0.2 — Tests para API mobile
Crear `tests/Feature/api/` con tests para:
- `POST /api/login` (PersonasController)
- `GET /api/actividades` (filtros y paginación)
- `POST /api/inscripciones/{id}` (flujo completo)
- `GET /api/me` (perfil del usuario autenticado)
- `POST /api/dispositivos` (registro de push token)

Usar `Passport::actingAs($persona)` para autenticación en tests de API.

### Acceptance criteria — ✅ COMPLETA (2026-09-07)
- [x] `phpunit --testdox` corre sin errores de configuración
- [x] Tests existentes: todos verdes (300/300; 6 fallas pre-existentes reparadas)
- [x] Nuevos tests de API mobile: los 5 endpoints + contrato de fechas + verificación de email
- [x] `init.sh` pasa

---

## Fase 1: Laravel 5.7 → 6.x + PHP 7.4
**Branch:** `upgrade/laravel-6x`  
**Riesgo:** BAJO — misma era PHP, pocos breaking changes.

### Breaking changes conocidos
1. **String helpers ELIMINADOS (no deprecados)** — `str_*()` y `array_*()` globales fueron **removidos** del framework en L6.0; sin reemplazo, revienta con `Call to undefined function`. ✅ **Ya reemplazados** por `\Illuminate\Support\Str::` en código propio (ver bloque de correcciones arriba). Verificar que no reaparezcan en código nuevo antes de subir.
2. **Autorización de gates** — `Gate::before()` ahora intercepta todas las verificaciones incluyendo super-admin.
3. **Carbon 2.x** — L6 usa Carbon 2. Revisar uso de `Carbon::now()` vs `now()`.
4. **Passport 8.x** — correr `php artisan migrate` (las versiones nuevas agregan columnas/tablas a `oauth_*`). **NO** correr `passport:install` (crearía clientes y claves nuevos). Las claves (`storage/oauth-*.pem`) y los tokens existentes siguen válidos mientras no se toquen.
5. **Verificación de email rota por el campo `mail`** — `Persona` usa la columna `mail`, no `email`. Desde L6, `VerifiesEmails::verify()` valida `{hash}` = `sha1($user->getEmailForVerification())`, que devuelve `$this->email` = **null**. Todos los links de verificación fallan. Acción: (a) override `getEmailForVerification(): string { return $this->mail; }` en `Persona`, (b) agregar `{hash}` a la ruta (`routes/web.php:183`) y a `verificationUrl()` (`app/Notifications/VerifyEmail.php`), (c) test de verificación como bloqueante de esta fase.

### Cambios en composer.json
```json
"php": ">=7.4.0",
"laravel/framework": "^6.0",
"laravel/passport": "^8.0",
"spatie/laravel-permission": "^3.0",
"phpunit/phpunit": "^8.0",
"fzaninotto/faker": "~1.9"
```

### Acceptance criteria
- [ ] `composer update` sin conflictos
- [ ] `phpunit` 100% verde
- [ ] No hay `str_*()` ni `array_*()` en código propio (solo en vendor)
- [ ] Login con Passport sigue funcionando (test de API)

---

## Fase 2: Laravel 6.x → 7.x
**Branch:** `upgrade/laravel-7x`  
**Riesgo:** BAJO — cambios principalmente en middleware y mail.

### Breaking changes conocidos
0. **🔴 Serialización de fechas JSON → ISO-8601 (impacto directo en la app MiTECHO)** — desde L7 los modelos serializan fechas como `2026-06-11T15:00:00.000000Z` en vez de `2026-06-11 15:00:00`. Cambia **todas** las respuestas JSON de la API mobile y los endpoints ajax que devuelven modelos con timestamps. Si la app parsea el formato viejo, rompe en producción y no se le puede "avisar" a los clientes instalados. Acción: override `serializeDate(DateTimeInterface $date)` con el formato legacy en un base model/trait (Actividad, Inscripcion, Persona…) + **tests de contrato que fijen el formato de fecha antes de esta fase**.
1. **Symfony 5 / HttpKernel** — los middleware reciben `Request $request` tipado más estrictamente.
2. **Mail** — `MailMessage` cambió algunas firmas de métodos.
3. **`assertExactJson`** — el orden de keys en JSON ahora importa en tests. Revisar assertions.
4. **Flysystem 1.x → 1.x** (sin cambio aún, pero prepararse).
5. **`Route::prefix()`** — pequeños cambios en cómo se concatenan prefijos.
6. **`laravel/socialite ^5`** (no ^4) — 4.x llega solo hasta L7 y hace fallar `composer update` en Fase 3. Subir a ^5.0 ya desde Fase 1 cubre todo el plan de una.

### Cambios en composer.json
```json
"laravel/framework": "^7.0",
"laravel/passport": "^9.0",
"laravel/socialite": "^4.0"
```

### Acceptance criteria
- [ ] `phpunit` 100% verde
- [ ] Mailing tests pasan (MailingTest.php)
- [ ] Socialite login sigue funcionando

---

## Fase 3: Laravel 7.x → 8.x + PHP 8.0
**Branch:** `upgrade/laravel-8x`  
**Riesgo:** ALTO — factory migration es obligatoria y extensa.

> ### 🔧 Secuenciación corregida (la Fase 3a original era infeasible)
> El plan original pedía migrar factories a clases **estando en L7**. Imposible: `Illuminate\Database\Eloquent\Factories\Factory` y `Model::factory()` **no existen en L7**. Orden correcto (cada paso deja la suite verde y es reversible):
> 1. **(En L7, verde)** Renombrar `App\ActividadFactory` → `App\ActividadBuilder` (builder propio usado por casi todos los Feature tests vía `app(ActividadFactory::class)`) para evitar colisión con `Database\Factories\ActividadFactory`. **Eliminar** `database/factories/UserFactory.php` (es de `App\User`, código muerto).
> 2. **Subir a L8 + PHP 8.0 con `laravel/legacy-factories`** — las factories de closures siguen funcionando tal cual. Tests verdes → mergear.
> 3. **Migrar factories a clases en tandas** (tests verdes entre tandas). En paralelo: `seed('PermisosSeeder')` (string, ×13+ en tests) → `Database\Seeders\PermisosSeeder::class`; seeders ganan namespace `Database\Seeders`; `autoload.classmap` de `composer.json` → PSR-4 (`Database\`).
> 4. **Eliminar `laravel/legacy-factories`.** Tests verdes → done.
>
> No requiere cambios en `CreatesApplication.php` ni `TestCase.php`. `fzaninotto/faker` (abandonado) → `fakerphp/faker` (fork drop-in, default de L8).

### Breaking changes conocidos

#### 3.1 Factory migration (CRÍTICO — ver secuenciación corregida arriba)
L8 reemplaza las factories basadas en closures por clases. Todos los archivos en `database/factories/` deben reescribirse.

Antes (actual):
```php
$factory->define(App\Persona::class, function (Faker $faker) {
    return [ ... ];
});
```

Después (L8+):
```php
namespace Database\Factories;
class PersonaFactory extends Factory {
    protected $model = \App\Persona::class;
    public function definition(): array { return [ ... ]; }
}
```

Y los tests deben cambiar de:
```php
factory('App\Persona')->create()
```
a:
```php
\App\Persona::factory()->create()
```

**Archivos afectados:** los 17 archivos en `database/factories/`.  
**Tests afectados:** prácticamente todos los Feature tests usan factories.

Este es el cambio más extenso de todo el upgrade. Dedicarle una sub-tarea separada.

#### 3.2 Seeders
Los seeders ahora tienen namespace `Database\Seeders` y usan `use Illuminate\Database\Seeder`.

#### 3.3 PHP 8.0
- `match` es palabra reservada (si hay variables con ese nombre)
- Named arguments disponibles (no breaking, pero nuevo)
- `str_contains()`, `str_starts_with()`, `str_ends_with()` nativas (no breaking)
- Deprecación de `create_function()`, `each()`

#### 3.4 Spatie Permission 4.x
```php
// Antes
$persona->givePermissionTo('ver_backoffice');
// Después — igual, pero la config de la clase model cambió
```
Publicar y actualizar config: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

#### 3.5 lcobucci/jwt 4.x (requerido por Passport 10.x)
JWT 4.x tiene una API completamente distinta. Passport 10.x lo maneja internamente, no debería requerir cambios en código propio a menos que el proyecto use JWT directamente.

Verificar: `grep -r "lcobucci\jwt" app/`

### Cambios en composer.json
```json
"php": ">=8.0.0",
"laravel/framework": "^8.0",
"laravel/passport": "^10.0",
"spatie/laravel-permission": "^4.0",
"lcobucci/jwt": "^4.0",
"phpunit/phpunit": "^9.0",
"stripe/stripe-php": "^10.0"
```

### Sub-tareas recomendadas
- `upgrade-8x-factories`: migrar las 17 factories a clases
- `upgrade-8x-seeders`: actualizar seeders al nuevo namespace
- `upgrade-8x-composer`: actualizar dependencias y fix breaking changes restantes

### Acceptance criteria
- [ ] Las 17 factories migradas a clases en `Database\Factories\`
- [ ] Todos los Feature tests actualizados con `Model::factory()`
- [ ] Seeders con namespace correcto
- [ ] `phpunit` 100% verde
- [ ] `php artisan migrate` aplica las migraciones nuevas de Passport (NO `passport:install`)
- [ ] Smoke test en staging: un token emitido **antes** del upgrade sigue autenticando (salto sensible lcobucci/jwt 3→4 + league/oauth2-server nuevo)

---

## Fase 4: Laravel 8.x → 9.x
**Branch:** `upgrade/laravel-9x`  
**Riesgo:** MEDIO — Flysystem y Doctrine DBAL.

### Breaking changes conocidos

#### 4.0 `Passport::routes()` eliminado (CRÍTICO — la app no bootea sin este fix)
`app/Providers/AuthServiceProvider.php:38` llama a `Passport::routes()`. En Passport 11 ese método **fue eliminado** (las rutas se registran automáticamente). Quitar esa línea o la app no arranca en esta fase. La API **no usa password grant** (usa `createToken()`), así que el cambio de Passport 12 que deshabilita el password grant por defecto no afecta.

#### 4.1 Flysystem 3.x
`Storage::url()`, `Storage::path()` cambian ligeramente. Si el proyecto usa `Storage::` directamente, revisar.

Buscar: `grep -r "Storage::" app/`

#### 4.2 Doctrine DBAL 3.x
Se usa en `doctrine/dbal: ^2.7` para columnas modificables en migrations. DBAL 3.x tiene breaking changes en su API interna, pero Laravel 9 los abstrae. El problema es si hay migrations que usan tipos de DBAL directamente.

Buscar: `grep -r "Doctrine" app/ database/`

#### 4.3 PHP mínimo 8.0
Ya lo tenemos desde Fase 3, no hay cambio adicional.

#### 4.4 Validación de arrays
`$request->validate()` con notación de punto en arrays ahora es más estricta.

### Cambios en composer.json
```json
"laravel/framework": "^9.0",
"laravel/passport": "^11.0",
"doctrine/dbal": "^3.0",
"spatie/laravel-permission": "^5.0"
```

### Acceptance criteria
- [ ] `composer update` sin conflictos
- [ ] `phpunit` 100% verde
- [ ] Storage tests pasan
- [ ] No hay uso de API deprecated de DBAL

---

## Fase 5: Laravel 9.x → 10.x + PHP 8.1
**Branch:** `upgrade/laravel-10x`  
**Riesgo:** BAJO — cambios principalmente en tipos y enums.

### Breaking changes conocidos
1. **PHP 8.1 mínimo** — update Docker.
2. **Symfony 6.x** — HTTP foundation actualizado, cambios menores en middleware.
3. **`Closure::fromCallable()`** — eliminado en favor de sintaxis nativa `Closure::fromCallable()` → `...&$closure`.
4. **PHPUnit 10** — la sintaxis de `setUp()` requiere `void` return type.

### Cambios en Docker
```dockerfile
# app.dockerfile (si existe) o docker-compose
FROM php:8.1-fpm
```

### Cambios en composer.json
```json
"php": ">=8.1.0",
"laravel/framework": "^10.0",
"laravel/passport": "^12.0",
"phpunit/phpunit": "^10.0"
```

### Acceptance criteria
- [ ] Docker corre PHP 8.1
- [ ] `phpunit` 100% verde con PHPUnit 10
- [ ] No hay warnings de tipos en PHP 8.1

---

## Fase 6: Laravel 10.x → 11.x + PHP 8.2
**Branch:** `upgrade/laravel-11x`  
**Riesgo:** BAJO-MEDIO — estructura simplificada opcional, algunos cambios en config.

### Breaking changes conocidos
1. **PHP 8.2 mínimo** — update Docker.
2. **Estructura de directorios simplificada** — L11 mueve `app/Http/Kernel.php`, `app/Console/Kernel.php` a bootstrap. Si se mantiene la estructura existente, funciona con el modo de compatibilidad.
3. **`config/` simplificado** — muchos archivos de config se consolidan. L11 es compatible con configs existentes.
4. **Middleware** — ahora se registran en `bootstrap/app.php`. L11 soporta el modo antiguo con `Kernel.php`.

**Recomendación:** no migrar a la estructura nueva de L11 en este paso. Usar el modo de compatibilidad y migrar la estructura por separado si se desea. Es una tarea cosmética, no de seguridad.

### Cambios en Docker
```dockerfile
FROM php:8.2-fpm
```

### Cambios en composer.json
```json
"php": ">=8.2.0",
"laravel/framework": "^11.0"
```

### Acceptance criteria
- [ ] Docker corre PHP 8.2
- [ ] `phpunit` 100% verde
- [ ] `php artisan route:list` sin errores
- [ ] `php artisan config:cache` sin errores
- [ ] API mobile responde correctamente (smoke test manual o automatizado)

---

## Checklist de verificación post-upgrade completo

Una vez en L11:

```bash
# En Docker
docker compose exec app php artisan --version        # debe ser 11.x
docker php --version                                 # debe ser 8.2.x
docker compose exec app vendor/bin/phpunit --testdox # todos verdes
docker compose exec app php artisan route:list       # sin errores
docker compose exec app php artisan config:cache     # sin errores
docker compose exec app php artisan optimize:clear
```

Verificar manualmente:
- [ ] Login via app mobile (Passport token)
- [ ] Inscripción completa desde frontend web
- [ ] Panel backoffice accesible
- [ ] Push notification de prueba
- [ ] Pago Stripe en staging

---

## Notas de producción

- **Nunca hacer el upgrade directamente en `main`**. Cada fase en su propio branch.
- **Staging first**: deployar cada fase a `sandbox.actividades.techo.org` antes de producción.
- **Passport keys**: las claves OAuth en `storage/` no necesitan regenerarse durante el upgrade. Solo si se cambia el algoritmo de firma.
- **Stripe webhooks**: no se ven afectados por el upgrade de Laravel. La URL del webhook no cambia.
- **Cola de jobs**: `queue:flush` borra los **failed jobs**, NO la cola pendiente. Para deployar una fase sin jobs serializados con clases del framework viejo: drenar la cola (esperar que la tabla `jobs` quede vacía con los workers viejos corriendo), deployar, y `php artisan queue:restart`. `queue:clear` recién existe desde L8.
- **`composer update --dry-run`**: correrlo con el composer.json target de la fase **antes** de tocar código (detecta en seco los conflictos de dependencias de §1.2 de la revisión).
