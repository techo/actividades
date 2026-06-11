#!/bin/bash
# init.sh — Verificaciones livianas del harness
# Corre sin Docker. Para los tests completos (phpunit) necesitás Docker activo.

set -e
ROOT="$(cd "$(dirname "$0")" && pwd)"
ERRORS=0

red()   { echo -e "\033[31m✗ $*\033[0m"; }
green() { echo -e "\033[32m✓ $*\033[0m"; }
warn()  { echo -e "\033[33m⚠ $*\033[0m"; }

echo "── Verificando harness ──────────────────────────"

# 1. Archivos del harness existen
for f in CLAUDE.md AGENTS.md tasks.json progress/current.md progress/history.md; do
  if [ -f "$ROOT/$f" ]; then
    green "$f existe"
  else
    red "$f NO encontrado"
    ERRORS=$((ERRORS + 1))
  fi
done

# 2. tasks.json es JSON válido
if command -v php &>/dev/null; then
  if php -r "json_decode(file_get_contents('$ROOT/tasks.json')); if (json_last_error() !== JSON_ERROR_NONE) exit(1);" 2>/dev/null; then
    green "tasks.json es JSON válido"
  else
    red "tasks.json tiene JSON inválido"
    ERRORS=$((ERRORS + 1))
  fi
fi

echo ""
echo "── Verificando código PHP ───────────────────────"

# 3. Sintaxis PHP en archivos modificados (o todo app/ si no hay git)
if command -v php &>/dev/null; then
  if git -C "$ROOT" rev-parse --is-inside-work-tree &>/dev/null 2>&1; then
    CHANGED_PHP=$(git -C "$ROOT" diff --name-only HEAD 2>/dev/null | grep '\.php$' || true)
    STAGED_PHP=$(git -C "$ROOT" diff --cached --name-only 2>/dev/null | grep '\.php$' || true)
    FILES_TO_CHECK=$(echo -e "$CHANGED_PHP\n$STAGED_PHP" | sort -u | grep -v '^$' || true)
  else
    FILES_TO_CHECK=""
  fi

  if [ -z "$FILES_TO_CHECK" ]; then
    warn "No hay archivos PHP modificados en git — saltando lint"
  else
    while IFS= read -r file; do
      fullpath="$ROOT/$file"
      if [ -f "$fullpath" ]; then
        if php -l "$fullpath" &>/dev/null; then
          green "php -l $file"
        else
          red "Error de sintaxis en $file"
          php -l "$fullpath" 2>&1 | tail -1
          ERRORS=$((ERRORS + 1))
        fi
      fi
    done <<< "$FILES_TO_CHECK"
  fi
else
  warn "PHP no disponible — saltando lint"
fi

echo ""
echo "── Verificando debug calls ──────────────────────"

# 4. No debe haber dd(), dump(), var_dump() en archivos modificados
if git -C "$ROOT" rev-parse --is-inside-work-tree &>/dev/null 2>&1; then
  ALL_CHANGED=$(git -C "$ROOT" diff --name-only HEAD 2>/dev/null; git -C "$ROOT" diff --cached --name-only 2>/dev/null)
  ALL_CHANGED=$(echo "$ALL_CHANGED" | sort -u | grep '\.php$' || true)

  DEBUG_FOUND=0
  while IFS= read -r file; do
    fullpath="$ROOT/$file"
    if [ -f "$fullpath" ]; then
      if grep -nE '\b(dd|dump|var_dump)\s*\(' "$fullpath" 2>/dev/null; then
        red "Debug call encontrado en $file"
        DEBUG_FOUND=$((DEBUG_FOUND + 1))
        ERRORS=$((ERRORS + 1))
      fi
    fi
  done <<< "$ALL_CHANGED"

  if [ "$DEBUG_FOUND" -eq 0 ]; then
    green "Sin debug calls en archivos modificados"
  fi
else
  warn "No es un repo git — saltando check de debug calls"
fi

echo ""
echo "── Resumen ──────────────────────────────────────"

if [ "$ERRORS" -eq 0 ]; then
  green "Todo OK. Podés cerrar la sesión."
  echo ""
  echo "  Para correr los tests completos (requiere Docker):"
  echo "  docker compose exec app vendor/bin/phpunit"
  exit 0
else
  red "$ERRORS error(s) encontrado(s). Resolverlos antes de cerrar la sesión."
  exit 1
fi

echo ""
echo "── Fase de upgrade ──────────────────────────────"

# Detectar en qué fase de upgrade estamos según la versión de Laravel en vendor
if [ -f "$ROOT/vendor/laravel/framework/src/Illuminate/Foundation/Application.php" ]; then
  LARAVEL_VERSION=$(grep "const VERSION" "$ROOT/vendor/laravel/framework/src/Illuminate/Foundation/Application.php" | grep -oE "[0-9]+\.[0-9]+" | head -1)
  green "Laravel $LARAVEL_VERSION detectado"
  case "$LARAVEL_VERSION" in
    5.*) warn "Fase pendiente: Fase 0 (baseline) → Tarea upgrade-0-baseline en tasks.json" ;;
    6.*) warn "Fase actual: L6.x ✓ → Próxima: Fase 2 (L7.x)" ;;
    7.*) warn "Fase actual: L7.x ✓ → Próxima: Fase 3 (factories + L8.x)" ;;
    8.*) warn "Fase actual: L8.x ✓ → Próxima: Fase 4 (L9.x)" ;;
    9.*) warn "Fase actual: L9.x ✓ → Próxima: Fase 5 (L10.x + PHP 8.1)" ;;
    10.*) warn "Fase actual: L10.x ✓ → Próxima: Fase 6 (L11.x + PHP 8.2)" ;;
    11.*) green "Laravel 11.x — upgrade completo" ;;
  esac
fi
