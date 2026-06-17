# ¿Conviene un refactor previo antes de las preguntas condicionales?

> Companion de `preguntas-condicionales-analisis.md`. Evalúa si vale la pena una inversión
> previa para reducir la duplicación Actividades/Campañas antes de construir la feature.
> No propone una reescritura: solo refactors chicos/medianos de alto beneficio y bajo riesgo.

---

## TL;DR

La buena noticia: **implementar las preguntas condicionales "una sola vez" no requiere
refactorizar el código duplicado existente.** El núcleo nuevo (evaluador de condiciones +
tabla polimórfica de condiciones + identidad estable de opciones) ya se diseña como capa
compartida; ambos módulos la consumen sin tocar sus tablas. Eso resuelve el "no duplicar
la feature" por sí solo.

La duplicación vieja (modales, controladores, modelos) es un problema **separable**. La
inversión previa que sí recomiendo es pequeña, sin cambios visibles y sin migrar datos:
**unificar el modal Vue de configuración**, **un trait/base controller para el CRUD** y
**un trait de modelo para preguntas/opciones**. Con eso, la UI del "Filtro condicional" y
la persistencia de la condición se escriben una vez. Lo que **no** haría es fusionar las
tablas/modelos en un esquema polimórfico único: ahí el riesgo (datos, app mobile, exports)
supera al beneficio.

---

## 1. Dónde está la duplicación real (ordenada por dolor)

| # | Qué | Grado de duplicación | Diferencias reales | Riesgo de unificar |
|---|-----|----------------------|--------------------|--------------------|
| 1 | Modales de config Vue (`preguntas-actividad.vue` vs `CampanaPreguntas.vue`) | ~99% (≈290 líneas casi calcadas) | URL base del endpoint, id del modal DOM, nombre de prop (`actividadId`/`campanaId`) | **Muy bajo** (front puro, sin datos, sin cambio de comportamiento) |
| 2 | Controladores CRUD (`ActividadPreguntasController` vs `CampaignPreguntasController`) | ~95% | Modelo usado y clave del padre (`idActividad`/`id`) | **Bajo** (sin datos, sin rutas nuevas; cubrible con tests) |
| 3 | Modelos de pregunta (`ActividadPregunta` vs `CampaignPregunta`) | ~90% | `$table` y relación al padre | **Bajo** |
| 4 | Render + guardado en inscripción (`inscripcion.vue` paso preguntas + `guardarRespuestasInscripcion` vs `suscribe.vue` + `SuscribeController@create`) | Parecido, **no idéntico** | Array **posicional** vs objeto **indexado por id**; con/sin FK; flujo multi-paso vs form simple | **Medio** (toca flujos vivos + app mobile) |
| 5 | Tablas/modelos de respuestas (`inscripcion_respuestas` vs `suscribe_respuestas`) | ~90% | FK formal solo en `suscribe_respuestas`; nombre de FK del padre | Medio (migración de datos si se fusiona) |

La duplicación más cara y más fácil de matar es la **#1 y #2**. La #4/#5 es la más
delicada y la de menor relación beneficio/riesgo para tocar ahora.

## 2. Refactors de mayor beneficio / menor riesgo

Son tres, todos sin cambio visible y sin migración de datos:

- **Unificar el modal de configuración** en un solo componente parametrizado
  (`PreguntasManager.vue` con props `baseUrl` y `entidad`). El "Filtro condicional" se
  agrega una sola vez.
- **Trait/base controller para el CRUD de preguntas** (`ManagesPreguntas` o
  `AbstractPreguntasController`) con `store/update/destroy/index` y la validación. Cada
  controlador concreto solo declara su modelo y la clave del padre. La persistencia de la
  condición se escribe una vez.
- **Trait de modelo `Preguntable`/`HasOpciones`** con `fillable`, `casts` y el **accessor
  de normalización de opciones** (la identidad estable `{id,label}` del análisis vive
  acá, escrita una vez).

## 3. Cambios sin comportamiento visible para el usuario

Los tres de arriba son refactors internos: misma UI, mismas rutas, mismas respuestas JSON.
La unificación del modal produce exactamente el mismo HTML/UX. El trait de controlador
mantiene idénticas las rutas y los payloads. El trait de modelo no cambia el contrato de
la API (el accessor devuelve opciones normalizadas, compatible con clientes viejos).

## 4. Cambios sin romper datos existentes

Todo lo anterior es **aditivo a nivel datos**: no se renombran ni fusionan tablas, no se
migran respuestas. La única migración de datos es la asignación idempotente de `id` a las
opciones existentes (Fase 0 del otro doc), que conserva el `label` y no toca la columna
`respuesta`. Fusionar tablas (#5) **sí** rompería compatibilidad y por eso queda fuera.

## 5. Estrategia para implementar la feature una sola vez

Clave: **separar "feature nueva" de "deuda vieja".**

- La lógica nueva (¿se cumple la condición? → `ConditionEvaluator`; el modelo
  `PreguntaCondicion` polimórfico; el accessor de opciones) **ya es compartida por
  diseño**. Ambos módulos la consumen igual. Eso da el "implementar una vez" sin tocar lo
  duplicado.
- Los refactors #1–#3 hacen que los **puntos de enganche** (modal de config, CRUD,
  modelo) también sean únicos, así no hay que pegar el enganche dos veces.
