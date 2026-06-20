# CLAUDE.md — Mapa del Proyecto

> Este archivo existe para que desarrolladores nuevos y agentes de IA puedan entender el sistema rápidamente. La fuente de verdad siempre es el código; este documento lo complementa con contexto que no es obvio desde los archivos solos.

---

## Qué es este sistema

Plataforma de voluntariado para **TECHO** (`actividades.techo.org`). Permite que voluntarios se inscriban a actividades (construcciones, eventos, campañas), y que coordinadores administren esas actividades desde un backoffice web.

El mismo backend cumple tres roles simultáneamente:

| Rol | Tecnología | Ruta de entrada |
|-----|-----------|----------------|
| API mobile (app MiTECHO) | JSON + Laravel Passport | `routes/api.php` |
| Backoffice web (admin) | Blade + Vue 2 | `routes/web.php` → `backoffice/` |
| Frontend público web | Blade + Vue 2 | `routes/web.php` → vistas raíz |

---

## Stack

- **Laravel 5.7** (PHP >=7.0) — EOL desde marzo 2020, pendiente de migración
- **MySQL 5.7**
- **Vue 2** (EOL diciembre 2023) — frontend web embebido en el monolito
- **Laravel Passport** 7.5.1 — autenticación API con OAuth2
- **Spatie Permission** 2.x — roles y permisos en backoffice
- **Laravel Queues** (driver: `database`) — jobs para mails y push notifications
- **OneSignal** — notificaciones push mobile
- **Stripe** — pagos de inscripciones y donaciones
- **Maatwebsite Excel** — exportaciones a .xlsx

---

## Autenticación — TRAMPA IMPORTANTE

**El modelo de autenticación es `App\Persona`, NO `App\User`.**

`config/auth.php` tiene:
```php
'providers' => ['users' => ['model' => App\Persona::class]]
```

`app/User.php` es la clase por defecto de Laravel que quedó en el repo. **No se usa para autenticación.** La única referencia activa es `RegisterController.php`, que es código muerto (el registro real va por `api\PersonasController`).

Cuando el código hace `auth()->user()`, devuelve una instancia de `Persona`.

---

## Modelos principales

### Persona (`app/Persona.php`)
Usuario del sistema. Implementa `Authenticatable + MustVerifyEmail + HasApiTokens + HasRoles + SoftDeletes`.

Campos clave: `idPersona`, `nombres`, `apellidoPaterno`, `mail`, `estadoPersona`, `recibirMails`, `recibir_push`.

> El campo de email es `mail`, no `email`. El método para Passport es `routeNotificationForMail()`.

### Actividad (`app/Actividad.php`)
Evento de voluntariado. Tabla: `Actividad`. PK: `idActividad`. Con `SoftDeletes` y auditoría automática en `updating`.

Flags de negocio importantes:
- `confirmacion` (0/1) — si requiere aprobación manual del coordinador
- `pago` (0/1) — si requiere pago para confirmar
- `estadoConstruccion` — `'Abierta'` | `'Cerrada'` | otros
- `inscripcionInterna` (0/1) — si es solo visible para internos

**⚠️ `getMiembrosAttribute()` tiene efecto secundario**: si no existe un grupo raíz, lo crea. No llamar en contextos de solo lectura masiva.

### Inscripcion (`app/Inscripcion.php`)
Relación Persona↔Actividad. Tabla: `Inscripcion`. PK: `idInscripcion`.

Campos de estado: `confirma` (0/1), `pago` (0/1), `presente` (0/1).

El estado resultante depende de la combinación de `Actividad.confirmacion`, `Actividad.pago`, `Inscripcion.confirma`, `Inscripcion.pago`. Ver `Actividad::estadoInscripcion()`.

> **Deuda conocida**: la lógica de `estadoInscripcion()` está duplicada en `Actividad.php` y en `Persona.php` con diferente granularidad. Cambios en reglas de inscripción deben tocarse en ambos lados hasta que se unifiquen.

### Campaign (`app/Campaign.php`)
Campañas de captación de voluntarios (colectas, captaciones). Sistema actual. Tabla: `campaigns` (snake_case, inglés).

