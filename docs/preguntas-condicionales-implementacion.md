# Preguntas condicionales — Implementación

Resumen de lo que se construyó (refactor previo + feature, en un solo cambio).

## Qué cambió

### Capa compartida nueva
- `app/Concerns/Preguntable.php` — trait de modelo: fillable/casts comunes,
  relación `condiciones`, e **identidad estable de opciones**. Las opciones se
  guardan como `[{id,label}]`; el accessor `opciones` sigue devolviendo strings
  (compatibilidad), y se agrega `opciones_detalle` ([{id,label}]) para la lógica nueva.
- `app/PreguntaCondicion.php` + migración `..._create_pregunta_condiciones_table.php`
  — tabla polimórfica de condiciones (target_type/target_id, parent_id, operator, value).
- `app/Services/Preguntas/ConditionEvaluator.php` — fuente única de "¿pregunta visible?".
- `resources/js/conditionEvaluator.js` — espejo en cliente del evaluador.
- `app/Http/Controllers/Concerns/ManagesPreguntas.php` — CRUD compartido de preguntas
  (antes duplicado en dos controladores).
- `resources/js/components/backoffice/preguntas/PreguntasManager.vue` — modal unificado
  (reemplaza `preguntas-actividad.vue` y `CampanaPreguntas.vue`) + editor de opciones con
  id estable + sección "Filtro condicional".

### Refactor de lo existente (sin cambio de comportamiento)
- `ActividadPregunta` / `CampaignPregunta` → usan el trait `Preguntable`.
- `ActividadPreguntasController` / `CampaignPreguntasController` → usan `ManagesPreguntas`
  (rutas y bindings intactos).
- `Actividad::preguntas()` / `Campaign::preguntas()` → eager-load de `condiciones`.
- `AppServiceProvider` → `Relation::morphMap` para los alias polimórficos.
- `app.js` + ambos blades → usan `<preguntas-manager>`.
- i18n: nuevas claves en `backend.php` (4 locales) y `suscribe.missing_required`;
  también agregadas al `vue-i18n-locales.generated.js`.

### Integración en inscripción / suscripción
- `inscripcion.vue` y `suscribe.vue` → ocultan preguntas cuya condición no se cumple,
  validan requeridas solo de las visibles y no envían respuestas ocultas.
- `InscripcionesController@guardarRespuestasInscripcion` y `SuscribeController@create`
  → calculan visibilidad en el servidor y **descartan respuestas de preguntas ocultas**
  antes de guardar (el backend es la autoridad).

## Pasos de despliegue

1. `php artisan migrate` — crea `pregunta_condiciones` y backfillea las opciones
   existentes a `[{id,label}]` (idempotente; no toca respuestas).
2. `npm run prod` — recompila el bundle Vue.
   - Las claves i18n nuevas ya están en `vue-i18n-locales.generated.js`. Si en algún
     momento se regenera ese archivo con `php artisan vue-i18n:generate`, las claves
     viven también en los `resources/lang/*/backend.php` y `suscribe.php`.

## Compatibilidad / riesgos

- **App mobile y frontend actual**: sin romper. `opciones` sigue siendo array de strings;
  `opciones_detalle` y `condiciones` son campos aditivos.
- **Datos existentes**: las actividades/campañas sin condiciones se comportan idéntico
  (la visibilidad solo cambia algo cuando hay una condición configurada).
- **Cambio de comportamiento acotado**: en suscripción a campañas se agregó validación
  server-side de requeridas **solo entre las visibles** (antes solo validaba el cliente).
  Devuelve 422 con `suscribe.missing_required`. En actividades se mantiene el
  comportamiento (validación en cliente) y el backend solo descarta ocultas.

## Limpieza pendiente (opcional)

Quedaron como código muerto (ya no se importan ni se usan): `preguntas-actividad.vue` y
`CampanaPreguntas.vue`. Se pueden borrar sin riesgo.

## Tests

- `tests/Unit/ConditionEvaluatorTest.php` — cubre: solo padre visible sin respuesta,
  mostrar dependiente correcto por opción, robustez ante renombre de label, cascada y
  fail-open con padre inexistente. Correr: `vendor/bin/phpunit --filter ConditionEvaluator`.
- La lógica del evaluador JS se validó con los mismos escenarios.
