# Revisión crítica del plan de upgrade Laravel 5.7 → 11.x

> Fecha de revisión: 2026-06-11
> Revisado: `docs/upgrade-laravel.md` + `tasks.json` (tareas 19–25) + `composer.json` + `phpunit.xml` + tests existentes
> Versiones verificadas contra la API de Packagist (repo.packagist.org/p2) el 2026-06-11
> Veredicto: **REQUIERE AJUSTES** (ver §5)

---

## 1. Tabla de versiones corregida

### 1.1 Dependencias que el plan sí contempla

| Dependencia | Actual | Target del plan | ¿Correcto? | Versión real recomendada | Notas |
|---|---|---|---|---|---|
| laravel/framework | 5.7.* | ^11.0 | ✅ | 11.54.0 (PHP ^8.2) | Mapa de fases correcto. |
| laravel/passport | 7.5.1 | 8→9→10→11→12 | ✅ | Igual al plan. En L11 existe también 13.x (requiere L11.35+, PHP 8.2) | Mapa por fase verificado: 8.5 (L6–L7), 9.4 (L6.18+–L7.22+), 10.4 (L8.37–L9), 11.10 (L9–L10), 12.4 (L9.21–L12). Ver breaking change omitido en §2.1. |
| spatie/laravel-permission | ^2.11 | 3→3→4→5→5→6 | ✅ | Igual al plan (6.25 soporta L8.12–L13) | El salto 2→3 tiene UPGRADING.md propio (config y cache de permisos); revisarlo en Fase 1. Alternativa: 5.x ya soporta L7–L10, permite saltar 4.x. |
| lcobucci/jwt | 3.3.3 (pin exacto) | ^4.0 en L8 | ✅ | 4.3.0 (PHP ^7.4\|^8.0) | El pin exacto hay que relajarlo desde Fase 1 (Passport 8/9 piden ^3.x). No se usa directamente en el código propio (verificado por grep) — lo gestiona Passport. |
| doctrine/dbal | ^2.7 | ^3.0 en L9 | ✅ | 3.10.5 | Desde L10 el framework ya no necesita DBAL para modificar columnas: se puede **eliminar** en Fase 5/6. No hay uso directo de Doctrine en `app/` ni `database/` (verificado). |
| maatwebsite/excel | ^3.0 | 3.1 | ✅ | 3.1.69 (soporta 5.8 → L13) | El constraint `^3.0` ya resuelve a 3.1.x solo. Atención a la API 3.0 vs 3.1 en los exports del backoffice. |
| phpunit/phpunit | ^7.0 | 8→8→9→9→10→10 | ✅ | Igual al plan (L11 acepta ^10.5\|^11) | Ver §2.5: el archivo `phpunit.xml` actual es **inválido** para PHPUnit 10. |
| stripe/stripe-php | >=7 <9 | ^10 (F2) → ^12 (F4) | ⚠️ Cuestionable | Desacoplar del upgrade | stripe-php no depende de Laravel (sin constraint de framework); el último estable es v20.2. Subir el SDK cambia el pinning de versión de API de Stripe y el comportamiento de webhooks/Checkout: es un upgrade con riesgo propio que no debería ir "de pasada" dentro de una fase de framework. Recomendación: mantener el rango actual mientras instale, y hacer un upgrade de Stripe dedicado (con sus tests de webhook) fuera de este plan. |
| laravel/socialite | ^3.0 | ^4.0 en Fase 2 y nunca más se toca | ❌ | **^5.0 desde Fase 1** | Error real: 4.4.1 llega solo hasta L7 → `composer update` **falla en Fase 3 (L8)**. 5.27.0 soporta L6→L13, un solo salto cubre todo el plan. |

### 1.2 Dependencias que el plan NO contempla (y bloquean fases)

