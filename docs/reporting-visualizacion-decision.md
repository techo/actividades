# Decisión: cómo visualizar el reporting (Power BI vs. sistema propio)

> Documento de decisión para discutir con el equipo. Estado: **abierto / a decidir.**
> Contexto: ya existe la capa de reporting (API REST sobre vistas `reporting_*`,
> ver [reporting.md](reporting.md)). La pregunta es qué herramienta consume esa API
> para mostrar los tableros (las 4 pantallas de DATASPOT).

---

## La pregunta
¿Conviene re-apuntar el Power BI existente (DATASPOT) a la nueva API, o construir un
sistema de dashboards propio que consuma la API y replique esas pantallas?

## Viabilidad de un sistema propio
**Es viable.** La API quedó diseñada para esto: modelo estrella (hechos +
dimensiones), filtro por país, paginación y `person_key`. Cualquier consumidor
externo puede levantar las 4 pantallas (KPIs, donas, barras, embudos, mapa).
Ventaja: durante el análisis ya se mapeó **cada visual → su dataset y su cálculo**
(ver [reporting-backlog.md](reporting-backlog.md)), así que la especificación
funcional ya existe.

## Premisa a corregir
Surgió la idea de que "hay que rehacer el Power BI desde cero". Importante:

> **Re-apuntar el Power BI existente a la API NO es rehacerlo desde cero.** Lo que
> estaba mal y había que rehacer era la *lógica* (métricas, joins, definición de
> "movilizado") — eso ya está resuelto en las vistas/API. Re-conectar DATASPOT a la
> API y rearmar los visuales es trabajo de BI normal, **menor** que construir una
> app de dashboards propia.

Y al revés: **construir un sistema propio es MÁS trabajo, no un atajo.** Implica
programar y mantener para siempre cada gráfico, filtro, métrica nueva, RLS,
exportación y compartir — todo lo que un BI ya da hecho.

## Build vs. buy

| Criterio | Power BI apuntado a la API | Sistema propio de dashboards |
|---|---|---|
| Esfuerzo inicial | Bajo-medio (re-conectar + rearmar visuales) | **Alto** (es una app entera) |
| Mantenimiento | Lo hacen analistas (no-devs) | **Desarrollo para siempre** por cada cambio |
| Self-service (nuevos reportes sin programar) | ✅ | ❌ (hay que codear cada uno) |
| Costo | Licencias Power BI | Sin licencia, pero costo de desarrollo continuo |
| Embeber en el sistema TECHO (mismo login) | Limitado | ✅ |
| Compartir / tableros públicos | Vía Power BI publish | ✅ a medida |
| Control de UX / branding | Limitado | Total |

## Recomendación
La capa difícil y valiosa (la API = **fuente única de verdad**) ya está hecha. Sobre eso:

1. **Para análisis self-service → seguir con Power BI** apuntado a la API. Es lo más
   sostenible: los analistas arman y cambian reportes sin pasar por desarrollo.
   Rearmar DATASPOT sobre la API es la mejor relación esfuerzo/valor.
2. **Construir un dashboard propio solo si hay un driver concreto** (no como atajo
   para evitar el rearmado):
   - embeberlo en el sistema TECHO (coordinadores ven sus tableros adentro, mismo login);
   - el **costo de licencias** de Power BI es un bloqueo;
   - se necesitan tableros **públicos/embebibles** sin publicar en Power BI;
   - se quiere un set **fijo y acotado** de pantallas operativas (no BI exploratorio).
3. **La API sirve a los dos a la vez.** Se puede tener Power BI para exploración +
   un dashboard propio liviano para unas pocas vistas operativas embebidas. No es
   uno u otro.

**En una línea:** no rehacer todo DATASPOT en una app propia como atajo — sería el
camino más caro. Sí tiene sentido una app propia si el objetivo es *embeber /
independizarse de Power BI*, como decisión deliberada.

## Plan si se decide el sistema propio
(Proyecto nuevo, separado. La spec ya existe, lo que lo hace abordable.)

- **Fase 0 — Spec**: inventario de las 4 pantallas; cada visual ya tiene dataset/cálculo mapeado.
- **Fase 1 — Setup**: SPA nueva (sugerido **Vue 3 + Vite + lib de charts** tipo ECharts/Chart.js, o React). Cliente HTTP a la API con token Passport.
- **Fase 2 — Capa de datos**: cliente que pagina los datasets (equivalente JS de la función M de Power BI), con **parámetro de país** y caché/refresh por intervalo (para "información estática"/snapshot en vez de live).
- **Fase 3 — Componentes de visual**: KPI cards, donas, barras, embudos, mapa — reutilizables.
- **Fase 4 — Pantallas**: ensamblar por pestaña (Equipo Permanente, Movilizado, Evaluación, Histórico) + filtro de país global.
- **Fase 5 — Auth/scope y deploy**: login contra la API; cuando se active el scope server-side por token, el filtrado por país pasa a ser obligatorio solo.

Estimación honesta: Fases 1-4 son un proyecto de varias sesiones, no un rato. Por
eso la recomendación #1 (Power BI) se mantiene salvo que el embedding o el costo lo
justifiquen.

## Filtrado por país (aplica a cualquiera de las dos opciones)
La API ya filtra por país (`?idPais=<id>` en todo dataset con columna `idPais`).
Para operación individual por país, lo recomendado es parametrizar el país en el
consumidor (Power BI o app propia) y pasarlo a cada request. Ver
[reporting.md](reporting.md#filtrar-por-país-operación-individual-por-país).
