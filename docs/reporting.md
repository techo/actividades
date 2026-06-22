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
> Los consumidores leen esas definiciones a través de la API. Nadie redefine
> reglas en DAX ni toca la base directamente.**

Así, si "movilizado" o el NPS cambian, se cambian en un solo lugar y todos los
consumidores quedan consistentes automáticamente.

---

## Cómo conectarse — SOLO vía API

**El único punto de acceso para consumidores externos (Power BI, otras apps,
integraciones, scripts) es la API REST de reporting. No se ofrece conexión MySQL
directa a la base.**

- **No** se entregan credenciales de base de datos a ningún consumidor de
  analytics. La frontera es la API: se obtiene un token y se consume por HTTP.
- Internamente, la aplicación sí lee las vistas `reporting_*` (la API y los
  servicios de `app/Reporting/` corren dentro de la app). Eso es uso interno; lo
  que queda fuera es el acceso SQL externo.
- **Agregación sí, definición no**: el consumidor puede sumar, contar y agrupar
  sobre las columnas/flags que devuelve la API (`es_presente`, `es_kpi`,
  `es_promotor`, `etapa`, …), pero **no** debe re-derivar reglas de negocio. Eso
  ya está resuelto en la vista que hay detrás del endpoint.
- **Fechas**: Power BI genera su propia tabla de fechas (time intelligence) sobre
  las columnas `fecha_*` / `anio` / `mes` que devuelve la API.
- **Personas**: la API expone `person_key` (UUID pseudónimo), nunca el
  `idPersona` real. Ver "Privacidad y seguridad".

### Endpoints

Implementación: `app/Http/Controllers/api/reporting/ReportingController.php`.
Auth: Passport (`auth:api`) — header `Authorization: Bearer <token>`.

| Método | Ruta | Qué devuelve |
|---|---|---|
| GET | `/api/reporting/catalog` | Datasets disponibles, sus columnas y los filtros soportados (autodescriptivo). |
| GET | `/api/reporting/datasets/{name}` | Filas paginadas de un dataset, con filtros opcionales. |
| GET | `/api/reporting/metrics/movilizacion` | KPIs (`movilizados_total`, `movilizados_kpi`, `personas_unicas`). |

`{name}` ∈ `fact_participacion`, `fact_membresia`, `fact_evaluacion_actividad`,
`fact_evaluacion_impacto`, `fact_lifecycle`, `dim_actividad`, `dim_equipo`,
`dim_persona`, `dim_geografia`, `dim_pais`, `dim_oficina`, `dim_indicador`,
`snapshot_lifecycle`.

**Convención id ↔ nombre (modelo estrella)**: los hechos (`fact_*`) llevan los
**ids** (`idPais`, `idOficina`, `idCategoria`, `tipo_indicador`); los **nombres**
viven en las dimensiones (`dim_pais`, `dim_oficina`, `dim_actividad`,
`dim_indicador`, …). El consumidor relaciona hecho→dimensión y muestra el nombre.
No se denormalizan nombres dentro de los hechos (evita duplicar la etiqueta y
permite localizar/renombrar en un solo lugar).

**Filtros opcionales** (se aplican solo si la columna existe en el dataset):
`anio`, `mes`, `idPais`, `idOficina`, `tipo_indicador`, `etapa`, `es_presente`,
`es_kpi`, `vigente`. **Paginación**: `per_page` (default 1000, máx 5000) y `page`;
la respuesta es el paginador de Laravel (`data`, `current_page`, `total`,
`next_page_url`, …).

Ejemplos:
```
GET /api/reporting/catalog
GET /api/reporting/datasets/fact_participacion?anio=2026&es_presente=1&per_page=2000
GET /api/reporting/metrics/movilizacion?anio=2026
```

### Conectar Power BI

La API pagina (`current_page`, `last_page`, `data[]`), así que se usa una función
**Power Query M** que recorre las páginas y concatena. Se escribe una vez y se
reutiliza para cualquier dataset. A esta escala (miles/decenas de miles de filas)
el refresh por API es viable.

