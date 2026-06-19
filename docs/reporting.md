# Capa de Reporting / Analytics

> Cómo está expuesta la información del sistema para Power BI y otros consumidores
> externos, qué significa cada objeto, y cómo conectarse sin romper consistencia
> ni privacidad.

---

## Para qué existe

Históricamente Power BI se conectaba directo a las tablas operativas (`Actividad`,
`Inscripcion`, `Persona`, …) y **reconstruía las métricas por su cuenta**, con
joins y reglas propias. Resultado: los números no coincidían con el sistema
(relaciones mal armadas, filtros olvidados, soft-deletes ignorados, definiciones
distintas de "movilizado").

La capa de reporting resuelve eso con un principio único:

> **La definición de cada métrica vive UNA sola vez, en una vista `reporting_*`.
> El backoffice y Power BI leen de la misma vista. Nadie redefine reglas en DAX.**

Así, si "movilizado" o el NPS cambian, se cambian en un solo lugar y todos los
consumidores quedan consistentes automáticamente.

---

## Cómo conectarse

- **Motor**: MySQL (la misma base del sistema). Las vistas tienen prefijo
  `reporting_` (MySQL no tiene *schemas* separados como Postgres).
- **Power BI**: conector *MySQL database* → seleccionar **solo objetos
  `reporting_*`**. Usar modo *Import* (o DirectQuery si se necesita frescura).
- **Regla de oro para cualquier consumidor (Power BI, otra BI, scripts, APIs)**:
  **conectarse únicamente a las vistas `reporting_*`. Nunca a las tablas
  operativas** (`Actividad`, `Inscripcion`, `Persona`, …). Las tablas crudas
  cambian de estructura, no aplican las reglas de negocio y contienen PII.
- **Agregación sí, definición no**: el consumidor puede sumar, contar y agrupar
  sobre columnas ya calculadas (`es_presente`, `es_kpi`, `es_promotor`, `etapa`,
  …), pero **no** debe re-derivar reglas (qué es movilizado, los cortes de NPS,
  etc.). Eso ya está resuelto en la vista.
- **Fechas**: no hay `reporting_dim_tiempo`; Power BI genera su propia tabla de
  fechas (time intelligence nativo) sobre las columnas `fecha_*` / `anio` / `mes`.

> ⚠️ **Seguridad (pendiente — Fase 4)**: hoy las vistas **no** tienen Row Level
> Security ni `person_key` anonimizada: una conexión ve todos los países y las
> dimensiones de persona todavía traen `idPersona`. Hasta implementar RLS +
> surrogate key, restringir el acceso a nivel de credencial/usuario de BD y no
> publicar datasets a nivel registro fuera de la organización. Ver
> "Privacidad y seguridad" más abajo.

---

## Catálogo de objetos

Convención: `reporting_fact_*` = hechos (eventos contables), `reporting_dim_*` =
dimensiones (atributos para filtrar/agrupar).

### `reporting_fact_participacion`
**Grano**: 1 fila por inscripción (haya asistido o no). Backbone del tablero
*Voluntariado Movilizado*.

| Columna | Descripción |
|---|---|
| `idInscripcion` | PK de la inscripción |
| `idActividad`, `idPersona` | claves de cruce |
| `idPais`, `idOficina` | de la actividad |
| `tipo_indicador` | categoría canónica de la actividad (de `Tipo`) |
| `fecha_actividad`, `anio`, `mes` | por `Actividad.fechaInicio` |
| `es_presente` | 1 si asistió (`Inscripcion.presente=1`) |
| `es_movilizado` | = `es_presente` (movilizado = presencia) |
| `es_kpi` | 1 si presente **y** `tipo_indicador` ∈ (`territorio`, `construccion_de_viviendas`) |

Métricas: movilizados = `SUM(es_presente)`; personas únicas =
`COUNT(DISTINCT idPersona)` con `es_presente=1`; movilizados KPI =
`SUM(es_kpi)`; inscriptos = `COUNT(*)`; asistencia = movilizados / inscriptos.

### `reporting_fact_membresia`
**Grano**: 1 fila por integrante de equipo. Backbone del tablero *Equipo
Permanente*.

| Columna | Descripción |
|---|---|
| `idIntegrante`, `idPersona`, `idEquipo` | claves |
| `idOficina`, `idPais`, `area_id` | del equipo |
| `idComunidad`, `rol` | atributos de la membresía |
| `fechaInicio`, `fechaFin` | vigencia |
| `vigente` | 1 si `fechaFin` es NULL o futura |

Equipo permanente = `SUM(vigente)`; personas únicas = `COUNT(DISTINCT idPersona)`
con `vigente=1`.

### `reporting_fact_evaluacion_actividad`
**Grano**: 1 fila por evaluación de actividad.

| Columna | Descripción |
|---|---|
| `idEvaluacion`, `idActividad`, `idPersona` | claves |
| `idPais`, `idOficina`, `tipo_indicador`, `fecha_actividad`, `anio` | contexto |
| `puntaje` | nota de la actividad |
| `tags_positivos`, `tags_negativos` | **JSON** (Power BI los desanida; MySQL 5.7 no tiene `JSON_TABLE`) |
| `tiene_comentario` | 1 si hay comentario |

Puntaje promedio = `AVG(puntaje)`; encuestas = `COUNT(*)`.

### `reporting_fact_evaluacion_impacto`
**Grano**: 1 fila por evaluación de impacto. Alimenta NPS e *Impacto personal*.

| Columna | Descripción |
|---|---|
| `idEvaluacionImpacto`, `idActividad`, `idPersona`, `idPais`, `idOficina`, `fecha_actividad`, `anio` | claves/contexto |
| `impacto_habilidades_capacidades`, `impacto_percepcion_realidad`, `impacto_recomendaria_experiencia` | scores 1-10 |
| `es_promotor` | 1 si recomienda ≥ 9 |
| `es_detractor` | 1 si recomienda ≤ 6 |
| `nps_categoria` | `promotor` / `pasivo` (7-8) / `detractor` |