| Dependencia | Actual | Bloquea en | Versión recomendada | Dónde se usa |
|---|---|---|---|---|
| sentry/sentry-laravel | ^0.11 | **Fase 1** (0.11 admite hasta L5.7) | **^4.0 en Fase 1** (4.26 soporta L6→L13, PHP ^7.2) | Error tracking de producción. El salto 0.11→4.x cambia config (`config/sentry.php`) e inicialización — seguir la guía de migración de Sentry. |
| laravel/telescope (dev) | ~1.0 | **Fase 1** (1.2 pide laravel ~5.7.7) | ^3.5 en F1–F2, ^5.0 desde F3 (5.20 soporta L8.37→L13) | `app/Providers/TelescopeServiceProvider.php`. Alternativa: sacarlo de require-dev si no se usa. |
| laravel/tinker | ~1.0 | **Fase 2** (1.x llega a L6) | ^2.0 desde Fase 1 (2.11 soporta L6→L12) | Tooling. |
| martinlindhe/laravel-vue-i18n-generator (dev) | ^0.1.42 | **Fase 3** (máx L7) | **ABANDONADO** — no hay versión para L8+ | Genera los JSON de traducciones para Vue. Decidir antes de Fase 3: commitear el output generado y eliminar el paquete, o reemplazar el generador. |
| rap2hpoutre/fast-excel | ^1.2.2 | **Fase 1** (1.x es de la era L5) | ^2.5 en F1 (L5.3–L8), ^5.0 desde F3 (5.7 soporta L6→L13, PHP ^8.0) | `app/Http/Controllers/backoffice/ajax/InscripcionesController.php:438` (import de Excel). |
| chencha/share | ^5.2 | **Fase 5** (máx 6.1.0 = L6–L9) | ^6.0 en F1; **eliminar antes de F5** | Solo 2 vistas: `resources/views/partials/compartir-modal.blade.php:14` y `backoffice/partials/compartir-modal.blade.php:19`. Son links de compartir a Facebook/Twitter/email: reemplazables por HTML estático trivial. |
| fideloper/proxy | ^4.0 | **Fase 5** (4.4.2 llega a L9) | Eliminar en Fase 4 | `app/Http/Middleware/TrustProxies.php` extiende `Fideloper\Proxy\TrustProxies`; desde L9 migrar a `Illuminate\Http\Middleware\TrustProxies` (incluido en el framework). |
| fzaninotto/faker (dev) | ~1.4 | Fase 3 (PHP 8) | **fakerphp/faker ^1.9.1** (fork drop-in, default de L8) | El plan lo sube a `~1.9` en Fase 1 pero del paquete **abandonado**. Swap de nombre en Fase 1, riesgo cero. |
| barryvdh/laravel-dompdf | ^0.8.0 | Según resolución del lock | **^2.0 desde Fase 1** (2.2 soporta L6→L11, PHP ^7.2\|^8.0); opcional ^3.1 al final | Generación de PDF (PostulacionesController). 2.x renombra la facade/config (`PDF` → `Pdf` en 2.x+): revisar usos al subir. |
| unisharp/laravel-filemanager | ~1.8 | Fase 1 (1.x es era L4/L5) | ^2.x (2.14 soporta L6→L13 pero exige PHP ≥8.1; usar una 2.x anterior en F1–F2) | ⚠️ Hay vistas vendor publicadas pero **no encontré rutas registradas** (`grep Lfm/filemanager routes/` vacío). Verificar si es código muerto y eliminarlo antes de Fase 1 — es el mejor escenario. |
| webpatser/laravel-uuid | ^3.0 | Fase 3 (3.0.2 es PHP ^7.0; PHP 8 necesita ^4.0; PHP 8.2 → ^5/^6) | **Eliminar**: reemplazar por `Str::uuid()` (nativo desde 5.6) | `api/PersonasController.php:183`, `ajax/UsuarioController.php`, `app/Http/Services/UserService.php` (genera `unsubscribe_token`). Tarea de mantenimiento previa, baratísima. |
| google/apiclient | ^2.0 | No bloquea | Dejar `^2.0` | PHP 8.0 necesita ≥2.12.1 y el último (2.19.3) exige PHP ^8.1, pero el constraint `^2.0` deja a composer elegir la correcta en cada fase. Se usa en `app/Services/SocialAuth/GoogleProvider.php` (login social mobile): smoke test por fase. |
| simplesoftwareio/simple-qrcode | ~4 | No bloquea | Dejar `~4` (4.2.0 = PHP >=7.2\|^8.0) | ⚠️ `QrCode::format('png')` (`app/Mail/MailInscripcionConfirmada.php:40`) requiere **ext-imagick**: incluirla al armar las imágenes Docker PHP 8.x. |
| mockery/mockery (dev) | ~1.0 | Fase 3/5 | ^1.4 en F3, ^1.6 en F5 (PHPUnit 10) | — |

