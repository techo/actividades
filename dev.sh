#!/usr/bin/env bash
#
# dev.sh — Levanta el sistema de voluntariado de TECHO en local con Docker.
#
# Este script cumple doble función: es la forma recomendada de arrancar el
# entorno de desarrollo y, a la vez, DOCUMENTA el setup completo. Si algo del
# arranque no te queda claro, leelo: cada paso está comentado.
#
# ─────────────────────────────────────────────────────────────────────────────
# Stack (definido en docker-compose.yml)
#   app    → PHP 7.2-fpm + Composer   (contenedor: laravel_app)
#   nginx  → sirve public/ en http://localhost:8000   (contenedor: laravel_nginx)
#   mysql  → MySQL 5.7, base "laravel"  (contenedor: laravel_mysql, host :3307)
#   node   → Node 10 para compilar los assets (Vue 2 + Laravel Mix)
#
#   Se usa Node 10 a propósito: el build (laravel-mix + node-sass 4) NO compila
#   con Node moderno. Por eso los assets SIEMPRE se compilan dentro del
#   contenedor, nunca con el node del host.
#
# ─────────────────────────────────────────────────────────────────────────────
# Uso
#   ./dev.sh up          Arranca todo. Primera vez: instala deps, crea la base
#                        y la puebla, compila assets. Es idempotente: podés
#                        volver a correrlo sin romper nada.
#   ./dev.sh fresh       Recrea la base desde cero (migrate:fresh --seed).
#                        ⚠ Borra todos los datos locales.
#   ./dev.sh assets      Recompila los assets JS/CSS una sola vez.
#   ./dev.sh watch       Compila assets en vivo (queda escuchando cambios).
#   ./dev.sh artisan ... Corre "php artisan ..." dentro del contenedor.
#   ./dev.sh composer ...Corre "composer ..." dentro del contenedor.
#   ./dev.sh sh          Abre una shell en el contenedor de PHP.
#   ./dev.sh logs        Muestra los logs (Ctrl-C para salir).
#   ./dev.sh down        Apaga los contenedores (los datos se conservan).
#   ./dev.sh help        Muestra esta ayuda.
#
# Requisitos: Docker Desktop (o Docker Engine) con el plugin `docker compose`.
#
set -euo pipefail
cd "$(dirname "$0")"

# ── Colores para que los mensajes se lean ────────────────────────────────────
if [ -t 1 ]; then
  BOLD=$'\033[1m'; GREEN=$'\033[32m'; YELLOW=$'\033[33m'; RED=$'\033[31m'; RESET=$'\033[0m'
else
  BOLD=""; GREEN=""; YELLOW=""; RED=""; RESET=""
fi
say()  { echo "${BOLD}${GREEN}▶ $*${RESET}"; }
warn() { echo "${BOLD}${YELLOW}⚠ $*${RESET}"; }
die()  { echo "${BOLD}${RED}✖ $*${RESET}" >&2; exit 1; }

# ── Detectar `docker compose` (v2) vs `docker-compose` (v1) ──────────────────
if docker compose version >/dev/null 2>&1; then
  DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  DC="docker-compose"
else
  die "No encuentro 'docker compose'. Instalá Docker Desktop o el plugin compose."
fi

# Helpers: correr comandos dentro de los contenedores.
dc()      { $DC "$@"; }
in_app()  { $DC exec -T app "$@"; }                       # PHP/artisan/composer
in_node() { $DC run --rm node sh -c "$*"; }               # build de assets (one-off)

# ─────────────────────────────────────────────────────────────────────────────
ensure_env() {
  if [ ! -f .env ]; then
    say "Creando .env a partir de .env.example"
    cp .env.example .env
  fi
}

wait_for_mysql() {
  say "Esperando a que MySQL acepte conexiones..."
  for i in $(seq 1 60); do
    if in_app php -r 'exit(@mysqli_connect("mysql","laravel","password","laravel")?0:1);' >/dev/null 2>&1; then
      return 0
    fi
    sleep 2
  done
  die "MySQL no respondió a tiempo. Revisá './dev.sh logs'."
}