**NPS** = `(SUM(es_promotor) - SUM(es_detractor)) / COUNT(*) * 100` (lo arma Power BI).

### `reporting_fact_lifecycle`
**Grano**: 1 fila por persona, con su etapa **actual** en el ciclo de
voluntariado. Alimenta los embudos.

| Columna | Descripción |
|---|---|
| `idPersona`, `idPais` | claves |
| `es_integrante_vigente`, `fue_integrante`, `tiene_presencia`, `es_suscripto` | flags base |
| `etapa` | `transformacion` > `cierre` > `insercion` > `captado` > `sin_etapa` |

Definición de etapa (en orden de prioridad):
- **transformación**: es integrante de equipo vigente.
- **cierre**: fue integrante pero ninguna membresía vigente.
- **inserción**: tiene ≥ 1 presencia y no es integrante.
- **captado**: está suscripto (campaña) sin presencia ni membresía.

### Dimensiones
- **`reporting_dim_actividad`**: `idActividad`, `nombreActividad`, `idTipo`,
  `tipo_indicador`, `idCategoria`, `categoria`, `alcance`, `idPais`, `idOficina`,
  `fechaInicio`, `fechaFin`, `anio`, `mes`, `vida_escuela`.
- **`reporting_dim_equipo`**: `idEquipo`, `nombre`, `idOficina`, `idPais`,
  `idEquipoPadre`, `area_id`, `area`, `activo`, `fechaInicio`, `fechaFin`.
- **`reporting_dim_persona`** (sin PII): `idPersona`, `genero`, `rango_edad`
  (`18 a 21` / `22 a 25` / `26 o más`), `canal_contacto`, `idPais`,
  `idProvincia`, `idLocalidad`. Alimenta las donas de demografía.
- **`reporting_dim_geografia`**: `idLocalidad`, `localidad`, `idProvincia`,
  `provincia`, `idOficina`, `oficina`, `idPais`, `pais`.

### `reporting_snapshot_lifecycle` (tabla, no vista)
Histórico mensual de etapas para "Var. vs Año Ant." y embudos en el tiempo.
Columnas: `snapshot_date`, `idPais`, `etapa`, `cantidad`. La llena el comando
`php artisan reporting:snapshot-lifecycle` (programado el día 1 de cada mes en
`App\Console\Kernel`). Re-ejecutarlo el mismo día reemplaza el snapshot del día.

---

## Glosario canónico (las definiciones, en un solo lugar)

| Término | Definición |
|---|---|
| **Movilizado** | participación con `presente=1`. Cuenta presencias, no personas. |
| **Personas únicas** | `COUNT(DISTINCT idPersona)` movilizadas. |
| **Movilizados KPI** | movilizados con `tipo_indicador` ∈ (`territorio`, `construccion_de_viviendas`). |
| **Período** | por fecha de la actividad (`Actividad.fechaInicio`). |
| **Equipo permanente** | membresía (`Integrantes`) con `fechaFin` NULL o futura. |
| **Antigüedad (movilizado)** | desde la primera inscripción. |
| **Antigüedad (permanente)** | desde la primera membresía (`Integrante.fechaInicio` mínima). |
| **NPS** | promotor ≥ 9, detractor ≤ 6, pasivo 7-8. |
| **Impacto "alto"** | score ≥ 8. |

Todas las vistas excluyen registros *soft-deleted* (de `Inscripcion`, `Actividad`,
etc.) — algo que las consultas directas solían olvidar.

---

## Privacidad y seguridad

- `reporting_dim_persona` **no** expone PII: ni nombre, ni mail, ni DNI, ni
  teléfono, ni fecha de nacimiento (solo `rango_edad` bucketizado). Las métricas
  demográficas se calculan sin datos personales.
- **Nunca** exponer las tablas operativas a un consumidor de analytics: contienen
  PII y ficha médica.
- **Pendiente (Fase 4)**: `person_key` surrogate (para que Power BI no vea
  `idPersona` real) y **Row Level Security** por país/oficina derivado del
  usuario autenticado. Hasta entonces, una conexión a estas vistas ve todos los
  países; restringir por credencial de BD y no compartir datasets a nivel
  registro fuera de la organización.

---

## Cómo extender

- **Nueva métrica sobre datos existentes**: en general **no requiere backend**.
  Se calcula en Power BI agregando sobre las columnas/flags de las vistas, o se
  agrega una columna calculada a la vista correspondiente.
- **Nuevo hecho o dimensión**: agregar una vista `reporting_*` en una migración
  (ver `database/migrations/2026_06_18_000004..0007`). Codificar la regla en la
  vista, no en el consumidor.
- **Métrica que la app también muestra**: además de la vista, crear/usar un
  servicio en `app/Reporting/` que **lea de la vista** (ej.
  `App\Reporting\MovilizacionMetrics`), para que app y Power BI compartan la
  misma fuente.

---

## Mapa de archivos

| Qué | Dónde |
|---|---|
| Vistas de participación | `database/migrations/2026_06_18_000004_create_reporting_participacion_views.php` |
| Vistas de membresía / equipo / persona | `..._000005_create_reporting_membresia_persona_views.php` |
| Vistas de evaluación / geografía | `..._000006_create_reporting_evaluacion_geografia_views.php` |
| Ciclo de voluntariado + snapshot | `..._000007_create_reporting_lifecycle.php` |
| Comando de snapshot | `app/Console/Commands/SnapshotLifecycle.php` |
| Servicios de métricas (consumo desde la app) | `app/Reporting/` |