Pasos:
1. Generar un token Passport y guardarlo en un **parámetro** de Power BI llamado
   `TokenAPI`.
2. *Power Query → Nueva consulta → Consulta en blanco → Editor avanzado*, pegar la
   función y nombrarla `fnReportingDataset`.
3. Invocarla por cada tabla, p. ej. `fnReportingDataset("fact_participacion", [anio = "2026"])`,
   `fnReportingDataset("dim_pais")`, `fnReportingDataset("dim_indicador")`.
4. Crear las relaciones (`fact[idPais]` ↔ `dim_pais[idPais]`,
   `fact[tipo_indicador]` ↔ `dim_indicador[tipo_indicador]`, …) y usar en los
   visuales los **nombres** de las dimensiones.

```m
// fnReportingDataset: trae un dataset completo de /api/reporting paginando.
(dataset as text, optional filtros as record) as table =>
let
    BaseUrl = "https://actividades.techo.org/api/reporting/datasets/",
    Token   = TokenAPI,  // parámetro de Power BI con el bearer token
    Headers = [ Authorization = "Bearer " & Token, Accept = "application/json" ],
    Filtros = if filtros = null then [] else filtros,

    TraerPagina = (pagina as number) as record =>
        let
            q    = Record.Combine({ Filtros, [ page = Text.From(pagina), per_page = "2000" ] }),
            json = Json.Document(Web.Contents(BaseUrl & dataset, [ Headers = Headers, Query = q ]))
        in
            json,

    Primera   = TraerPagina(1),
    UltimaPag = Primera[last_page],
    Paginas   = List.Transform({1..UltimaPag}, each TraerPagina(_)[data]),
    Filas     = List.Combine(Paginas),
    Tabla     = Table.FromRecords(Filas)
in
    Tabla
```

> Ejemplo ilustrativo: los nombres `last_page` / `data` son los del paginador de
> Laravel (lo que devuelve la API). Ajustar el dominio al real.

---

## Catálogo de objetos (lo que hay detrás de la API)

Cada `dataset` de la API se respalda en una vista `reporting_*`. Convención:
`fact_*` = hechos (eventos contables), `dim_*` = dimensiones.

### `fact_participacion`
**Grano**: 1 fila por inscripción (haya asistido o no). Tablero *Voluntariado
Movilizado*.

| Columna | Descripción |
|---|---|
| `idInscripcion` | PK de la inscripción |
| `idActividad`, `person_key` | claves de cruce (persona pseudonimizada) |
| `idPais`, `idOficina` | de la actividad |
| `tipo_indicador` | categoría canónica de la actividad (de `Tipo`) |
| `fecha_actividad`, `anio`, `mes` | por `Actividad.fechaInicio` |
| `es_presente` | 1 si asistió (`Inscripcion.presente=1`) |
| `es_movilizado` | = `es_presente` (movilizado = presencia) |
| `es_kpi` | 1 si presente **y** `tipo_indicador` ∈ (`territorio`, `construccion_de_viviendas`) |

Métricas: movilizados = `SUM(es_presente)`; personas únicas =
`COUNT(DISTINCT person_key)` con `es_presente=1`; movilizados KPI =
`SUM(es_kpi)`; inscriptos = `COUNT(*)`; asistencia = movilizados / inscriptos.

### `fact_membresia`
**Grano**: 1 fila por integrante de equipo. Tablero *Equipo Permanente*.

| Columna | Descripción |
|---|---|
| `idIntegrante`, `person_key`, `idEquipo` | claves |
| `idOficina`, `idPais`, `area_id` | del equipo |
| `idComunidad`, `rol` | atributos de la membresía |
| `fechaInicio`, `fechaFin` | vigencia |
| `vigente` | 1 si `fechaFin` es NULL o futura |

Equipo permanente = `SUM(vigente)`; personas únicas = `COUNT(DISTINCT person_key)`
con `vigente=1`.

