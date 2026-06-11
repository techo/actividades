#!/bin/bash

set -e

HOST="techo@actividades.techo.org"
DIR="/var/www/html/sandbox-voluntariado-eventual"

echo "🚀 Deploying to $HOST..."

ssh "$HOST" bash -s << EOF
  set -e
  cd $DIR

  echo "→ Putting site in maintenance mode..."
  php artisan down

  echo "→ Pulling latest code..."
  git pull

  echo "→ Generating i18n files..."
  php artisan vue-i18n:generate

  echo "→ Building assets..."
  npm run dev

  echo "→ Clearing caches..."
  php artisan cache:clear
  php artisan route:clear
  php artisan config:clear
  php artisan view:clear

  echo "→ Bringing site back up..."
  php artisan up

  echo "✅ Deploy complete."
EOF