**Conclusión §1:** el mapa de dependencias del plan cubre 8 paquetes; `composer.json` tiene 25. Al menos **cinco de los no contemplados hacen fallar `composer update` en Fase 1–3** (sentry, telescope, tinker, fast-excel, socialite en F3) y dos más en Fase 5 (chencha/share, fideloper/proxy). Correr `composer update --dry-run` con el composer.json propuesto de cada fase **antes** de empezar esa fase.

---

## 2. Breaking changes adicionales (omitidos en el plan)

### 2.1 Passport: `Passport::routes()` desaparece en Passport 11 (Fase 4)
`app/Providers/AuthServiceProvider.php:38` llama a `Passport::routes()`. En Passport 11 ese método **se eliminó** (las rutas se registran automáticamente). La app no bootea en Fase 4 hasta sacar esa línea. El plan no lo menciona.

Además, dos instrucciones del plan sobre Passport son **incorrectas**:
- Fase 1: *"requiere ejecutar `php artisan passport:install` de nuevo"* — no. `passport:install` **crearía clientes nuevos** (y claves si no existen); lo que corresponde en cada fase es `php artisan migrate` (las versiones nuevas agregan columnas/tablas) y **no tocar** claves ni clientes existentes. Misma corrección para el acceptance de Fase 3.
- Tokens existentes: los personal access tokens que emite la API (`createToken()` en `api/PersonasController.php`) son JWTs firmados con `storage/oauth-*.pem`; siguen siendo válidos entre versiones mientras no cambien las claves ni las tablas `oauth_*`. El salto sensible es Fase 3 (lcobucci/jwt 3→4 + league/oauth2-server nuevo): **smoke test en staging con un token viejo real** como acceptance de Fase 3.
- Dato a favor: la API **no usa password grant** (login propio con `createToken()`), así que el cambio de Passport 12 que deshabilita el password grant por defecto no afecta.

### 2.2 Serialización de fechas JSON cambia en Laravel 7 (Fase 2) — afecta a la app mobile
Desde L7, los modelos serializan fechas a ISO-8601 (`2026-06-11T15:00:00.000000Z`) en vez de `2026-06-11 15:00:00`. Eso cambia **todas** las respuestas JSON de la API mobile y los endpoints ajax que devuelven modelos con timestamps. Si la app MiTECHO parsea el formato viejo, rompe en producción.

Acción: override de `serializeDate(DateTimeInterface $date)` con el formato legacy en un base model o trait (Actividad, Inscripcion, Persona, etc.), y tests de contrato JSON (tarea 15 de tasks.json) que **fijen el formato de fecha** antes de empezar Fase 1. El criterio del Revisor ("respuestas JSON iguales") lo detectaría tarde; mejor anticiparlo.

### 2.3 Verificación de email rota en L6 por el campo `mail` (riesgo específico de App\Persona)
Esto responde la pregunta §3 del prompt: **sí hay un riesgo concreto y rompe en Fase 1.**

- Desde L6, `VerifiesEmails::verify()` (usado por `app/Http/Controllers/Auth/VerificationController.php`) valida un parámetro `{hash}` = `sha1($user->getEmailForVerification())`.
- `Persona` **no** overridea `getEmailForVerification()` → el default devuelve `$this->email`, que es **null** (la columna es `mail`).
- La ruta `routes/web.php:183` está definida sin `{hash}`, y la notificación custom (`app/Notifications/VerifyEmail.php:55`) firma la URL solo con `id`.

