#!/bin/bash
#
# Backup de la base de datos MySQL de actividades.
#
# - Hace un mysqldump consistente (--single-transaction) comprimido con gzip.
# - Verifica que el dump sea válido (gzip -t) antes de darlo por bueno.
# - Aplica retención: borra los backups más viejos que RETENTION_DAYS.
# - Devuelve exit != 0 si algo falla, para que deploy.sh aborte ANTES de migrar.
#
# Credenciales: por defecto se leen del .env de la app (ubicado junto al repo).
# Se pueden pisar por variables de entorno (útil para el cron o para tests):
#   DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
#   BACKUP_DIR (default: <repo>/storage/backups)
#   RETENTION_DAYS (default: 14)
#
# Uso:
#   ./scripts/backup-db.sh
#   BACKUP_DIR=/var/backups/actividades RETENTION_DAYS=30 ./scripts/backup-db.sh
#
set -euo pipefail

# Raíz del repo = carpeta padre de este script.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${ENV_FILE:-$APP_DIR/.env}"

# Lee una variable del .env (respeta comillas simples/dobles). Vacío si no está.
env_get() {
  [ -f "$ENV_FILE" ] || return 0
  grep -E "^$1=" "$ENV_FILE" | head -1 | cut -d= -f2- \
    | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'\$/\1/"
}

# Env var explícita > valor del .env > default.
DB_HOST="${DB_HOST:-$(env_get DB_HOST)}";         DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-$(env_get DB_PORT)}";         DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-$(env_get DB_DATABASE)}"
DB_USERNAME="${DB_USERNAME:-$(env_get DB_USERNAME)}"
DB_PASSWORD="${DB_PASSWORD:-$(env_get DB_PASSWORD)}"

BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/storage/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
  echo "❌ backup-db: faltan credenciales de BD (DB_DATABASE/DB_USERNAME). Revisá $ENV_FILE." >&2
  exit 1
fi

command -v mysqldump >/dev/null 2>&1 || { echo "❌ backup-db: mysqldump no está instalado." >&2; exit 1; }

mkdir -p "$BACKUP_DIR"

STAMP="$(date +%Y%m%d-%H%M%S)"
OUTFILE="$BACKUP_DIR/${DB_DATABASE}-${STAMP}.sql.gz"

# Password vía archivo temporal (chmod 600) para que NO aparezca en la lista de procesos.
CNF="$(mktemp)"
chmod 600 "$CNF"
cleanup() { rm -f "$CNF"; }
trap cleanup EXIT
cat > "$CNF" <<CONF
[client]
host=$DB_HOST
port=$DB_PORT
user=$DB_USERNAME
password=$DB_PASSWORD
CONF

echo "→ backup-db: volcando '$DB_DATABASE' a $OUTFILE ..."
# --single-transaction: consistente sin lockear (InnoDB). --routines/--triggers/--events: esquema completo.
# --no-tablespaces: evita el volcado de tablespaces, que en MySQL 5.7.31+/8.0
#   requiere el privilegio PROCESS. El usuario de BD del sandbox no lo tiene y sin
#   este flag mysqldump aborta con "Access denied; you need PROCESS privilege(s)".
if ! mysqldump --defaults-extra-file="$CNF" \
      --single-transaction --quick --no-tablespaces --routines --triggers --events \
      --default-character-set=utf8mb4 \
      "$DB_DATABASE" | gzip -c > "$OUTFILE"; then
  echo "❌ backup-db: mysqldump falló." >&2
  rm -f "$OUTFILE"
  exit 1
fi

# El dump debe ser un gzip válido y no vacío.
if ! gzip -t "$OUTFILE" 2>/dev/null || [ ! -s "$OUTFILE" ]; then
  echo "❌ backup-db: el dump quedó vacío o corrupto ($OUTFILE)." >&2
  rm -f "$OUTFILE"
  exit 1
fi

SIZE="$(du -h "$OUTFILE" | cut -f1)"
echo "✅ backup-db: OK ($SIZE) → $OUTFILE"

# Retención: borrar dumps de esta base más viejos que RETENTION_DAYS.
if [ "$RETENTION_DAYS" -gt 0 ] 2>/dev/null; then
  DELETED="$(find "$BACKUP_DIR" -maxdepth 1 -type f -name "${DB_DATABASE}-*.sql.gz" -mtime +"$RETENTION_DAYS" -print -delete | wc -l | tr -d ' ')"
  [ "$DELETED" -gt 0 ] && echo "🧹 backup-db: retención — borrados $DELETED backup(s) > ${RETENTION_DAYS} días."
fi

# Path del backup a stdout (para scripting).
echo "$OUTFILE"
