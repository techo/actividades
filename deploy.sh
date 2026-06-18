#!/bin/bash
#
# Deploy a sandbox.
#
# Uso:
#   ./deploy.sh                      # deploya la branch que ya esté checkouteada en el server
#   ./deploy.sh preguntas-condicionales   # fuerza checkout + pull de esa branch
#
set -euo pipefail

HOST="techo@actividades.techo.org"
DIR="/var/www/html/sandbox-voluntariado-eventual"
BRANCH="${1:-}"

echo "🚀 Deploying to $HOST (${BRANCH:-branch actual del server})..."

ssh "$HOST" bash -s <<EOF
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

echo "✅ Deploy complete."
