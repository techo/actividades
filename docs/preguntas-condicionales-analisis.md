# Preguntas condicionales — Análisis y propuesta de diseño

> Documento de diseño previo a la implementación. Fuente de verdad: el código. Este
> documento resume el estado actual y propone una solución reutilizable, segura y
> compatible hacia atrás.

---

## 1. Arquitectura actual del sistema de preguntas

Hoy existen **dos sistemas de preguntas paralelos y completamente duplicados**, uno por
módulo. No comparten ni una línea de lógica: son copias casi idénticas con distinta
nomenclatura. Hay además un tercer sistema de preguntas no relacionado (Evaluaciones)
que usa otras tablas y queda fuera de alcance.

### 1.1 Tablas

| Módulo | Tabla de preguntas | Tabla de respuestas | Convención |
|--------|--------------------|---------------------|------------|
| Actividades | `actividad_preguntas` | `inscripcion_respuestas` | snake_case nuevo (2026) |
| Campañas | `campaign_preguntas` | `suscribe_respuestas` | snake_case nuevo (2026) |

Esquema de `actividad_preguntas` / `campaign_preguntas` (idéntico salvo la FK):

```
id              increments (PK)
actividad_id / campaign_id   FK
pregunta        string(500)
descripcion     text/string(1000) nullable
tipo            enum('abierta','desplegable')   ← solo dos tipos hoy
opciones        json nullable                   ← array de strings: ["Zona Norte", ...]
requerida       boolean default false
orden           integer default 0
timestamps
```

Esquema de respuestas (`inscripcion_respuestas` / `suscribe_respuestas`):

```
id              increments (PK)
inscripcion_id / suscripcion_id   FK (index, sin FK formal en inscripcion_respuestas)
pregunta_id     index (sin FK formal en inscripcion_respuestas)
respuesta       text nullable    ← se guarda el TEXTO de la respuesta elegida
timestamps
```

### 1.2 Modelos

- `App\ActividadPregunta` → `belongsTo Actividad`. Cast `opciones => array`.
- `App\CampaignPregunta` → `belongsTo Campaign`. Cast `opciones => array`.
- `App\InscripcionRespuesta` → `belongsTo Inscripcion` + `belongsTo ActividadPregunta`.
- `App\SuscribeRespuesta` → `belongsTo Suscribe` + `belongsTo CampaignPregunta`.
- `Actividad::preguntas()` → `hasMany(...)->orderBy('orden')`.
- `Campaign::preguntas()` → `hasMany(...)->orderBy('orden')`.

### 1.3 Controladores (CRUD de configuración — backoffice)

Dos controladores ajax casi idénticos, ambos con la misma validación
(`tipo in:abierta,desplegable`, `opciones nullable|array`):

- `backoffice/ajax/ActividadPreguntasController` — rutas `/admin/ajax/actividades/{actividad}/preguntas`
- `backoffice/ajax/CampaignPreguntasController` — rutas `/admin/ajax/campanas/{campana}/preguntas`

### 1.4 Componentes Vue / Blade

**Configuración (backoffice):**

- `preguntas-actividad.vue` y `CampanaPreguntas.vue` — **modales casi idénticos** (mismo
  markup, mismos campos, misma lógica). Editan pregunta/descripcion/tipo/opciones/
  requerida/orden. Las opciones se editan con `vue-tags-input` → array de strings.

**Inscripción (consumo):**

- Actividades: `inscripcion.vue` — paso `preguntas`. Renderiza `actividad.preguntas`,
  arma `respuestas` como **array posicional** (`{pregunta_id, respuesta}` indexado por
  posición). Validación de requeridas **solo en cliente** (`avanzarPreguntas()`).
- Campañas: `suscribe.vue` — renderiza `preguntas`, arma `respuestas` como **objeto
  indexado por `pregunta.id`**. Usa `:required` del HTML nativo.

En ambos, el `<select>` de desplegable hace `:value="opcion"` (string) → **se envía y se
guarda el texto visible de la opción**.

### 1.5 Flujo de guardado y validación (estado real)

