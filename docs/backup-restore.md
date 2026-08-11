# Backup y restore de la base de datos

> Estrategia de backup de la BD MySQL de `actividades` y procedimiento de restore
> probado de punta a punta. Cubre la task 31 (Etapa 1).

## Resumen

- **Script:** [`scripts/backup-db.sh`](../scripts/backup-db.sh) — `mysqldump` consistente + gzip + retención.
- **En deploy:** `deploy.sh` corre el backup **antes** de `php artisan migrate --force`; si el backup falla, el deploy aborta y no se migra.
- **Automatizado:** vía cron en el server (ver abajo — es una acción de ops a instalar en prod).
- **Restore:** documentado y **probado end-to-end** (ver §Restore probado).

## El script `scripts/backup-db.sh`

Qué hace:

1. Lee credenciales del `.env` de la app (o de variables de entorno, que tienen prioridad).
2. `mysqldump --single-transaction --quick --routines --triggers --events` (consistente, sin lockear InnoDB, con esquema completo), comprimido con `gzip`.
3. La contraseña se pasa por un archivo temporal `--defaults-extra-file` (chmod 600), **nunca** en la línea de comandos (no aparece en `ps`).
4. **Valida** el dump (`gzip -t` y tamaño > 0). Si quedó corrupto o vacío, borra el archivo y sale con error.
5. **Retención:** borra los dumps de esa base más viejos que `RETENTION_DAYS` (default 14). Esto ataca el problema conocido de que el disco de prod se llenaba con dumps viejos.
6. Devuelve exit ≠ 0 ante cualquier falla (para que `deploy.sh` aborte antes de migrar). El path del backup va a stdout.

Configuración (variables de entorno, todas opcionales):

| Variable | Default | Descripción |
|---|---|---|
| `DB_HOST` `DB_PORT` `DB_DATABASE` `DB_USERNAME` `DB_PASSWORD` | del `.env` | Conexión a la BD |
| `BACKUP_DIR` | `<repo>/storage/backups` | Dónde se guardan los dumps (gitignored) |
| `RETENTION_DAYS` | `14` | Antigüedad máxima antes de purgar |

Uso manual:

```bash
./scripts/backup-db.sh
# o con overrides:
BACKUP_DIR=/var/backups/actividades RETENTION_DAYS=30 ./scripts/backup-db.sh
```

## Automatización (cron) — acción de ops en el server

El script queda en el repo; **falta instalar el cron en el server de producción** (no se
puede hacer desde el repo). En el host, como el usuario que corre la app:

```cron
# Backup diario de la BD a las 03:00, retención 30 días, log a storage/logs
0 3 * * * cd /var/www/html/sandbox-voluntariado-eventual && BACKUP_DIR=/var/backups/actividades RETENTION_DAYS=30 ./scripts/backup-db.sh >> storage/logs/backup.log 2>&1
```

Recomendación adicional (fuera de este cambio): replicar los dumps a almacenamiento
**offsite** (S3/rsync a otro host) para sobrevivir a la pérdida del disco del server.

## Restore

Para restaurar un dump generado por este script:

```bash
# 1. (Opcional pero recomendado) backup del estado actual antes de restaurar.
./scripts/backup-db.sh

# 2. Restaurar sobre la base destino (reemplaza su contenido).
gunzip -c storage/backups/laravel-YYYYMMDD-HHMMSS.sql.gz | \
  mysql -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE"
```

Si hace falta recrear la base desde cero antes del restore:

```bash
mysql -h "$DB_HOST" -u "$DB_USERNAME" -p -e "DROP DATABASE IF EXISTS $DB_DATABASE; CREATE DATABASE $DB_DATABASE CHARACTER SET utf8mb4;"
gunzip -c <dump>.sql.gz | mysql -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE"
```

## Restore probado (end-to-end)

Ejecutado el **2026-08-11** contra la MySQL local de Docker (`laravel_mysql`, MySQL 5.7.44),
sobre la base `laravel_test` (98 tablas), con una tabla canario `br_demo` (3 filas):

```
1) Estado inicial: 98 tablas en laravel_test (incluye canario br_demo con 3 filas)
2) Backup con scripts/backup-db.sh → laravel_test-20260811-152047.sql.gz (21 KB, gzip válido)
3) DESASTRE: DROP DATABASE laravel_test  → 0 tablas
4) RESTORE: gunzip -c <dump> | mysql laravel_test
5) Verificación: 98 tablas (== antes) y canario br_demo con 3 filas
   ✅ RESTORE END-TO-END OK
```

Se verificó tanto el **conteo de tablas** (98 → 0 → 98) como la **integridad de datos**
(el canario volvió con sus 3 filas exactas). El dump del script es restaurable de cero.

## Integración con `deploy.sh`

`deploy.sh` ejecuta, dentro del bloque remoto y **antes** de las migraciones:

```bash
echo "→ Backup de BD ANTES de migrar (aborta el deploy si falla)..."
bash scripts/backup-db.sh

echo "→ Running database migrations..."
php artisan migrate --force
```

Como el bloque corre con `set -euo pipefail`, si el backup falla el deploy se corta
**antes** de tocar la BD (y el `trap ... EXIT` deja el sitio arriba). Así nunca se corre
`migrate --force` sobre una base sin backup previo.