### Suscribe (`app/Suscribe.php`)
Sistema legacy de suscripciones. Tabla: `Suscripciones`. Coexiste con Campaign. Pendiente de evaluación si se migra o se mantiene separado.

---

## Convención de nombres de tablas — INCONSISTENCIA CONOCIDA

El sistema tiene dos convenciones que coexisten:

| Tablas legacy | Tablas nuevas (2026) |
|--------------|---------------------|
| `PascalCase` en español | `snake_case` en inglés |
| `Actividad`, `Persona`, `Inscripcion` | `campaigns`, `donations`, `stripe_customers` |
| PK: `idActividad`, `idPersona` | PK: `id` |

Los modelos declaran explícitamente `$table` y `$primaryKey` para manejar esto. Respetá la convención de la tabla que estás tocando.

---

## Estructura de controladores

```
app/Http/Controllers/
├── ajax/                  → JSON, consumidos por Vue del frontend web propio
├── api/                   → JSON, consumidos por la app mobile (auth: Passport)
├── backoffice/            → Blade + ajax para administración interna
│   └── ajax/              → JSON, consumidos por Vue del backoffice
└── (raíz)                 → Blade públicas y flujos web legacy
```

Los controladores `ajax/` son usados tanto desde `web.php` como desde `api.php`. No asumir que un controlador en `ajax/` es solo para el frontend web.

---

## Flujo de inscripción

La clase `app/Services/InscripcionFlow.php` es la fuente de verdad del flujo. Tiene comentarios completos. Leerla antes de modificar cualquier cosa relacionada con el proceso de inscripción.

Pasos posibles (dependen de la configuración de la actividad):
1. `ficha_medica` — gate inicial si la actividad lo requiere
2. `rol`, `tipo`, `estudios`, `jornadas`, `preguntas` — pasos Vue condicionales
3. `punto_encuentro` — siempre el último paso Vue
4. `confirmar`, `pago` (condicional), `finalizar` — pasos Blade

---

## Sistema de pagos

Hay tres sistemas de pago en producción simultáneamente:

| Sistema | Controlador | Cuándo se usa |
|---------|------------|---------------|
| PayU (legacy) | `PagosController` + `app/Payments/PayU.php` | Inscripciones en países con PayU configurado |
| Stripe web | `StripeController` (344 líneas) | Inscripciones via Stripe Checkout (redirect) |
| Stripe API mobile | `api/InscripcionStripeController` + `StripePaymentService` | Pago de inscripciones desde la app |
| Donaciones Stripe | `api/DonationController` + `DonationWebhookController` | Donaciones libres y suscripciones |

La resolución del gateway de pago para inscripciones es dinámica: `Pais.config_pago` es un campo JSON en la tabla de países que indica qué clase de `app/Payments/` usar.

---

## Notificaciones push

Ver `docs/push-notifications.md` — es el documento más completo del repositorio.

Arquitectura resumida:
```
PushNotificationService → EnviarNotificacionPush (Job) → OneSignalService → OneSignal API
```

El locale se resuelve desde `persona->pais->locale`. Los textos están en `resources/lang/{locale}/push.php`.

---

## Multi-país

El sistema opera en múltiples países de Latinoamérica. La variable `APP_PAIS_DEFAULT` en `.env` determina el país activo. El middleware `SeleccionarPais` maneja el contexto por sesión.

Locales disponibles: `es_AR` (Argentina), `es_CH` (Chile y resto de LatAm), `pt` (Brasil), `en` (inglés), `es` (fallback genérico).

---

## Patrón Search Objects

Los filtros de listados usan un patrón propio en `app/Search/`. Cada clase `*Search` recibe los parámetros de request y aplica filtros encadenados usando clases en `app/Search/filters/`.

Ejemplo:
```php
ActividadesSearch::apply($request)  // devuelve un Builder paginado
```

---

## Jobs y colas

Driver: `database` (tabla `jobs`). En producción corre con Supervisor (`supervisor-worker.conf` en la raíz del proyecto).