- Actividades: `InscripcionesController@create` → `guardarRespuestasInscripcion()` hace
  `updateOrCreate` por cada `{pregunta_id, respuesta}`. **No valida requeridas en
  servidor.**
- Campañas: `SuscribeController@create` → `SuscribeRespuesta::create` por cada respuesta.
  **No valida requeridas en servidor.** La API mobile (`api/CampanasController@suscribir`)
  delega en el mismo `SuscribeController@create`.
- `InscripcionFlow.php` define el orden de pasos de la inscripción de actividades; el paso
  `preguntas` se incluye si `actividad->preguntas()->exists()`.

> **Hallazgo crítico:** hoy **no hay validación de requeridas en el backend** en ninguno
> de los dos módulos. La obligatoriedad es puramente client-side. Esto importa para el
> diseño: agregar lógica condicional es la oportunidad natural para cerrar también este
> hueco, pero hay que hacerlo con cuidado para no rechazar inscripciones que hoy pasan.

### 1.6 Lógica reutilizable hoy

**Ninguna.** Cada módulo tiene su tabla, su modelo, su controlador, su modal Vue y su
render de inscripción, todos duplicados. Cualquier feature nueva (como esta) se
duplicaría dos veces si no se introduce una capa compartida.

---

## 2. Impacto del cambio

### 2.1 Archivos afectados

**Backend (nuevos):**

- Migración de la tabla de condiciones.
- (Opción A robusta) migración para dar identidad estable a las opciones.
- `App\PreguntaCondicion` (modelo nuevo).
- `App\Services\Preguntas\ConditionEvaluator` (servicio compartido nuevo).

**Backend (modificados):**

- `ActividadPreguntasController` y `CampaignPreguntasController` — aceptar/persistir la
  condición al crear/editar una pregunta (extensión aditiva del payload).
- `InscripcionesController@create` (`guardarRespuestasInscripcion`) — purgar respuestas de
  preguntas ocultas y validar requeridas solo de visibles.
- `SuscribeController@create` — idem para campañas.
- `app/Actividad.php` y `app/Campaign.php` — eager-load de condiciones en `preguntas()` o
  relación nueva `condiciones`.
- `routes/web.php` — sin cambios si la condición viaja dentro del payload de la pregunta.

**Frontend (modificados):**

- `preguntas-actividad.vue` y `CampanaPreguntas.vue` — sección "Filtro condicional" en el
  modal.
- `inscripcion.vue` y `suscribe.vue` — evaluación reactiva de visibilidad.
- (Opción A) ambos selects de desplegable en inscripción para trabajar con identidad de
  opción además del texto.
- Idealmente: un módulo JS compartido `conditionEvaluator.js` espejo del servicio PHP.

### 2.2 Riesgos

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Cambiar el shape de `opciones` rompe el render actual y datos guardados | Alta | Accessor que normaliza ambos shapes; migración de datos idempotente; no tocar `respuesta` (sigue siendo texto) |
| Agregar validación server-side de requeridas rechaza inscripciones que hoy pasan | Media | Validar **solo visibles**; desplegar detrás de la misma config; el cliente ya exigía requeridas, así que el universo afectado es chico |
| Array posicional de `respuestas` en `inscripcion.vue` se desalinea al ocultar preguntas | Media | No reordenar el array: solo marcar visibilidad y filtrar en submit; conservar índices |
| Compatibilidad con app mobile (consume `with('preguntas')`) | Media | La condición viaja como campo adicional del JSON de pregunta; clientes viejos lo ignoran (backward compatible) |
| Duplicar la lógica una vez más entre los dos módulos | Media | Extraer `ConditionEvaluator` compartido desde el día uno |
| Referencia a opción por texto se rompe al renombrar la opción | Alta | Ver sección 4 (identidad estable de opciones) |

### 2.3 Módulos afectados

Actividades (web + app mobile) y Campañas (web + app mobile). Evaluaciones **no** se
toca. El "nuevo frontend" que consumirá las APIs hereda el contrato extendido sin cambios
incompatibles.

