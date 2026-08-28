#!/bin/bash
#
# Deploy a sandbox o producción (elegís el ambiente por argumento).
#
# Uso:
#   ./deploy.sh [sandbox|prod] [branch]
#
#   El PRIMER argumento elige el ambiente (sandbox o prod). Si se omite, es sandbox
#   (default seguro). Por compatibilidad, si el 1er argumento no es sandbox/prod se
#   interpreta como branch y el ambiente queda en sandbox.
#     ./deploy.sh                     # sandbox, branch ya checkouteada en el server
#     ./deploy.sh develop             # sandbox, branch develop (compat viejo uso)
#     ./deploy.sh sandbox develop     # sandbox, branch develop
#     ./deploy.sh prod develop        # PRODUCCIÓN, branch develop
#
#   Por qué distingue ambiente (la razón de este script = deploy SEGURO):
#     - prod    -> hace BACKUP de la BD antes de migrar y aborta si el backup falla.
#     - sandbox -> NO hace backup (base descartable; el deploy es más rápido).
#
# Notificaciones n8n (opcional):
#   Exportá N8N_WEBHOOK_URL y N8N_WEBHOOK_TOKEN antes de correr, o dejalos
#   en un archivo .deploy.env (gitignoreado) que se sourcea automáticamente.
#   Si N8N_WEBHOOK_URL no está seteada, el deploy corre igual sin notificar.
#
set -euo pipefail

# --- Selección de ambiente ----------------------------------------------------
# 1er arg = ambiente si es sandbox/prod (lo consumimos con shift). Si no lo es,
# el ambiente queda en sandbox y ese arg se toma como branch (compat uso viejo).
ENVIRONMENT="sandbox"
case "${1:-}" in
  sandbox|prod)  ENVIRONMENT="$1"; shift ;;
  production)    ENVIRONMENT="prod"; shift ;;
esac

HOST="techo@actividades.techo.org"
case "$ENVIRONMENT" in
  prod)    DIR="/var/www/html/voluntariado-eventual" ;;
  sandbox) DIR="/var/www/html/sandbox-voluntariado-eventual" ;;
  *)       echo "Ambiente inválido: $ENVIRONMENT (usá sandbox|prod)"; exit 1 ;;
esac
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
  local commits="$3"      # commits deployados, ya escapados para JSON (una línea, \n entre commits)

  # Sin URL configurada => no notificamos (el deploy no depende de esto).
  [ -z "$N8N_WEBHOOK_URL" ] && return 0

  # Etiqueta de ambiente legible: que la notificación NO diga siempre "producción"
  # cuando en realidad es un deploy a sandbox.
  local env_label
  case "$ENVIRONMENT" in
    prod)    env_label="Producción" ;;
    sandbox) env_label="Sandbox" ;;
    *)       env_label="$ENVIRONMENT" ;;
  esac

  # Estado legible + emoji para el mensaje.
  local status_text status_icon
  if [ "$status" = "success" ]; then
    status_text="finalizó con éxito"; status_icon="✅"
  else
    status_text="falló"; status_icon="❌"
  fi

  # Mensaje listo-para-mostrar: qué ambiente, qué rama y QUÉ commits se subieron.
  # (commits ya viene escapado y con \n entre líneas). n8n puede usar este campo
  # tal cual, sin re-armar el texto ni asumir el ambiente.
  local branch_label="${BRANCH:-branch actual del server}"
  local commits_text="$commits"
  [ -z "$commits_text" ] && commits_text="(sin nuevos commits)"
  local message="$status_icon Deploy a $env_label — voluntariado-eventual (rama $branch_label) $status_text.\\nCommits subidos:\\n$commits_text"

  # Un fallo del curl no debe tumbar el script (el deploy ya terminó).
  curl -fsS -m 10 -X POST "$N8N_WEBHOOK_URL" \
    -H "Content-Type: application/json" \
    -H "X-Deploy-Token: $N8N_WEBHOOK_TOKEN" \
    -d @- <<JSON || echo "⚠️  Falló la notificación a n8n (el deploy siguió normalmente)"
{
  "project": "voluntariado-eventual",
  "environment": "$ENVIRONMENT",
  "environment_label": "$env_label",
  "status": "$status",
  "message": "$message",
  "branch": "${BRANCH:-branch-actual-del-server}",
  "host": "$HOST",
  "commits": "$commits",
  "started_at": "$started_at",
  "finished_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "user": "$(whoami)"
}
JSON
}