composer_install() {
  if [ ! -f vendor/autoload.php ]; then
    say "Instalando dependencias PHP (composer install)"
    in_app composer install --no-interaction --prefer-dist
  fi
}

app_key() {
  # Genera APP_KEY solo si está vacío en .env.
  if grep -qE '^APP_KEY=$' .env; then
    say "Generando APP_KEY"
    in_app php artisan key:generate --no-interaction
  fi
}

passport_keys() {
  # Passport necesita un par de claves OAuth. Las generamos una sola vez.
  if [ ! -f storage/oauth-private.key ]; then
    say "Generando claves de Laravel Passport"
    in_app php artisan passport:install --no-interaction || \
      warn "passport:install falló (podés reintentarlo con './dev.sh artisan passport:install')."
  fi
}

storage_setup() {
  in_app php artisan storage:link --no-interaction >/dev/null 2>&1 || true
  in_app sh -c 'chmod -R ug+rwX storage bootstrap/cache' >/dev/null 2>&1 || true
}

db_setup() {
  # Primera vez (sin marcador): migra Y puebla la base.
  # Siguientes: solo aplica migraciones nuevas, sin re-sembrar.
  if [ ! -f storage/.dev-db-seeded ]; then
    say "Migrando y poblando la base (primera vez)"
    in_app php artisan migrate --seed --force
    touch storage/.dev-db-seeded
  else
    say "Aplicando migraciones pendientes"
    in_app php artisan migrate --force
  fi
}

build_assets() {
  # El archivo de traducciones de Vue es generado y está gitignoreado: hay que
  # regenerarlo antes de compilar (si no, el build falla al importarlo).
  say "Generando traducciones de Vue (vue-i18n:generate)"
  in_app php artisan vue-i18n:generate || warn "vue-i18n:generate falló; el build puede quejarse."
  say "Compilando assets (esto puede tardar la primera vez)"
  in_node "npm install && npm run development"
}

banner() {
  cat <<EOF

${BOLD}${GREEN}✔ Entorno listo${RESET}

  App:      ${BOLD}http://localhost:8000${RESET}
  MySQL:    localhost:${BOLD}3307${RESET}  (base: laravel · user: laravel · pass: password)

  Usuarios de prueba (seed):
    admin:       ${BOLD}administrador@administrador.com${RESET} / administrador
    coordinador: ${BOLD}coordinador@coordinador.com${RESET} / coordinador

  Compilar assets en vivo:   ./dev.sh watch
  Ver logs:                  ./dev.sh logs
  Apagar:                    ./dev.sh down

EOF
}

# ─────────────────────────────────────────────────────────────────────────────
cmd="${1:-up}"; shift || true

case "$cmd" in
  up)
    ensure_env
    say "Levantando contenedores (app, nginx, mysql)"
    dc up -d --build app nginx mysql
    wait_for_mysql
    composer_install
    app_key
    storage_setup
    passport_keys
    db_setup
    build_assets
    banner
    ;;

  fresh)
    ensure_env
    dc up -d app nginx mysql
    wait_for_mysql
    composer_install
    warn "Recreando la base desde cero (se pierden los datos locales)."
    in_app php artisan migrate:fresh --seed --force
    touch storage/.dev-db-seeded
    build_assets
    banner
    ;;

  assets)  ensure_env; build_assets ;;
  watch)   say "Compilando en vivo (Ctrl-C para cortar)"; dc up node ;;
  artisan) in_app php artisan "$@" ;;
  composer) in_app composer "$@" ;;
  sh)      dc exec app sh ;;
  logs)    dc logs -f --tail=100 ;;
  down)    say "Apagando contenedores"; dc down ;;
  help|-h|--help)
    sed -n '2,60p' "$0" | sed 's/^# \{0,1\}//'
    ;;
  *)
    die "Comando desconocido: '$cmd'. Probá './dev.sh help'."
    ;;
esac
