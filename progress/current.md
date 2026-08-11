# Sesión actual

> Cuando empieces una sesión: completá esta plantilla.
> Cuando la termines: mové el contenido a `history.md` y dejá solo la plantilla.

---

## Estado

- **Tarea en progreso:** Task 31 — Backup/restore (`etapa1-estabilizacion`, risk: medium). Código listo; falta merge a la feature.
- **Inicio:** 2026-08-11
- **Agente / desarrollador:** Claude (Opus 4.8) — protocolo Líder/Implementador/Revisor.
- **Base de esta branch:** `claude/task-31-backup-restore` desde el tip de `feature/indicadores-plan-vs-real` (`5220265b`, ya incluye task-32 y task-35).
- **Orden de la sesión:** 32 ✅ → 34 ✅(obsoleta) → 35 ✅ → **31 (cerrando)** → **AVISO cierre Etapa 1** (salvo branch protection, del dueño) → 40 (plan a aprobar antes de implementar). 45/46/47 salteadas.

## Task 31 — Backup/restore (alcance: entregable repo + demo local, elegido por el dueño)

- `scripts/backup-db.sh`: mysqldump --single-transaction + gzip, password por --defaults-extra-file, valida el dump, retención (RETENTION_DAYS default 14).
- `deploy.sh`: backup ANTES de `migrate --force`; aborta el deploy si falla.
- `docs/backup-restore.md`: doc + cron (a instalar en prod = acción del dueño) + restore probado.
- Restore **ejecutado de verdad** contra laravel_test (98 tablas + canario): backup → DROP DATABASE → restore → 98 tablas y canario 3/3. ✅

## Progreso

- [x] `scripts/backup-db.sh` + `storage/backups/` gitignored.
- [x] `deploy.sh` hace backup antes de migrar (aborta si falla).
- [x] `docs/backup-restore.md` con procedimiento + transcript del restore probado.
- [x] Restore end-to-end ejecutado y verificado (tablas + datos).
- [x] Task 31 marcada `done` en tasks.json.
- [ ] Suite completa verde (corriendo).
- [ ] Commit + merge a `feature/indicadores-plan-vs-real`.

## Contexto relevante (entorno de test)

- Suite en el worktree: `docker exec -e APP_ENV=testing laravel_app bash -c "cd /var/www/html/.claude/worktrees/etapa-1-tareas-techo-f0f123 && php -d memory_limit=512M vendor/bin/phpunit"`. ~3.7 min.
- `mix-manifest.json` debe incluir `/css/app.css` o toda vista Blade da 500.
- Ruido inofensivo en stderr del worktree: `fatal: not a git repository ...` (no afecta resultados).
- Merge target `feature/indicadores-plan-vs-real` está checked-out en el checkout principal; el merge se hace ahí cuando su working tree esté limpio.

## Bloqueos

Ninguno.