START_TIME="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
DEPLOY_LOG="$(mktemp)"

echo "🚀 Deploying [$ENVIRONMENT] a $HOST:$DIR (${BRANCH:-branch actual del server})..."
[ "$ENVIRONMENT" = "prod" ] && echo "⚠️  AMBIENTE DE PRODUCCIÓN"

# El heredoc es <<EOF (sin comillas): las variables SIN escapar ($DIR, $BRANCH) se
# expanden ACÁ (local); las que deben evaluarse en el SERVER van con \$ (\$BEFORE_HEAD).
if ssh "$HOST" bash -s <<EOF | tee "$DEPLOY_LOG"
  set -euo pipefail
  cd "$DIR"

  echo "→ Maintenance mode ON..."
  php artisan down || true
  # Pase lo que pase de acá en adelante, el sitio vuelve a estar arriba al salir.
  trap 'php artisan up || true' EXIT

  echo "→ Pulling latest code..."
  BEFORE_HEAD=\$(git rev-parse HEAD)
  git fetch --all --prune
  if [ -n "$BRANCH" ]; then
    git checkout "$BRANCH"
    git pull origin "$BRANCH"
  else
    git pull
  fi
  AFTER_HEAD=\$(git rev-parse HEAD)
  # Commits que este deploy incorpora (para la notificación). Delimitados con
  # marcadores para poder recortarlos localmente del log.
  echo "===DEPLOYED_COMMITS_START==="
  if [ "\$BEFORE_HEAD" = "\$AFTER_HEAD" ]; then
    echo "(sin nuevos commits; HEAD \$AFTER_HEAD)"
  else
    git log --oneline --no-decorate "\$BEFORE_HEAD..\$AFTER_HEAD"
  fi
  echo "===DEPLOYED_COMMITS_END==="

  echo "→ Installing PHP deps..."
  # Sin --no-dev: sandbox necesita phpunit/mockery para poder correr tests acá.
  composer install --no-interaction --prefer-dist --optimize-autoloader

  # Backup de BD solo en prod: es donde el dato importa. En sandbox (base
  # descartable) se omite a propósito para que el deploy sea más rápido.
  if [ "$ENVIRONMENT" = "prod" ]; then
    echo "→ [prod] Backup de BD ANTES de migrar (aborta el deploy si falla)..."
    # scripts/backup-db.sh lee credenciales del .env, hace mysqldump+gzip, valida el
    # dump y aplica retención. Si falla, 'set -e' corta acá (el trap deja el sitio arriba)
    # y NO se corre migrate --force sobre una BD sin backup.
    bash scripts/backup-db.sh
  else
    echo "→ [sandbox] Se omite el backup de BD (base descartable)."
  fi

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
  STATUS="success"; echo "✅ Deploy complete."
else
  STATUS="failure"; echo "❌ Deploy falló."
fi

# Recortar los commits deployados del log y dejarlos JSON-safe (escapar \ y ",
# y colapsar los saltos de línea a \n) para el mensaje de la notificación.
DEPLOYED_COMMITS="$(sed -n '/===DEPLOYED_COMMITS_START===/,/===DEPLOYED_COMMITS_END===/p' "$DEPLOY_LOG" 2>/dev/null | grep -vE '===DEPLOYED_COMMITS_(START|END)===' || true)"
COMMITS_JSON="$(printf '%s' "$DEPLOYED_COMMITS" | sed 's/\\/\\\\/g; s/"/\\"/g' | awk 'NR>1{printf "\\n"} {printf "%s", $0}')"
rm -f "$DEPLOY_LOG"

if [ -n "$DEPLOYED_COMMITS" ]; then
  echo "📦 Commits deployados:"; printf '%s\n' "$DEPLOYED_COMMITS"
fi

notify_n8n "$STATUS" "$START_TIME" "$COMMITS_JSON"
[ "$STATUS" = "failure" ] && exit 1
exit 0