---

## 3. Diseño propuesto

### 3.1 Principio rector

Una pregunta es **visible** si **no tiene condición** o si **su condición se cumple**
dado el conjunto de respuestas actuales. La evaluación es idéntica en cliente (UX
reactiva) y en servidor (autoridad de validación y guardado). Una sola definición de
"¿se cumple la condición?", compartida por ambos módulos.

### 3.2 Identidad estable de opciones (el punto de robustez)

**Problema:** hoy una opción es un string (`"Zona Norte"`) y la respuesta guarda ese
mismo string. Si una condición apunta al texto `"Zona Norte"` y mañana se renombra a
`"Norte"`, la condición se rompe silenciosamente.

**Solución recomendada:** darle a cada opción un **identificador estable** que no cambie
nunca, aunque cambie su etiqueta visible. La condición referencia ese id, no el texto.

Nuevo shape de `opciones` (objeto por opción):

```json
[
  { "id": "opt_a1b2", "label": "Zona Norte" },
  { "id": "opt_c3d4", "label": "Zona Sur" },
  { "id": "opt_e5f6", "label": "Zona Centro" }
]
```

- El `id` se genera al crear la opción y **es inmutable**.
- El `label` es editable libremente sin romper condiciones.
- Backward compatibility: un **accessor** en el modelo normaliza el shape viejo
  (`["Zona Norte", ...]`) al nuevo en lectura, y una **migración de datos idempotente**
  reescribe las filas existentes asignando ids una sola vez. El render que hoy hace
  `{{ opcion }}` se adapta a `{{ opcion.label }}`.
- La columna `respuesta` **no cambia**: sigue guardando el texto (`label`) por
  legibilidad en exports y compatibilidad con datos históricos. La condición se evalúa
  por `id`, resuelto contra la configuración viva de la pregunta en el momento de
  inscribirse (el frontend siempre conoce el `id` de la opción seleccionada).

> Alternativa de menor esfuerzo (mayor riesgo a futuro): mantener `opciones` como strings
> y referenciar por texto, aceptando que renombrar una opción rompe la condición. **No
> recomendada** dado que el requisito de robustez es explícito.

### 3.3 Modelo de datos de las condiciones

Se evaluaron dos estrategias, ordenadas de menor a mayor alcance:

**Opción 1 — columna JSON `condicion` en cada tabla de preguntas (menor esfuerzo).**
Agregar `condicion json nullable` a `actividad_preguntas` y `campaign_preguntas`. Contiene
`{ "parent_id": 12, "operator": "equals", "value": "opt_a1b2" }`. Aditivo, cero impacto
en filas existentes. Para AND/OR a futuro el objeto pasa a array → implica versionar el
shape.

**Opción 2 — tabla dedicada polimórfica `pregunta_condiciones` (recomendada).** Una fila
por condición, apuntando polimórficamente a la pregunta destino. Escala a múltiples
condiciones, AND/OR, grupos y secciones **sin migraciones traumáticas** (solo se agregan
filas y, a futuro, columnas nullable). Es la opción alineada con el roadmap declarado.

```
pregunta_condiciones
  id            increments PK
  target_type   string   ← morphMap: 'actividad_pregunta' | 'campaign_pregunta'
  target_id     unsigned ← pregunta que se muestra condicionalmente
  parent_id     unsigned ← pregunta de la que depende (mismo módulo, mismo PK 'id')
  operator      string default 'equals'   ← extensible: not_equals, in, ...
  value         string   ← id de opción esperado (no el texto)
  created_at / updated_at
  index (target_type, target_id)
```

Notas de diseño forward-looking (no se implementan en v1, pero la tabla las admite sin
dolor):

- **Múltiples condiciones / AND-OR:** hoy 1 fila por `target` = dependencia simple.
  Mañana N filas + columnas nullable `logic_operator` ('AND'|'OR') y `group_id` →
  combinaciones. Agregar columnas nullable es no-disruptivo.
- **Grupos / secciones condicionales:** el `target_type` polimórfico permite que mañana
  un `question_group` sea target con el mismo mecanismo.
