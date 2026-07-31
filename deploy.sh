#!/bin/bash
#
# Deploy a sandbox.
#
# Uso:
#   ./deploy.sh                      # deploya la branch que ya esté checkouteada en el server
#   ./deploy.sh preguntas-condicionales   # fuerza checkout + pull de esa branch
#
# Notificaciones n8n (opcional):
#   Exportá N8N_WEBHOOK_URL y N8N_WEBHOOK_TOKEN antes de correr, o dejalos
#   en un archivo .deploy.env (gitignoreado) que se sourcea automáticamente.
#   Si N8N_WEBHOOK_URL no está seteada, el deploy corre igual sin notificar.
#
set -euo pipefail

HOST="techo@actividades.techo.org"
DIR="/var/www/html/sandbox-voluntariado-eventual"
BRANCH="${1:-}"

# --- Config de notificación n8n (secretos NUNCA hardcodeados acá) -----------
# Cargá credenciales desde un archivo local gitignoreado si existe.
if [ -f "$(dirname "$0")/.deploy.env" ]; then
  # shellcheck disable=SC1091
  source "$(dirname "$0")/.deploy.env"
fi
N8N_WEBHOOK_URL="${N8N_WEBHOOK_URL:-}"
N8N_WEBHOOK_TOKEN="${N8N_WEBHOOK_TOKEN:-}"

notify_n8n() {
  local status="$1"       # "success" o "failure"
  local started_at="$2"

  # Sin URL configurada => no notificamos (el deploy no depende de esto).
  [ -z "$N8N_WEBHOOK_URL" ] && return 0

  # Un fallo del curl no debe tumbar el script (el deploy ya terminó).
  curl -fsS -m 10 -X POST "$N8N_WEBHOOK_URL" \
    -H "Content-Type: application/json" \
    -H "X-Deploy-Token: $N8N_WEBHOOK_TOKEN" \
    -d @- <<JSON || echo "⚠️  Falló la notificación a n8n (el deploy siguió normalmente)"
{
  "project": "sandbox-voluntariado-eventual",
  "status": "$status",
  "branch": "${BRANCH:-branch-actual-del-server}",
  "host": "$HOST",
  "started_at": "$started_at",
  "finished_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "user": "$(whoami)"
}
JSON
}

START_TIME="$(date -u +%Y-%m-%dT%H:%M:%SZ)"

echo "🚀 Deploying to $HOST (${BRANCH:-branch actual del server})..."

if ssh "$HOST" bash -s <<EOF
  set -euo pipefail
  cd "$DIR"

  echo "→ Maintenance mode ON..."
  php artisan down || true
  # Pase lo que pase de acá en adelante, el sitio vuelve a estar arriba al salir.
  trap 'php artisan up || true' EXIT

  echo "→ Pulling latest code..."
  git fetch --all --prune
  if [ -n "$BRANCH" ]; then
    git checkout "$BRANCH"
    git pull origin "$BRANCH"
  else
    git pull
  fi

  echo "→ Installing PHP deps..."
  # Sin --no-dev: sandbox necesita phpunit/mockery para poder correr tests acá.
  composer install --no-interaction --prefer-dist --optimize-autoloader

  echo "→ Running database migrations..."
  php artisan migrate --force

  echo "→ Generating i18n files..."
  php artisan vue-i18n:generate

  echo "→ Installing JS deps + building assets..."
  npm install
  npm run dev

  echo "→ Clearing caches..."
  php artisan cache:clear
  php artisan route:clear
  php artisan config:clear
  php artisan view:clear

  echo "✅ Code deployed. (El EXIT trap deja el sitio arriba.)"
EOF
then
  echo "✅ Deploy complete."
  notify_n8n "success" "$START_TIME"
else
  echo "❌ Deploy falló."
  notify_n8n "failure" "$START_TIME"
  exit 1
fi
