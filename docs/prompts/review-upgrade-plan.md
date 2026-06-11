# Prompt: Revisión del plan de upgrade Laravel

> Uso: desde la raíz del proyecto, correr:
> `claude "$(cat docs/prompts/review-upgrade-plan.md)"`
> O pegarlo directamente en Claude Code.

---

Sos un experto en migraciones de Laravel en producción. Vas a revisar el plan de upgrade de este proyecto y producir un informe crítico.

## Contexto del proyecto

Este es un monolito Laravel en producción con usuarios activos que sirve tres roles simultáneamente: API mobile (autenticada con Passport), backoffice web (Blade+Vue 2) y frontend público web. No puede haber downtime ni regressions.

Lee estos archivos en orden antes de cualquier análisis:

1. `CLAUDE.md` — contexto técnico completo del sistema (auth, modelos, convenciones, deuda conocida)
2. `docs/upgrade-laravel.md` — el plan de upgrade que vas a revisar
3. `tasks.json` — el listado de tareas del upgrade (grupos `upgrade-fase0` a `upgrade-fase6`)
4. `composer.json` — dependencias actuales exactas
5. `phpunit.xml` — configuración de tests

Luego lee una muestra representativa de los tests existentes:
- `tests/Feature/InscripcionesConPagoTest.php` (el más complejo, 605 líneas)
- `tests/Feature/ajax/backofficeActividadesTest.php`
- `database/factories/PersonaFactory.php` (para entender el estilo actual de factories)

## Lo que tenés que revisar y reportar

### 1. Precisión de las versiones target

Para cada dependencia en el plan, verificar que las versiones target son correctas en Packagist (o desde tu conocimiento actualizado):

| Dependencia | Versión actual | Target en plan | ¿Correcto? | Versión real recomendada |
|-------------|---------------|----------------|-----------|--------------------------|
| laravel/passport | 7.5.1 | ver plan | ? | ? |
| spatie/laravel-permission | 2.11 | ver plan | ? | ? |
| lcobucci/jwt | 3.3.3 | ver plan | ? | ? |
| doctrine/dbal | 2.7 | ver plan | ? | ? |
| maatwebsite/excel | 3.0 | ver plan | ? | ? |
| stripe/stripe-php | >=7 <9 | ver plan | ? | ? |
| laravel/socialite | 3.x | ver plan | ? | ? |
| barryvdh/laravel-dompdf | 0.8.x | no está en el plan | — | ? |
| unisharp/laravel-filemanager | 1.8 | no está en el plan | — | ? |
| webpatser/laravel-uuid | 3.x | no está en el plan | — | ? |
| rap2hpoutre/fast-excel | 1.2 | no está en el plan | — | ? |

**Importante:** barryvdh/laravel-dompdf, unisharp/laravel-filemanager y webpatser/laravel-uuid NO están mencionados en el plan. ¿Son problemáticos para alguna fase?

### 2. Breaking changes que faltan en el plan

El plan cubre los breaking changes más obvios. ¿Hay algo que se haya omitido?

Específicamente:
- ¿Hay cambios en cómo Passport maneja los tokens entre versiones que afecten tokens existentes en producción?
- ¿La migración de factories (tarea 13) requiere cambios en `CreatesApplication.php` o `TestCase.php`?
- ¿`google/apiclient: ^2.0` tiene problemas de compatibilidad con PHP 8.x?
- ¿`simplesoftwareio/simple-qrcode` tiene versiones para PHP 8.x?
- ¿El paso de PHPUnit 7 → 8 → 9 → 10 tiene breaking changes en las assertions usadas en los tests existentes?

### 3. Riesgos específicos del modelo App\Persona

Este sistema usa `App\Persona` como modelo de autenticación (no `App\User`). El campo de email es `mail`, no `email`. Esto es inusual.

¿Hay algún riesgo específico de esta configuración no estándar durante el upgrade? ¿Alguna versión de Laravel o Passport asume que el modelo de auth tiene un campo `email`?

### 4. Orden de las fases — ¿es el correcto?

El plan propone: Fase 0 (tests) → L6 → L7 → factories → L8 → L9 → L10 → L11.

¿Cambiarías algo en el orden? En particular:
- ¿Es mejor migrar las factories (tarea 13) en L7 o esperar hasta L8?
- ¿Hay alguna razón para saltar L6 o L7 e ir directo de L5.7 a L8?
- ¿La separación en sub-tareas 13a (factories) y 13b (L8 upgrade) es la correcta o crea problemas?

### 5. Tests faltantes — ¿el análisis es completo?

El plan identifica como gaps críticos: API mobile sin tests, push notifications sin tests.

Leyendo el código de los controladores en `app/Http/Controllers/`, ¿hay otros gaps importantes que no se mencionan? Específicamente:
- ¿Qué cubre `StripeController` (webhook) y tiene tests?
- ¿`SuscribeController` tiene cobertura?
- ¿Los flujos de PayU legacy tienen tests?
- ¿El sistema de Campaigns tiene tests?

### 6. Riesgos de producción no cubiertos

¿Hay riesgos de producción que el plan no menciona?
- ¿Qué pasa con los jobs serializados en la cola durante el deploy de una nueva versión de Laravel?
- ¿Las claves OAuth de Passport son compatibles entre versiones?
- ¿Hay algún riesgo en la sesión de usuarios logueados durante el deploy?

## Formato del informe

Producir un informe en markdown con:

1. **Tabla de versiones corregida** — con versiones reales para todas las dependencias
2. **Breaking changes adicionales** — lista de lo que falta en el plan actual, con el archivo/línea de código afectado si podés identificarlo
3. **Riesgos críticos** — máximo 5, ordenados por prioridad, con acción concreta para cada uno
4. **Cambios recomendados al plan** — qué modificarías y por qué
5. **Veredicto**: PLAN APROBADO / REQUIERE AJUSTES / REQUIERE REDISEÑO

Guardá el informe en `docs/upgrade-review.md`.

No hagas cambios en el código ni en las tareas — solo el informe.