- **Skip logic / obligatoriedad condicional:** se modelan como nuevos `operator`/efectos
  sobre la misma estructura.

Se usa `morphMap` (alias cortos) para no acoplar la BD a namespaces PHP y poder renombrar
clases sin migrar datos. No hay FK formal en el morph (estándar Laravel); la integridad se
cuida en aplicación, consistente con que `inscripcion_respuestas` tampoco tiene FK formal.

### 3.4 Relaciones

```php
// PreguntaCondicion
public function target()  { return $this->morphTo(); }                  // ActividadPregunta | CampaignPregunta
public function parent()  { return $this->belongsTo(<mismo tipo>, 'parent_id'); }

// ActividadPregunta / CampaignPregunta
public function condiciones() { return $this->morphMany(PreguntaCondicion::class, 'target'); }
```

`Actividad::preguntas()` y `Campaign::preguntas()` agregan `->with('condiciones')` para no
generar N+1 en inscripción.

### 3.5 Servicio compartido `ConditionEvaluator`

Núcleo único, sin estado, reutilizable por ambos módulos y por validación + guardado:

```php
// Dado el set de preguntas (con sus condiciones) y el mapa de respuestas
// (pregunta_id => id_de_opcion_elegida), devuelve los ids de preguntas visibles.
ConditionEvaluator::visibles(Collection $preguntas, array $respuestasPorId): array
ConditionEvaluator::esVisible(Pregunta $p, array $respuestasPorId): bool
```

Reglas v1:
- Sin condición → visible.
- `operator = equals` → visible si la opción elegida en `parent_id` == `value`.
- Si la pregunta padre está **oculta**, la dependiente también (cascada — se resuelve en
  orden topológico simple; para v1 basta una pasada porque solo hay dependencia de un
  nivel, pero el servicio contempla la cascada).

El mismo algoritmo se porta a `resources/js/.../conditionEvaluator.js` para la UX
reactiva. Una sola fuente conceptual, dos implementaciones mínimas y espejadas.

### 3.6 Estrategia frontend

**Configuración (modales backoffice):** agregar la sección "Filtro condicional" (como en
la captura de referencia):

```
[ ] Mostrar esta pregunta solo si se cumple una condición
    Pregunta:  [ desplegable con preguntas anteriores tipo desplegable/radio ]
    Respuesta: [ desplegable con las opciones de la pregunta elegida ]
```

- El selector de "Pregunta" lista **solo preguntas anteriores** (`orden` menor) de tipo
  desplegable, evitando ciclos por construcción.
- El selector de "Respuesta" se puebla con las opciones de la pregunta padre y guarda el
  `id` de opción.
- UX para no técnicos: un solo bloque, lenguaje "Mostrar … solo si … es igual a …".

**Inscripción (consumo):** evaluación reactiva.

- `inscripcion.vue`: mantener el array posicional, pero envolver cada pregunta en
  `v-if="esVisible(pregunta)"`. **No reordenar `respuestas`** (conserva índices); al
  enviar, filtrar las ocultas. Al ocultarse una pregunta, **limpiar su respuesta**.
- `suscribe.vue`: idem con el objeto indexado por id (más limpio).
- Al cambiar la respuesta del padre, recomputar visibilidad y limpiar respuestas de las
  que quedaron ocultas (incluida cascada).

### 3.7 Estrategia backend

`guardarRespuestasInscripcion()` (Actividades) y `SuscribeController@create` (Campañas)
pasan a:

1. Cargar preguntas + condiciones de la actividad/campaña.
2. Calcular preguntas visibles con `ConditionEvaluator` sobre las respuestas recibidas.
3. **Descartar** respuestas de preguntas ocultas (no se persisten).
4. **Validar requeridas solo de las visibles**; las ocultas nunca son obligatorias.

> Recomendación sobre respuestas ocultas: **descartarlas antes de guardar** (no
> persistir). Es más limpio para exports y estadísticas y evita datos contradictorios
> ("respondió algo que no debía ver"). Si se prefiere trazabilidad, la alternativa es
> guardarlas pero marcarlas; para v1 se recomienda descartarlas.