- El único punto que queda inevitablemente en dos lugares es la **integración en el flujo
  de inscripción vs suscripción** (son flujos distintos: multi-paso Vue vs form simple).
  Ahí se comparte el *cálculo* (mismo evaluador JS + helper backend de
  visibilidad/limpieza/validación) pero la *integración* se hace en cada flujo. Es
  aceptable y de bajo riesgo no unificar esos dos flujos ahora.

---

## Opciones intermedias, ordenadas

| Opción | Qué | Esfuerzo | Riesgo | Beneficio a 5 años |
|--------|-----|----------|--------|--------------------|
| 0. Solo capa nueva compartida | Evaluador + tabla condiciones + accessor opciones; no tocar lo viejo | — (va igual) | Nulo | Alto: feature una vez |
| 1. Unificar modal Vue | Un `PreguntasManager.vue` parametrizado | S | Muy bajo | Alto |
| 2. Trait/base controller CRUD | Lógica de negocio compartida, controladores finos | S–M | Bajo | Alto |
| 3. Trait de modelo `Preguntable` | fillable/casts + accessor de opciones | S | Bajo | Medio-alto |
| 4. Helper compartido de respuestas | Visibilidad + purga de ocultas + validación de requeridas visibles, llamado por ambos flujos | M | Medio | Medio-alto |
| 5. Unificar tablas/modelos (esquema polimórfico único de formularios) | Fusionar preguntas y respuestas en un solo esquema | L | **Alto** | Alto en teoría, desproporcionado |

Mapeo a tus alternativas:
- *Tablas separadas + servicios compartidos* → **sí** (Opción 0 + 4): es el corazón de la
  propuesta.
- *Modelos separados + traits* → **sí** (Opción 3).
- *Controladores separados + lógica compartida* → **sí** (Opción 2).
- *Unificar componentes Vue* → **sí** (Opción 1).
- *Capa de formularios reutilizable* → **parcial ahora, completa más adelante**: el
  evaluador + helpers son esa capa en germen; la abstracción "FormDefinition" completa es
  Opción 5, que no recomiendo todavía.

## Qué elegiría (5 años, equipo chico)

**Opciones 0 + 1 + 2 + 3, y un 4 "lite". Nunca la 5 (por ahora).**

Razón: 0–3 son todas de bajo riesgo, sin cambio visible y sin migrar datos, y colapsan la
peor duplicación (config/CRUD/modelo) de modo que esta feature **y toda feature futura de
formularios** se escriben en un solo lugar. El 4 "lite" comparte el cálculo de
visibilidad/validación entre ambos flujos sin unificar los flujos en sí. La 5 (esquema
único) es la única con relación riesgo/beneficio mala para un equipo chico: una migración
de datos grande y un cambio de contrato de API mobile para ganar elegancia que los traits
y servicios ya capturan al 80%.

## Antes / Durante / Después

**Antes de las preguntas condicionales** (inversión previa, chica y segura):
- Opción 1: unificar el modal de configuración en `PreguntasManager.vue`.
- Opción 3: trait `Preguntable` + accessor de opciones + migración idempotente de ids
  (Fase 0 del otro doc).
- Opción 2: trait/base controller del CRUD.

Todo esto es no-regresión pura: se mergea con la suite de tests verde y sin cambios de UX.

**Al mismo tiempo que la implementación:**
- Opción 0: `ConditionEvaluator` (PHP + espejo JS), modelo y tabla `pregunta_condiciones`.
- Añadir el "Filtro condicional" al modal ya unificado (se escribe una vez).
- Añadir la persistencia de la condición en el base controller (se escribe una vez).
- Opción 4-lite: un helper compartido (visibilidad + purga de ocultas + validación de
  requeridas visibles) invocado desde `InscripcionesController` y `SuscribeController`.

**Para más adelante (opcional):**
- Opción 4 completa: un `RespuestaService` que unifique persistencia de respuestas.
- Converger las formas de datos del lado inscripción (array posicional → indexado por id)
  para igualar ambos flujos.
- Una abstracción `FormDefinition` reutilizable más allá de preguntas.

**Probablemente nunca:**
- Opción 5: fusionar las tablas de preguntas/respuestas en un esquema polimórfico único.
  El costo (datos + app mobile + exports) no se justifica frente a lo que ya resuelven los
  traits y servicios.

---

## Conclusión

Sí, hay una inversión previa razonable: **unificar el modal Vue, el CRUD y el modelo de
preguntas (Opciones 1–3)**. Son chicas, invisibles para el usuario y no migran datos, y
hacen que las preguntas condicionales —y el resto del roadmap de formularios— se
implementen en un solo lugar. La lógica condicional en sí ya se comparte por diseño. La
fusión de tablas es el único refactor "grande" y es justamente el que conviene no hacer.