Resultado: todos los links de verificación fallan a partir de L6. Acción: (a) override `getEmailForVerification(): string { return $this->mail; }` en `Persona`, (b) agregar `{hash}` a la ruta y a `verificationUrl()`, (c) test de verificación en Fase 0 (la tarea 10 ya lo lista — marcarlo bloqueante de Fase 1).

El resto de la superficie `mail` vs `email` está bien cubierta: `routeNotificationForMail()` (`Persona.php:34`), `getEmailForPasswordReset()` (`Persona.php:201`) y `sendPasswordResetNotification()` ya están overrideados, y al no usarse el password grant de Passport tampoco hace falta `findForPassport()` (aunque agregarlo defensivamente es barato).

### 2.4 Los helpers `str_*`/`array_*` NO emiten deprecation en L6: **fueron eliminados**
El plan dice *"En L6 aún funcionan pero emiten deprecation"*. Falso: Laravel 6.0 los **removió** del framework (existe `laravel/helpers` para compatibilidad). Sin el reemplazo previo, Fase 1 explota con `Call to undefined function`.

Alcance real verificado: `str_random`/`str_slug`/`str_limit`/`str_singular`/`studly_case` aparecen en ~10 archivos de `app/` (p. ej. `ajax/UsuarioController.php` ×6, `api/PersonasController.php:178`, `app/Http/Services/UserService.php:19`, **los 20 Search objects** usan `studly_case()` en `app/Search/*.php`), en **4 vistas de email** (`resources/views/emails/*.blade.php` — el plan habla de "código propio" pero no de vistas), en `database/factories/UserFactory.php:21` y en la vista vendor del filemanager. Reemplazar antes o durante Fase 1, o sumar `laravel/helpers` como red.

### 2.5 phpunit.xml: dos roturas silenciosas + una fatal
- `QUEUE_DRIVER` (`phpunit.xml:29`) fue renombrado a `QUEUE_CONNECTION` en 5.8+: la suite dejará de forzar `sync` **silenciosamente** apenas se actualice `config/queue.php`.
- PHPUnit 9.3 deprecia `<filter><whitelist>` → `<coverage>`; PHPUnit 10 lo elimina.
- PHPUnit 10 **elimina** los atributos `convertErrorsToExceptions` etc. (líneas 6–8): la configuración actual es inválida en Fase 5. Usar `vendor/bin/phpunit --migrate-configuration` en cada salto de PHPUnit.

Sobre las assertions de los tests existentes (pregunta §2 del prompt): el riesgo es bajo — verificado por grep que **no** hay `setUp()` overrides (PHPUnit 8 exige `: void`), ni `assertContains` sobre strings (removido en PHPUnit 9), ni `withConsecutive` (removido en PHPUnit 10). Las anotaciones `/** @test */` siguen funcionando en PHPUnit 10. Queda el detalle de L7 y `assertExactJson` (orden de keys numéricas) que el plan ya menciona.

### 2.6 Fase 3a es infeasible como está escrita (factories)
La tarea 21 exige *"phpunit sigue verde en Laravel 7.x (no cambiar framework aún)"* con factories de clases. Imposible: `Illuminate\Database\Eloquent\Factories\Factory` y `Model::factory()` **no existen en L7**. Hay que invertir el orden — ver §4.

