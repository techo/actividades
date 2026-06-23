# Backlog de métricas de reporting (a definir antes de implementar)

> Lista de trabajo de las métricas solicitadas para exponer por la API de reporting.
> Para cada una: cálculo propuesto, fuente de datos y estado. **Nada acá está
> implementado todavía** — primero acordamos definiciones y resolvemos los 🟡/🔴.

**Estado**: ✅ regla clara, listo para implementar · 🟡 falta una decisión de
negocio · 🔴 falta un dato/estructura en el sistema (o backfill).

Convenciones ya acordadas (ver `reporting.md`): movilizado = `presente=1`,
período por `Actividad.fechaInicio`, persona = `person_key`, equipo permanente =
membresía vigente (`fechaFin` null/futura).

---

## A. Movilización (base: `reporting_fact_participacion`) — ✅ DEFINIDA

Corte por `tipo_indicador` **puro** (decisión confirmada: sin refinar por
`vida_escuela`). Partición exacta: A1+A2+A3+A4 = A5. Cada métrica =
`SUM(es_presente)` de `reporting_fact_participacion` en el período, filtrando por
el bucket.

| # | Métrica | Bucket (`tipo_indicador`) | Estado |
|---|---|---|---|
| A1 | Movilizados en construcciones | `construccion_de_viviendas` | ✅ |
| A2 | Movilizados a territorio | `territorio` | ✅ |
| A3 | Movilizados en Colecta | `colecta` | ✅ |
| A4 | Movilizados en otras actividades | el resto: `captacion`, `encuentros`, `gestion_y_acompañamiento`, `insercion`, `renovacion`, `otras_actividades`, y los `NULL`/sin indicador | ✅ |
| A5 | Movilizados en actividades (TOTAL) | todos los buckets = `SUM(es_presente)` | ✅ |

> Calidad de dato: los tipos **sin `tipo_indicador` cargado** caen en "Otras" (A4)
> hasta que se etiqueten desde el backoffice. No afecta la definición, sí los
> números hasta completar el etiquetado.

---

## B. Equipo permanente (base: `reporting_fact_membresia`) — ✅ DEFINIDA

Comunidad vs área por **`idComunidad` de la membresía**. Vigente = `fechaFin`
null/futura (ya en la vista). Se exponen **dos conteos**: membresías vigentes y
personas únicas (`COUNT(DISTINCT person_key)`).

| # | Métrica | Cálculo | Estado |
|---|---|---|---|
| B1 | Equipo permanente (TOTAL) | vigentes (todas) — membresías y DISTINCT `person_key` | ✅ |
| B2 | Permanentes en equipos de comunidad | vigentes con `idComunidad` **no nulo** | ✅ |
| B3 | Permanentes en áreas | vigentes con `idComunidad` **nulo** (agrupable por `area`) | ✅ |
| B4 | Voluntarios en coordinaciones de comunidad | `COUNT(DISTINCT person_key)` vigentes con `rol='coordinacion'` e `idComunidad` no nulo | ✅ |

> Notas: B1 en personas únicas puede ser < B2+B3 si alguien está en un equipo de
> comunidad y uno de área a la vez (solapamiento a nivel persona); en membresías es
> aditivo. B4 sale del mismo `fact_membresia` (rol + comunidad + vigencia), no de
> `coordinadores_comunidad`. Se podría incluir `subcoordinacion` en B4 si se decide.
> Calidad de dato: `idComunidad` y `rol='coordinacion'` están poco cargados hoy →
> B2/B4 subcontarán hasta completarlos.

---

## C. Campañas y encuentros — ✅ C1 y C2 (total) · 🔴 split local/nacional

| # | Métrica | Cálculo | Estado |
|---|---|---|---|
| C1 | Campañas nacionales de captación ejecutadas | `COUNT(campaigns)` con `tipo='captacion'` y `oficina_id` NULL (nacional) que **solapan** el período (`fecha_inicio <= fin AND fecha_fin >= inicio`) | ✅ |
| C2 | Encuentros realizados (TOTAL) | `COUNT(Actividad)` con `tipo_indicador='encuentros'` en el período (por `fechaInicio`) | ✅ |
| C2b | Encuentros — split local / nacional | igual que C2 separando por `Actividad.alcance` | 🔴 |

> C1: "nacional" = `oficina_id` null (opción "sin oficina" del form); "ejecutada" =
> solapamiento de fechas con el período (no hay estado "ejecutada", solo fechas/`activa`).
> C2b: bloqueado — `Actividad.alcance` está vacío y **no** se puede inferir de la
> oficina (todas las actividades tienen oficina). Pendiente: definir y backfillear `alcance`.

---

## D. Comunidades (base: `Comunidad`, `comunidad_ficha_inicial`)