En desarrollo local usar `QUEUE_CONNECTION=sync` en `.env` para que los jobs corran sincrónicamente sin necesitar worker.

Comandos Artisan programados (ver `app/Console/Kernel.php`):
- `actividad:recordatorio` — diario 08:00, push 24hs antes
- `push:apertura-evaluacion` — diario 09:00
- `push:recordatorio-evaluacion` — diario 09:00
- `push:recordatorio-pago` — diario 10:00
- `push:reactivacion-voluntarios` — mensual día 1 10:00

---

## Deep linking mobile

La app MiTECHO usa `mitecho://actividades/{id}` como custom URL scheme. Los archivos para Universal Links (iOS) y App Links (Android) se sirven desde:
- `/.well-known/apple-app-site-association`
- `/.well-known/assetlinks.json`

---

## Documentación de API

`docs/api.md` — referencia completa de la API mobile. Incluye flujos de Stripe, donaciones, campañas, push notifications.

> Hay también un `api.md` en la raíz del repo. El canónico es `docs/api.md`. El de la raíz es un duplicado pendiente de eliminar.

---

## Capa de reporting / analytics

`docs/reporting.md` — vistas `reporting_*` (datamart) que consume Power BI y otros: qué significa cada hecho/dimensión, el glosario canónico de métricas (movilizado, KPI, NPS, etapas del ciclo), cómo conectarse y las definiciones que **no** deben re-implementarse en BI.

> Regla clave: las métricas se definen **una sola vez** en las vistas `reporting_*`; app y Power BI leen de ahí. Nunca conectar un consumidor de analytics a las tablas operativas (PII + reglas no aplicadas). Las vistas exponen `person_key` (UUID pseudónimo, mapeo interno en `reporting_person`), no `idPersona`. RLS por país vía `reporting_acceso_usuario` (se aplica en el modelo de Power BI; ver doc). Limitación: scope a nivel país (no oficina), porque el usuario solo tiene `idPaisPermitido`.

---

## Testing

```bash
# PHP (Feature + Unit)
vendor/bin/phpunit

# Vue (mocha-webpack)
npm run test
```

Los tests PHP usan `DatabaseTestSeeder` y requieren base de datos activa. No hay mocks de BD.

Deuda conocida: los tests de push notifications (PushNotificationService, comandos cron) están pendientes. Ver `docs/push-notifications.md#deuda-técnica`.

---

## Variables de entorno relevantes

| Variable | Descripción |
|----------|-------------|
| `APP_PAIS_DEFAULT` | ID del país activo (ej: `13` = Argentina) |
| `LOCALE` | Locale por defecto (ej: `es_AR`) |
| `QUEUE_CONNECTION` | `database` en prod, `sync` en desarrollo |
| `ONESIGNAL_APP_ID` | Credencial OneSignal para push |
| `STRIPE_*` | Credenciales Stripe por país |
| `STRIPE_DONATIONS_SECRET` | Clave Stripe del módulo de donaciones (separada) |
| `SENTRY_LARAVEL_DSN` | Error tracking en producción |

---

## Deuda técnica conocida

| Deuda | Archivo | Impacto |
|-------|---------|---------|
| Laravel 5.7 sin soporte desde 2020 | `composer.json` | Seguridad |
| `App\User` es código muerto | `app/User.php`, `RegisterController.php` | Confusión |
| `estadoInscripcion()` duplicado | `Actividad.php` L95, `Persona.php` L115 | Bugs potenciales |
| IDs de categorías hardcodeados en ruta | `routes/api.php` L167-190 | Fragilidad |
| `getMiembrosAttribute()` con efecto secundario | `Actividad.php` L70 | Bugs potenciales |
| `bower_components` en git (67 MB) | `public/bower_components/` | Repo pesado |
| Dos clases Search con nombres casi iguales | `TiposActividadSearch.php` vs `TiposActividadesSearch.php` | Confusión |
| Vue 2 EOL | `resources/js/` | Deuda frontend |
| Tests de push pendientes | `docs/push-notifications.md` | Cobertura |
