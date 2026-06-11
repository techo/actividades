# Plan de upgrade: Laravel 5.7 → 11.x

> Fecha de análisis: 2026-06-11  
> Estado actual: Laravel 5.7, PHP 7.2, Passport 7.5.1  
> Objetivo: Laravel 11.x, PHP 8.2+, dependencias modernas  
> Condición de éxito en cada fase: **todos los tests pasan en verde**

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

### Acceptance criteria
- [ ] `phpunit --testdox` corre sin errores de configuración
- [ ] Tests existentes: todos verdes (o documentados si había fallos previos)
- [ ] Nuevos tests de API mobile: mínimo los 5 endpoints listados arriba
- [ ] `init.sh` pasa

---

## Fase 1: Laravel 5.7 → 6.x + PHP 7.4
**Branch:** `upgrade/laravel-6x`  
**Riesgo:** BAJO — misma era PHP, pocos breaking changes.

### Breaking changes conocidos
1. **String helpers deprecados** — `str_*()` y `array_*()` globales reemplazados por `Str::` y `Arr::`. En L6 aún funcionan pero emiten deprecation. Buscar y reemplazar.
2. **Autorización de gates** — `Gate::before()` ahora intercepta todas las verificaciones incluyendo super-admin.
3. **Carbon 2.x** — L6 usa Carbon 2. Revisar uso de `Carbon::now()` vs `now()`.
4. **Passport 8.x** — requiere ejecutar `php artisan passport:install` de nuevo. Las claves existentes en producción siguen siendo válidas.

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
1. **Symfony 5 / HttpKernel** — los middleware reciben `Request $request` tipado más estrictamente.
2. **Mail** — `MailMessage` cambió algunas firmas de métodos.
3. **`assertExactJson`** — el orden de keys en JSON ahora importa en tests. Revisar assertions.
4. **Flysystem 1.x → 1.x** (sin cambio aún, pero prepararse).
5. **`Route::prefix()`** — pequeños cambios en cómo se concatenan prefijos.

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

### Breaking changes conocidos

#### 3.1 Factory migration (CRÍTICO)
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
- [ ] `php artisan passport:install` completa sin errores

---

## Fase 4: Laravel 8.x → 9.x
**Branch:** `upgrade/laravel-9x`  
**Riesgo:** MEDIO — Flysystem y Doctrine DBAL.

### Breaking changes conocidos

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
- **Cola de jobs**: vaciar la cola antes de deployar una fase nueva (`php artisan queue:flush`) para evitar jobs serializados con clases del framework viejo.
