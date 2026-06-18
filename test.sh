#!/usr/bin/env bash
#
# test.sh — Corre la suite PHPUnit dentro de Docker, en un solo comando.
#
#   ./test.sh                      # toda la suite
#   ./test.sh --testdox            # con nombres legibles
#   ./test.sh --filter fusionar    # un test puntual
#   ./test.sh tests/Feature/PaisYLocaleTest.php
#
# Hace todo el setup que la suite necesita y que de otro modo habría que recordar:
#   - crea la base de datos de test (laravel_test) si no existe
#   - resuelve la ruta dentro del contenedor (funciona desde el repo o un worktree)
#   - si falta vendor (caso típico de un worktree nuevo), lo copia del repo
#     principal y regenera el autoloader (el contenedor no tiene red)
#
# La config de test (APP_ENV, conexión MySQL, memory_limit) vive en phpunit.xml,
# así que no hace falta pasar flags ni exportar variables.
#
# Variables opcionales: APP_CONTAINER, DB_CONTAINER, TEST_DB.
set -euo pipefail

APP_CONTAINER="${APP_CONTAINER:-laravel_app}"
DB_CONTAINER="${DB_CONTAINER:-laravel_mysql}"
TEST_DB="${TEST_DB:-laravel_test}"

# Ruta del repo montada en /var/www/html dentro del contenedor.
MOUNT_SRC="$(docker inspect "$APP_CONTAINER" \
  --format '{{ range .Mounts }}{{ if eq .Destination "/var/www/html" }}{{ .Source }}{{ end }}{{ end }}')"
if [ -z "$MOUNT_SRC" ]; then
  echo "✗ No encontré el mount /var/www/html en el contenedor $APP_CONTAINER. ¿Está levantado? (docker compose up -d)" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REL="${SCRIPT_DIR#"$MOUNT_SRC"}"          # '' si es el repo principal; '/.claude/worktrees/...' si es worktree
CONTAINER_PATH="/var/www/html${REL}"

# 1. Base de datos de test (idempotente).
docker exec "$DB_CONTAINER" sh -c \
  'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS '"$TEST_DB"' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON '"$TEST_DB"'.* TO \"laravel\"@\"%\"; FLUSH PRIVILEGES;"' \
  >/dev/null 2>&1 || true

# 2. vendor del worktree (si falta): copia real desde el repo principal + dump-autoload.
#    Un symlink NO sirve: el autoloader resolvería las clases contra el repo principal.
if [ -n "$REL" ]; then
  if ! docker exec "$APP_CONTAINER" test -f "${CONTAINER_PATH}/vendor/bin/phpunit"; then
    echo "── Preparando worktree (vendor, autoloader, assets) ──"
    docker exec "$APP_CONTAINER" bash -c "
      set -e
      cd '$CONTAINER_PATH'
      rm -rf vendor
      cp -a /var/www/html/vendor ./vendor
      composer dump-autoload --quiet
      cp -n /var/www/html/public/mix-manifest.json public/ 2>/dev/null || true
      cp -n /var/www/html/storage/oauth-*.key storage/ 2>/dev/null || true
    "
  fi
fi

# 3. Correr la suite.
docker exec "$APP_CONTAINER" bash -c "cd '$CONTAINER_PATH' && vendor/bin/phpunit $*"