Detalles adicionales que ninguna tarea menciona:
- **Colisión de nombres**: existe `app/ActividadFactory.php` (`App\ActividadFactory`, builder propio usado por casi todos los Feature tests vía `app(ActividadFactory::class)`) que va a convivir con `Database\Factories\ActividadFactory`. No es error de PHP (namespaces distintos) pero es una trampa de imports en cada test migrado. Considerar renombrar el builder (p. ej. `ActividadBuilder`) como paso previo.
- Los **13+ `$this->seed('PermisosSeeder')`** con string (InscripcionesConPagoTest ×13, InscripcionesTest ×10, backofficeActividadesTest ×15, etc.) deben pasar a `Database\Seeders\PermisosSeeder::class` cuando los seeders ganen namespace.
- `composer.json` carga factories y seeds por **classmap** (`autoload.classmap`): hay que cambiarlo a PSR-4 (`Database\`) en Fase 3.
- `database/factories/UserFactory.php` es de `App\User` (código muerto según CLAUDE.md): eliminarla en vez de migrarla (conecta con la tarea 6 de mantenimiento).
- Respuesta directa a la pregunta del prompt: la migración de factories **no** requiere cambios en `CreatesApplication.php` ni `TestCase.php` (ambos triviales, verificados); los cambios van en los tests, los seeders y el autoload.

### 2.7 Config skeleton nunca se sincroniza
El plan actualiza `composer.json` por fase pero nunca menciona sincronizar `config/*.php`, `app/Exceptions/Handler.php`, middlewares skeleton, etc. contra `laravel/laravel` de cada versión. Es la fuente clásica de bugs sutiles post-upgrade (defaults nuevos que no se adoptan, keys renombradas como `QUEUE_CONNECTION`). Agregar al protocolo del Implementador: diff contra el skeleton de la versión target (o usar Laravel Shift como referencia).

---

## 3. Riesgos críticos (máximo 5, por prioridad)

1. **`composer update` de Fase 1 falla en seco por dependencias no mapeadas** (sentry 0.11, telescope ~1.0, fast-excel 1.x; tinker en F2; socialite y vue-i18n-generator en F3).
   **Acción:** adoptar la tabla §1.2 dentro del plan, y agregar al protocolo: `composer update --dry-run` con el composer.json target de la fase como paso 2.a, antes de tocar código.

2. **Regresión de contrato JSON en la API mobile por serialización de fechas (Fase 2).** Usuarios reales con la app instalada — no se puede "avisar" a los clientes.
   **Acción:** `serializeDate()` legacy + tests de contrato que fijen formato de fechas en los endpoints top de la app (tarea 15), como bloqueante de Fase 1.

3. **Fase 3 mal secuenciada (factories antes del framework).**
   **Acción:** invertir 3a/3b usando `laravel/legacy-factories` (ver §4) y actualizar tareas 21–22 de tasks.json.

4. **Passport: `Passport::routes()` en Fase 4 + instrucciones de `passport:install` erróneas + continuidad de tokens vivos.**
   **Acción:** corregir el plan (migrate sí, install no; eliminar la llamada en AuthServiceProvider en F4) y agregar como acceptance de F3 y F4: "un token emitido antes del upgrade sigue autenticando en staging".

5. **Verificación de email rota desde L6 por el campo `mail` (§2.3).**
   **Acción:** override de `getEmailForVerification()` + ruta/URL con `{hash}` + test en Fase 0.

---

## 4. Cambios recomendados al plan

1. **Reescribir Fase 3** con este orden:
   1. (En L7, verde) Renombrar `App\ActividadFactory` → `App\ActividadBuilder` (o similar) y borrar `UserFactory`.
   2. Subir a L8 + PHP 8.0 **con `laravel/legacy-factories`** (paquete oficial de Laravel): las 16 factories de closures siguen funcionando tal cual. Tests verdes → mergear.
   3. Migrar factories a clases en tandas (con tests verdes entre tandas), actualizar `seed('PermisosSeeder')` → clase, autoload classmap → PSR-4.
   4. Eliminar `laravel/legacy-factories`. Tests verdes → done.
   Esto convierte el paso más riesgoso del upgrade en dos pasos reversibles, cada uno con la suite verde.

2. **Completar el mapa de dependencias** con la tabla §1.2 (sentry, telescope, tinker, socialite ^5, fast-excel, chencha/share, fideloper/proxy, dompdf, filemanager, uuid, faker→fakerphp). Tareas previas baratas que reducen el upgrade: eliminar `webpatser/laravel-uuid` (→ `Str::uuid()`), confirmar si `unisharp/laravel-filemanager` es código muerto, reemplazar `chencha/share` por links estáticos.

3. **Fase 0, agregar:** (a) tests de contrato de formato de fechas en respuestas API; (b) test del flujo de verificación de email; (c) confirmar la versión real de PHP del runtime actual (composer.json dice `>=7.0`, el plan asume 7.2; L6 exige ≥7.2.5); (d) alinear el doc con tasks.json — el doc de Fase 0 lista 5 endpoints pero tasks.json ya tiene las tareas 12 (webhook Stripe), 13 (campañas/suscripciones) y 18 (donaciones) como bloqueantes de la cadena: el doc debería referenciarlas en vez de duplicar una lista más corta. *(Nota: el prompt de revisión menciona "tarea 13" como la de factories; en tasks.json la migración de factories es la **21** — los IDs citados están desactualizados.)*

4. **Desacoplar stripe/stripe-php** del upgrade de framework (ver §1.1). Mantener el rango actual mientras resuelva, y planificar un upgrade de SDK propio con los tests de webhook (tareas 12 y 18) ya en verde.

5. **Notas de producción, corregir:** `queue:flush` borra los **failed jobs**, no la cola pendiente. Para deployar una fase: drenar la cola (esperar que `jobs` quede vacía con los workers viejos corriendo), deployar, `php artisan queue:restart`. `queue:clear` recién existe desde L8. El resto de las notas (claves Passport, webhooks Stripe, staging first) es correcto. Sesiones: sin riesgo si `APP_KEY` no cambia; los bcrypt existentes siguen válidos.

6. **Orden de fases (respuesta a §4 del prompt):** el orden general 0→6 es correcto y **no** conviene saltar L6 ni L7 — cada upgrade guide asume la versión anterior, y con la cobertura de tests todavía en construcción el costo de pasos chicos es bajo comparado con el costo de debuggear dos majors a la vez. La única corrección de orden es la interna de Fase 3 (punto 1).

7. **Gaps de tests no mencionados en el doc del plan (respuesta a §5 del prompt):**
   - `StripeController@webhook` (`routes/web.php:620`) procesa 3 eventos (`checkout.session.completed`, `payment_intent.succeeded`, `payment_intent.payment_failed`) y **no tiene ningún test** — cubierto por la tarea 12 de tasks.json, pero el doc del plan no lo lista en Fase 0.
   - `SuscribeController` (get/checkEmail/create) y el sistema de Campaigns: **sin tests** — cubierto por tarea 13.
   - PayU legacy: **sí tiene cobertura razonable** — `InscripcionesConPagoTest` ejercita `/pagos/{id}/confirmation` y `/pagos/{id}/response` (confirmación, fuera de fecha, error de transacción).
   - **Gap que tasks.json tampoco cubre:** los exports de Excel del backoffice (maatwebsite 3.0→3.1 y fast-excel 1.x→5.x cambian de major durante el plan y nada los testea). Agregar smoke tests de export (al menos que el endpoint responda 200 y el archivo se genere).

---

## 5. Veredicto

**REQUIERE AJUSTES.**

La arquitectura del plan es sólida y no necesita rediseño: fases incrementales con gate de tests verdes, branch por fase, tests de API mobile antes de tocar nada, y el protocolo Líder/Implementador/Revisor están bien pensados. El orden de fases es el correcto.

Lo que falta para que sea ejecutable:

1. El mapa de dependencias cubre 8 de 25 paquetes; al menos cinco de los omitidos **hacen fallar composer en las Fases 1–3** (§1.2).
2. La Fase 3a es **infeasible** tal como está escrita (§2.6) — necesita `laravel/legacy-factories` y el orden invertido.
3. Tres afirmaciones factuales del plan son incorrectas: helpers `str_*` "deprecados" en L6 (fueron eliminados), `passport:install` por fase (crearía clientes nuevos), `queue:flush` para vaciar la cola (borra failed jobs).
4. Dos breaking changes con impacto directo en producción están omitidos: serialización de fechas JSON en L7 (API mobile) y `Passport::routes()` en Passport 11; más la rotura de verificación de email en L6 por el campo `mail`.

Con los ajustes de §4 incorporados al doc y a tasks.json, el plan queda en condiciones de ejecutarse fase por fase.