### `fact_evaluacion_actividad`
**Grano**: 1 fila por evaluación de actividad.

| Columna | Descripción |
|---|---|
| `idEvaluacion`, `idActividad`, `person_key` | claves |
| `idPais`, `idOficina`, `tipo_indicador`, `fecha_actividad`, `anio` | contexto |
| `puntaje` | nota de la actividad |
| `tags_positivos`, `tags_negativos` | **JSON** (se desanidan en el consumidor; MySQL 5.7 no tiene `JSON_TABLE`) |
| `tiene_comentario` | 1 si hay comentario |

Puntaje promedio = `AVG(puntaje)`; encuestas = `COUNT(*)`.

### `fact_evaluacion_impacto`
**Grano**: 1 fila por evaluación de impacto. Alimenta NPS e *Impacto personal*.

| Columna | Descripción |
|---|---|
| `idEvaluacionImpacto`, `idActividad`, `person_key`, `idPais`, `idOficina`, `fecha_actividad`, `anio` | claves/contexto |
| `impacto_habilidades_capacidades`, `impacto_percepcion_realidad`, `impacto_recomendaria_experiencia` | scores 1-10 |
| `es_promotor` | 1 si recomienda ≥ 9 |
| `es_detractor` | 1 si recomienda ≤ 6 |
| `nps_categoria` | `promotor` / `pasivo` (7-8) / `detractor` |

**NPS** = `(SUM(es_promotor) - SUM(es_detractor)) / COUNT(*) * 100`.

### `fact_lifecycle`
**Grano**: 1 fila por persona, con su etapa **actual** en el ciclo de
voluntariado. Alimenta los embudos.

| Columna | Descripción |
|---|---|
| `person_key`, `idPais` | claves |
| `es_integrante_vigente`, `fue_integrante`, `tiene_presencia`, `es_suscripto` | flags base |
| `etapa` | `transformacion` > `cierre` > `insercion` > `captado` > `sin_etapa` |

Definición de etapa (en orden de prioridad):
- **transformación**: es integrante de equipo vigente.
- **cierre**: fue integrante pero ninguna membresía vigente.
- **inserción**: tiene ≥ 1 presencia y no es integrante.
- **captado**: está suscripto (campaña) sin presencia ni membresía.

### Dimensiones
- **`dim_actividad`**: `idActividad`, `nombreActividad`, `idTipo`, `tipo`
  (nombre del tipo), `tipo_indicador`, `idCategoria`, `categoria` (nombre),
  `alcance`, `idPais`, `idOficina`, `fechaInicio`, `fechaFin`, `anio`, `mes`,
  `vida_escuela`.
- **`dim_equipo`**: `idEquipo`, `nombre`, `idOficina`, `idPais`,
  `idEquipoPadre`, `area_id`, `area`, `activo`, `fechaInicio`, `fechaFin`.
- **`dim_persona`** (sin PII): `person_key`, `genero`, `rango_edad`
  (`18 a 21` / `22 a 25` / `26 o más`), `canal_contacto`, `idPais`,
  `idProvincia`, `idLocalidad`.
- **`dim_geografia`**: `idLocalidad`, `localidad`, `idProvincia`, `provincia`,
  `idOficina`, `oficina`, `idPais`, `pais`.
- **`dim_pais`**: `idPais`, `pais` (nombre), `iso2`, `abreviacion`.
- **`dim_oficina`**: `idOficina`, `oficina` (nombre), `idPais`.
- **`dim_indicador`**: `tipo_indicador` (código), `indicador` (etiqueta es).
  Mapea los códigos de `tipo_indicador` a su nombre para mostrar. Las etiquetas
  viven en `lang/*/backend.php`; esta vista las refleja para BI (es).

### `snapshot_lifecycle`
Histórico mensual de etapas para "Var. vs Año Ant." y embudos en el tiempo.
Columnas: `snapshot_date`, `idPais`, `etapa`, `cantidad`. La llena el comando
`php artisan reporting:snapshot-lifecycle` (programado el día 1 de cada mes en
`App\Console\Kernel`).

