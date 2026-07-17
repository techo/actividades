# Sesión actual

> Cuando empieces una sesión: completá esta plantilla.
> Cuando la termines: mové el contenido a `history.md` y dejá solo la plantilla.

---

## Estado

- **Tarea en progreso:** Etapa 1 — CI en GitHub Actions (task 29) + reconciliación de tasks.json (task 33)
- **Inicio:** 2026-07-15
- **Agente / desarrollador:** Claude (Fable 5)

## Plan

1. ~~Workflow de GitHub Actions~~ (`.github/workflows/ci.yml`): job PHPUnit (PHP 7.2 + MySQL 5.7 servicio) + job frontend (node 10). Reemplaza `.travis.yml` (eliminado).
2. ~~Reconciliación de tasks.json~~ (task 33): ids 4 y 7 → done (verificados en código), id 2 → pending, nuevas tasks 29-35 (Etapa 1 + TODOs segurizar).
3. Pendiente: push + primer run verde en GitHub + branch protection (task 29 queda `in_progress` hasta eso).

## Progreso

- [x] Workflow ci.yml escrito; pipeline frontend validado completo en node:10 limpio (npm ci → build → tests)
- [x] Tests de Vue arreglados: 3 fallaban con `$t is not a function` (componentes con vue-i18n, specs sin mock — rotos desde que se agregó i18n, nadie los corría). Fix: `mocks: { $t, $tc }` en los mount(). Ahora 10/10.
- [x] tasks.json reconciliado + backlog Etapa 1 agregado (tasks 29-35)
- [x] CLAUDE.md: estadoInscripcion unificado (ya no es deuda), líneas de deuda corregidas, sección Testing con MySQL + CI
- [ ] Push y primer run verde del workflow en GitHub
- [ ] Branch protection exigiendo el check de CI

## Contexto relevante

- El job PHP genera claves de Passport (`passport:keys`) y el `mix-manifest.json` identidad (deploy usa `npm run dev`, sin versionado — verificado en `deploy.sh:47` y `webpack.mix.js`). Sin manifest, los tests web dan 500 (verificado).
- El job frontend necesita `artisan vue-i18n:generate` antes del build: `resources/js/vue-i18n-locales.generated.js` no está trackeado y ambos entry points lo importan.
- Cómo correr la suite local: `docker exec -e APP_ENV=testing laravel_app bash -c "cd /var/www/html && php -d memory_limit=512M vendor/bin/phpunit"`. Si Docker no corre: `colima start` y `docker start laravel_app`. Estado al 2026-07-15: 167/167 en verde.

## Bloqueos

Ninguno.
