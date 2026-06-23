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

## A. Movilización (base: `reporting_fact_participacion` + `dim_actividad`)

| # | Métrica | Cálculo propuesto | Fuente | Estado |
|---|---|---|---|---|
| A1 | Movilizados en construcciones | `SUM(es_presente)` donde la actividad es construcción unifamiliar **con vida de escuela** | `fact_participacion` + `Actividad.vida_escuela` + tipo | 🟡 |
| A2 | Movilizados a territorio | `SUM(es_presente)` en actividades de territorio **que NO** sean construcción unifamiliar con vida de escuela | `fact_participacion` + `tipo_indicador` + `vida_escuela` | 🟡 |
| A3 | Movilizados en Colecta | `SUM(es_presente)` en actividades de colecta | `fact_participacion` + (`tipo_indicador='colecta'` o campaña tipo colecta) | 🟡 |
| A4 | Movilizados en otras actividades | `SUM(es_presente)` en lo no cubierto por A1–A3 (formación, escuela, encuentros…) | `fact_participacion` (complemento) | 🟡 |
| A5 | Movilizados en actividades (TOTAL) | `SUM(es_presente)` total = A1+A2+A3+A4 | `fact_participacion` | ✅ |

**Preguntas A**:
- ¿"Construcciones" (A1) = `vida_escuela=1`? ¿y se restringe a algún `tipo_indicador`
  (p. ej. `construccion_de_viviendas`) o basta `vida_escuela`?
- "Territorio" (A2): ¿qué `tipo_indicador` cuentan? ¿`territorio` menos los de A1?
- "Colecta" (A3): ¿se identifica por `tipo_indicador='colecta'`, por la actividad
  ligada a una `campaign` tipo `colecta`, o ambos?
- Hoy **muchos `Tipo` no tienen `tipo_indicador` cargado** → estos números saldrán
  incompletos hasta etiquetarlos (calidad de dato).

---

## B. Equipo permanente (base: `reporting_fact_membresia`)

| # | Métrica | Cálculo propuesto | Fuente | Estado |
|---|---|---|---|---|
| B1 | Equipo permanente (TOTAL) | `COUNT(DISTINCT person_key)` con `vigente=1` (comunidad + oficina/área) | `fact_membresia` | 🟡 |
| B2 | Permanentes en equipos de comunidad | `COUNT(DISTINCT person_key)` vigentes en equipos **de comunidad** | `fact_membresia` + vínculo equipo↔comunidad | 🟡 |
| B3 | Permanentes en áreas | `COUNT(DISTINCT person_key)` vigentes en equipos que **no** son de comunidad, por `area` | `fact_membresia` + `area` | 🟡 |
| B4 | Voluntarios en coordinaciones de comunidad | `COUNT(DISTINCT idPersona)` activos en `coordinadores_comunidad` | `coordinadores_comunidad` | 🟡 |

**Preguntas B**:
- ¿Cómo se distingue un **equipo de comunidad** de uno de área/oficina? Opciones:
  el equipo está ligado a una comunidad (`equipo_comunidad` / `Integrante.idComunidad`),
  o el `area` indica comunidad. Hay que fijar la regla (define B1/B2/B3).
- B4: ¿"activo" en `coordinadores_comunidad` cómo se determina? (la tabla no tiene
  flag de vigencia visible). ¿Cuenta también `coordinadores_equipos`/`Coordinadores`?

---

## C. Campañas y encuentros

| # | Métrica | Cálculo propuesto | Fuente | Estado |
|---|---|---|---|---|
| C1 | Campañas nacionales de captación ejecutadas | `COUNT(campaigns)` con `tipo='captacion'`, alcance nacional, ejecutadas en el período | `campaigns` (`tipo`, `pais_id`, `oficina_id`, `estado`/`activa`, fechas) | 🟡 |
| C2 | Encuentros locales/nacionales realizados | `COUNT(Actividad)` de tipo "encuentro", separados por alcance local/nacional | `Actividad` (`tipo_indicador='encuentros'`) + `alcance` | 🔴 |

**Preguntas C**:
- C1: ¿"nacional" = `campaigns.oficina_id` nulo (a nivel país)? ¿"ejecutada" =
  `estado`/`activa` o fecha dentro del período (`fecha_inicio`/`fecha_fin`)?
- C2: el campo `Actividad.alcance` existe pero **no tiene datos** (sin backfill).
  Hasta poblarlo no se puede separar local/nacional. ¿Cómo se determina el alcance
  de una actividad? (¿manual, o se infiere de oficina vs país?)

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

## E. Soluciones / impacto (base: `actividad_informe_cierre`)

`soluciones_entregadas` es un **select único** por informe. Cada métrica filtra por
su clave y **suma una cantidad** (a definir cuál). Período por la fecha de la
actividad asociada.

| # | Métrica | Clave `soluciones_entregadas` | Estado |
|---|---|---|---|
| E1 | Viviendas de emergencia construidas | `vivienda_emergencia` | 🟡 |
| E2 | Viviendas transitorias/progresivas construidas | `vivienda_transitoria` | 🟡 |
| E3 | Viviendas permanentes/sociales construidas | `vivienda_social` | 🟡 |
| E4 | Familias con solución de agua | `solucion_agua_familiar` | 🟡 |
| E5 | Familias con solución de saneamiento | `solucion_saneamiento_familiar` | 🟡 |
| E6 | Familias con solución de energía eléctrica | `solucion_energia_familiar` | 🟡 |
| E7 | Proyectos de infraestructura comunitaria | `solucion_infraestructura_comunitaria` | 🟡 |
| E8 | Soluciones comunitarias de agua | `solucion_agua_comunitaria` | 🟡 |
| E9 | Soluciones comunitarias de saneamiento | `solucion_saneamiento_comunitario` | 🟡 |
| E10 | Soluciones comunitarias de energía/iluminación | `solucion_energia_comunitaria` | 🟡 |
| E11 | Sedes comunitarias construidas | **(no hay clave)** | 🔴 |

**Preguntas E** (una sola decisión resuelve casi todas):
- **¿Qué cantidad se suma por informe?** Las opciones del modelo son:
  `numero_beneficiados`, o la suma de `cant_soluciones_voluntariado +
  _corporativos + _secundarios + _universitarios + _familias`, o `COUNT` de informes.
  Probable: las de "familias que cuentan con…" (E4–E6) suman **familias**
  (`numero_beneficiados`/`cant_soluciones_familias`), y las de "construidas/
  soluciones" (E1–E3, E7–E10) suman **soluciones** (sumatoria de `cant_soluciones_*`).
  Hay que confirmarlo.
- E11 (sedes): no existe la clave en el desplegable. ¿Se agrega una opción
  `sede_comunitaria`, o se deriva de `infraestructura_comunitaria`?
- ¿Filtramos informes por algún estado (validado/completo) o cuentan todos?

---

## Resumen de bloqueos transversales
1. **`tipo_indicador` incompleto** en `Tipo` (afecta A, C2) → etiquetar tipos.
2. **`Actividad.alcance` sin backfill** (afecta C2) → definir y poblar.
3. **`Comunidad` sin fechas de inicio/fin de trabajo ni "tipo mesa"** (afecta D3,
   D5, D6, D7) → posible cambio de modelo.
4. **Regla equipo de comunidad vs área** (afecta B1–B3) → definir.
5. **Qué cantidad se suma en impacto** (afecta todo E) → una decisión.