### Objetos internos (NO se exponen por la API)
- **`reporting_person`** (tabla): mapeo `idPersona` → `person_key` (UUID). Tabla de
  re-identificación; sin ella no se puede volver a la persona. La mantiene el
  comando `reporting:sync-person-keys` (diario).
- **`reporting_acceso_usuario`** (vista): mapeo usuario → país permitido. Insumo
  para el scope server-side de la API (ver "Privacidad y seguridad").

---

## Glosario canónico (las definiciones, en un solo lugar)

| Término | Definición |
|---|---|
| **Movilizado** | participación con `presente=1`. Cuenta presencias, no personas. |
| **Personas únicas** | `COUNT(DISTINCT person_key)` movilizadas. |
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

### Sin acceso a la base
Los consumidores no reciben credenciales de BD; el único acceso es la API, detrás
de `auth:api` (Passport). Esto da frontera (no se expone el esquema ni PII),
auditabilidad (cada request queda logueado) y revocabilidad (tokens).

### Pseudonimización (person_key)
- La API expone `person_key` (UUID), nunca el `idPersona` real. El mapeo vive en
  `reporting_person`, tabla **interna** que no se expone.
- `dim_persona` **no** expone PII: ni nombre, ni mail, ni DNI, ni teléfono, ni
  fecha de nacimiento (solo `rango_edad` bucketizado).

### Scope por país (server-side, en la API)
El filtrado por país se hace —cuando se active— **en la API, derivándolo del
token** del consumidor (el `idPaisPermitido` del usuario autenticado), no en el
cliente. La vista `reporting_acceso_usuario` es el insumo de ese mapeo
(`email` → `idPais`, con `es_global` cuando `idPaisPermitido` es NULL/0).

> Estado actual: **el scope está desactivado** por decisión de negocio — la API
> trae todos los países. `idPais` / `idOficina` quedan como filtros opcionales.
> Activarlo es un cambio aditivo (filtrar por el país del token) que **no cambia
> el contrato** de la API.

> Limitación: el scope es a nivel **país** (no oficina), porque el modelo de
> usuario hoy solo tiene `idPaisPermitido`. Un scope por oficina o un rol
> regional requeriría extender ese modelo.

---

## Cómo extender

- **Nueva métrica sobre datos existentes**: agregar una columna calculada a la
  vista correspondiente, o un endpoint/parametro en la API. No re-derivar reglas
  en el consumidor.
- **Nuevo hecho o dimensión**: agregar una vista `reporting_*` en una migración
  (ver `database/migrations/2026_06_18_000004..0009`) y sumar su alias a la
  whitelist del `ReportingController`. Si la vista expone persona, usar
  `person_key` (join a `reporting_person`).
- **Métrica que la app también muestra**: usar un servicio en `app/Reporting/`
  que lea de la vista (ej. `App\Reporting\MovilizacionMetrics`), para que app y
  API compartan la misma fuente.

---

## Mapa de archivos

| Qué | Dónde |
|---|---|
| API de reporting (único acceso externo) | `app/Http/Controllers/api/reporting/ReportingController.php`, rutas en `routes/api.php` |
| Vistas de participación | `database/migrations/2026_06_18_000004_create_reporting_participacion_views.php` |
| Vistas de membresía / equipo / persona | `..._000005_create_reporting_membresia_persona_views.php` |
| Vistas de evaluación / geografía | `..._000006_create_reporting_evaluacion_geografia_views.php` |
| Ciclo de voluntariado + snapshot | `..._000007_create_reporting_lifecycle.php` |
| person_key (pseudonimización) | `..._000008_create_reporting_person_key.php` |
| Mapeo de acceso (insumo de scope) | `..._000009_create_reporting_acceso_usuario_view.php` |
| Comandos (snapshot, sync de person_key) | `app/Console/Commands/SnapshotLifecycle.php`, `SyncPersonKeys.php` |
| Servicios de métricas (consumo interno) | `app/Reporting/` |