| # | Métrica | Cálculo propuesto | Fuente | Estado |
|---|---|---|---|---|
| D1 | Comunidades activas | `COUNT(Comunidad)` con `activo=1` | `Comunidad.activo` | ✅ |
| D2 | Comunidades con ficha de asentamiento finalizada | `COUNT` de comunidades con `comunidad_ficha_inicial` (¿completa?) | `comunidad_ficha_inicial` | 🟡 |
| D3 | Comunidades con las que iniciamos trabajo en el período | `COUNT` con fecha de inicio dentro del período | `Comunidad` (¿`created_at`? ¿`fecha_diagnostico`?) | 🔴 |
| D4 | Comunidades con tenencia regularizada | `COUNT` con `estado_legalizacion` = "legal" | `comunidad_ficha_inicial.estado_legalizacion` | 🟡 |
| D5 | Comunidades donde finalizamos trabajo | `COUNT` con fin de trabajo en el período | `Comunidad` (no hay fecha de fin) | 🔴 |
| D6 | Mesas de trabajo activas | `COUNT` de comunidades con "mesa de trabajo" generada hace < 6 meses | ¿`Actividad` tipo mesa? ↔ comunidad | 🔴 |
| D7 | Mesas de trabajo implementadas en el período | `COUNT` de mesas implementadas en el período | mismo que D6 | 🔴 |
| D8 | Vecinas/os participando en mesa de trabajo | `SUM(numero_participantes)` de informes de actividades tipo mesa | `actividad_informe_cierre.numero_participantes` + tipo mesa | 🟡 |

**Preguntas D**:
- D2: no hay flag "finalizada" en la ficha. ¿"finalizada" = existe la fila, o que
  ciertos campos estén completos? ¿Cuáles?
- D3/D5: `Comunidad` **no tiene** fecha de inicio ni de fin de trabajo. ¿Usamos
  `created_at` para inicio? ¿"finalizamos" = pasó a `activo=0` (pero no hay fecha)?
  → probablemente haya que **agregar campos de fecha** (inicio/fin de trabajo).
- D4: `estado_legalizacion` está vacío en dev. ¿Cuáles son los valores y cuál(es)
  cuentan como "regularizada/legal"?
- D6/D7: **no existe "tipo mesa de trabajo" en `Comunidad`**. ¿Una "mesa de trabajo"
  es un `Tipo` de actividad? ¿una comunidad? ¿un equipo? Hay que definir qué es y
  cómo se la marca/fecha antes de contar.
- D8: ¿qué identifica a una actividad como "mesa de trabajo"? (¿`tipo_indicador`?
  ¿`Tipo.nombre`?)

---

## E. Soluciones / impacto (base: `actividad_informe_cierre`) — ✅ DEFINIDA

`soluciones_entregadas` es un **select único** por informe. Cada métrica filtra por
su clave y suma el **TOTAL de soluciones del informe** (confirmado), que es la suma
de las 5 cantidades por grupo (lo que el formulario ya muestra como TOTAL):

```
total_soluciones = cant_soluciones_voluntariado + cant_soluciones_corporativos
                 + cant_soluciones_secundarios + cant_soluciones_universitarios
                 + cant_soluciones_familias
```

Métrica = `SUM(total_soluciones)` de los informes con `soluciones_entregadas = <clave>`
en el período. Las de "familias con…" (E4–E6) usan el TOTAL de su clave `*_familiar`
(cada solución familiar ≈ 1 familia). `numero_participantes` y `numero_beneficiados`
quedan para otras métricas, no para el conteo de soluciones.

| # | Métrica | Clave `soluciones_entregadas` | Estado |
|---|---|---|---|
| E1 | Viviendas de emergencia construidas | `vivienda_emergencia` | ✅ |
| E2 | Viviendas transitorias/progresivas construidas | `vivienda_transitoria` | ✅ |
| E3 | Viviendas permanentes/sociales construidas | `vivienda_social` | ✅ |
| E4 | Familias con solución de agua | `solucion_agua_familiar` | ✅ |
| E5 | Familias con solución de saneamiento | `solucion_saneamiento_familiar` | ✅ |
| E6 | Familias con solución de energía eléctrica | `solucion_energia_familiar` | ✅ |
| E7 | Proyectos de infraestructura comunitaria | `solucion_infraestructura_comunitaria` | ✅ |
| E8 | Soluciones comunitarias de agua | `solucion_agua_comunitaria` | ✅ |
| E9 | Soluciones comunitarias de saneamiento | `solucion_saneamiento_comunitario` | ✅ |
| E10 | Soluciones comunitarias de energía/iluminación | `solucion_energia_comunitaria` | ✅ |
| E11 | Sedes comunitarias construidas | **(no hay clave)** | ⛔ |

**Pendiente E**:
- E11 (sedes): no existe la clave en el desplegable. **Preguntar** si está OK
  agregar la opción `sede_comunitaria`, o si las sedes se reportan dentro de
  `solucion_infraestructura_comunitaria`.

---

## Resumen de bloqueos transversales
1. **`tipo_indicador` incompleto** en `Tipo` (afecta A, C2) → etiquetar tipos.
2. **`Actividad.alcance` sin backfill** (afecta C2) → definir y poblar.
3. **`Comunidad` sin fechas de inicio/fin de trabajo ni "tipo mesa"** (afecta D3,
   D5, D6, D7) → posible cambio de modelo.
4. ✅ **Equipo permanente (B)** — resuelto: comunidad vs área por `idComunidad`,
   dos conteos (membresías y personas únicas), B4 por rol `coordinacion`.
5. ✅ **Impacto/soluciones (E)** — resuelto: se suma el TOTAL (suma de las 5
   `cant_soluciones_*`). Solo queda **preguntar** por agregar la opción
   `sede_comunitaria` al desplegable (E11).