> Sobre validación de requeridas: como hoy el servidor no valida nada, conviene introducir
> la validación server-side **acotada a preguntas visibles** y comunicarlo. Riesgo bajo
> (el cliente ya la exigía), pero es un cambio de comportamiento que hay que documentar.

---

## 4. Plan de implementación (paso a paso)

**Fase 0 — Preparación de datos (robustez de opciones)**

1. Migración: agregar accessor de normalización en `ActividadPregunta` y
   `CampaignPregunta` que devuelva `opciones` siempre como `[{id,label}]`.
2. Migración de datos idempotente: asignar `id` estable a las opciones existentes.
3. Adaptar `preguntas-actividad.vue`, `CampanaPreguntas.vue`, `inscripcion.vue`,
   `suscribe.vue` para leer `opcion.label` y conocer `opcion.id`. (Sin tocar `respuesta`.)
4. Verificar inscripción/suscripción end-to-end sin condiciones todavía (no-regresión).

**Fase 1 — Modelo de condiciones**

5. Migración `pregunta_condiciones` + `morphMap` en `AppServiceProvider`.
6. Modelo `PreguntaCondicion` + relaciones `condiciones()` en ambos modelos de pregunta.
7. `ConditionEvaluator` (PHP) + tests unitarios (equals, sin condición, cascada, opción
   inexistente).

**Fase 2 — Configuración (backoffice)**

8. Extender validación y persistencia en ambos `*PreguntasController` para aceptar la
   condición (aditivo).
9. Sección "Filtro condicional" en ambos modales Vue (preguntas anteriores + opciones).

**Fase 3 — Inscripción (consumo)**

10. `conditionEvaluator.js` compartido + integración reactiva en `inscripcion.vue` y
    `suscribe.vue` (visibilidad, limpieza de ocultas).
11. Backend: visibilidad + descarte de ocultas + validación de requeridas visibles en
    `InscripcionesController` y `SuscribeController`.

**Fase 4 — Verificación**

12. Tests Feature: inscripción con pregunta dependiente mostrada/oculta; requerida oculta
    no bloquea; respuesta de oculta no se persiste; app mobile (`api`) sigue funcionando.
13. Prueba manual con "Probar inscripción" en backoffice y verificación de exports.

---

## 5. Recomendaciones

1. **Extraer la capa compartida ahora, no después.** Es la última oportunidad barata de
   dejar de duplicar; si esta feature se vuelve a copiar/pegar entre módulos, la deuda se
   duplica. El `ConditionEvaluator` y el modelo polimórfico sirven a ambos sin fusionar
   las tablas de preguntas (refactor grande e innecesario).
2. **Referenciar opciones por id estable, no por texto.** Es el único punto realmente
   difícil de revertir más adelante; conviene hacerlo bien desde v1.
3. **Una sola definición de "visible", espejada en PHP y JS.** Evita que cliente y
   servidor discrepen sobre qué se muestra/valida.
4. **Cerrar el hueco de validación server-side** aprovechando este cambio, acotado a
   visibles, para no romper inscripciones actuales.
5. **Evitar ciclos por construcción**: el selector de pregunta padre solo ofrece
   preguntas de menor `orden`. Igualmente, defender en `ConditionEvaluator` contra
   referencias colgadas (pregunta padre borrada → tratar dependiente como visible o
   limpiar la condición al borrar el padre).
6. **Tabla polimórfica > columna JSON** si se va a perseguir el roadmap (grupos,
   secciones, skip logic). Si el roadmap se descarta y solo importa v1, la columna JSON es
   válida y más barata.
7. **Mantener `respuesta` como texto.** No migrar el almacenamiento de respuestas;
   reduce el blast radius y conserva compatibilidad con exports e históricos.
8. **Documentar el contrato de API** (condición incluida en el JSON de pregunta) en
   `docs/api.md` para el frontend nuevo y la app mobile.
