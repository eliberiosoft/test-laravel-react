#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INFRA_DIR="$ROOT_DIR/infrastructure"
ENV_FILE="$ROOT_DIR/envs/.env.dev"
PORTS_FILE="$ROOT_DIR/envs/ports.env"
BACKEND_DIR="$ROOT_DIR/apps/backend"
TIMEOUT=180

FRESH=false
BUILD=true
SEED=false

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

info()  { printf "${BLUE}==>${NC} %s\n" "$1"; }
ok()    { printf "${GREEN}✔${NC} %s\n" "$1"; }
warn()  { printf "${YELLOW}!${NC} %s\n" "$1"; }
fail()  { printf "${RED}✖ %s${NC}\n" "$1" >&2; exit 1; }

usage() {
    cat <<EOF
Uso: ./up.sh [opciones]

  --fresh      Borra contenedores y volúmenes (MySQL, Redis, Mailpit, vendor, node_modules) antes de levantar
  --no-build   No reconstruye la imagen del backend
  --seed       Carga roles, permisos, menú y usuarios demo al terminar (para más datos: ./seed.sh)
  -h, --help   Muestra esta ayuda
EOF
}

for arg in "$@"; do
    case "$arg" in
        --fresh) FRESH=true ;;
        --no-build) BUILD=false ;;
        --seed) SEED=true ;;
        -h|--help) usage; exit 0 ;;
        *) usage; fail "Opción desconocida: $arg" ;;
    esac
done

info "Verificando requisitos"
command -v docker >/dev/null 2>&1 || fail "Docker no está instalado"
docker info >/dev/null 2>&1 || fail "Docker no está corriendo. Inicia Docker Desktop y vuelve a intentar"
docker compose version >/dev/null 2>&1 || fail "Docker Compose v2 no está disponible"
ok "Docker $(docker version --format '{{.Server.Version}}') / $(docker compose version --short)"

info "Verificando puertos"
[ -f "$PORTS_FILE" ] || fail "No existe envs/ports.env"
set -a
. "$PORTS_FILE"
set +a
BACKEND_PORT="${BACKEND_PORT:-8000}"
FRONTEND_PORT="${FRONTEND_PORT:-5173}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
REDIS_PORT="${REDIS_PORT:-6379}"
MAILPIT_SMTP_PORT="${MAILPIT_SMTP_PORT:-1025}"
MAILPIT_UI_PORT="${MAILPIT_UI_PORT:-8025}"
APP_URL="http://localhost:$BACKEND_PORT"
FRONTEND_URL="http://localhost:$FRONTEND_PORT"
MAILPIT_URL="http://localhost:$MAILPIT_UI_PORT"
if [ "$(readlink "$INFRA_DIR/.env" 2>/dev/null)" != "../envs/ports.env" ]; then
    rm -f "$INFRA_DIR/.env"
    ln -s ../envs/ports.env "$INFRA_DIR/.env"
    ok "Enlace infrastructure/.env -> envs/ports.env creado"
fi
ok "Frontend $FRONTEND_PORT · Backend $BACKEND_PORT · MySQL $MYSQL_PORT · Redis $REDIS_PORT · Mailpit $MAILPIT_SMTP_PORT/$MAILPIT_UI_PORT"

info "Verificando archivo de entorno"
if [ ! -f "$ENV_FILE" ]; then
    [ -f "$ROOT_DIR/envs/.env.example" ] || fail "No existe envs/.env.dev ni envs/.env.example"
    cp "$ROOT_DIR/envs/.env.example" "$ENV_FILE"
    ok "envs/.env.dev creado desde envs/.env.example"
fi
if [ -f "$BACKEND_DIR/.env" ]; then
    warn "apps/backend/.env existe y tendría prioridad sobre envs/.env.dev"
    read -r -p "¿Eliminarlo? [s/N] " answer
    if [[ "$answer" =~ ^[sS]$ ]]; then
        rm -f "$BACKEND_DIR/.env"
        ok "apps/backend/.env eliminado"
    fi
fi

if ! grep -qE '^APP_KEY=.+' "$ENV_FILE"; then
    command -v openssl >/dev/null 2>&1 || fail "APP_KEY vacío y openssl no está disponible para generarlo"
    NEW_KEY="base64:$(openssl rand -base64 32)"
    TMP_FILE="$(mktemp)"
    if grep -qE '^APP_KEY=' "$ENV_FILE"; then
        awk -v key="$NEW_KEY" '/^APP_KEY=/{print "APP_KEY=" key; next} {print}' "$ENV_FILE" > "$TMP_FILE"
    else
        { echo "APP_KEY=$NEW_KEY"; cat "$ENV_FILE"; } > "$TMP_FILE"
    fi
    cat "$TMP_FILE" > "$ENV_FILE"
    rm -f "$TMP_FILE"
    ok "APP_KEY generado en envs/.env.dev"
else
    ok "envs/.env.dev listo"
fi

cd "$INFRA_DIR"

if [ "$FRESH" = true ]; then
    warn "Se borrarán contenedores y volúmenes (se pierden los datos de MySQL, Redis y Mailpit)"
    read -r -p "¿Continuar? [s/N] " answer
    [[ "$answer" =~ ^[sS]$ ]] || fail "Cancelado"
    docker compose down -v --remove-orphans
    ok "Ambiente limpio"
fi

info "Levantando contenedores"
if [ "$BUILD" = true ]; then
    docker compose up -d --build --remove-orphans
else
    docker compose up -d --remove-orphans
fi

info "Esperando a que el backend responda en $APP_URL/up"
elapsed=0
until [ "$(curl -s -o /dev/null -w '%{http_code}' "$APP_URL/up" || true)" = "200" ]; do
    if [ "$(docker compose ps -q backend | xargs docker inspect -f '{{.State.Running}}' 2>/dev/null)" != "true" ]; then
        docker compose logs --tail=50 backend
        fail "El contenedor backend se detuvo"
    fi
    if [ "$elapsed" -ge "$TIMEOUT" ]; then
        docker compose logs --tail=50 backend
        fail "El backend no respondió en ${TIMEOUT}s"
    fi
    sleep 2
    elapsed=$((elapsed + 2))
    printf "."
done
[ "$elapsed" -gt 0 ] && echo
ok "Backend listo"

info "Esperando a que el frontend responda en $FRONTEND_URL"
elapsed=0
until [ "$(curl -s -o /dev/null -w '%{http_code}' "$FRONTEND_URL" || true)" = "200" ]; do
    if [ "$elapsed" -ge "$TIMEOUT" ]; then
        docker compose logs --tail=50 frontend
        fail "El frontend no respondió en ${TIMEOUT}s"
    fi
    sleep 2
    elapsed=$((elapsed + 2))
    printf "."
done
[ "$elapsed" -gt 0 ] && echo
ok "Frontend listo"

if [ "$SEED" = true ]; then
    info "Ejecutando seeders"
    docker compose exec -T backend php artisan db:seed --force
    ok "Seeders ejecutados"
fi

echo
docker compose ps --format 'table {{.Service}}\t{{.Status}}\t{{.Ports}}'
echo
printf "${GREEN}Ambiente listo${NC}\n"
printf "  Frontend  %s\n" "$FRONTEND_URL"
printf "  Backend   %s\n" "$APP_URL"
printf "  API docs  %s/docs/api\n" "$APP_URL"
printf "  Mailpit   %s\n" "$MAILPIT_URL"
printf "  MySQL     localhost:%s\n" "$MYSQL_PORT"
printf "  Redis     localhost:%s\n" "$REDIS_PORT"
printf "  SMTP      localhost:%s\n" "$MAILPIT_SMTP_PORT"
echo
printf "  Logs      cd infrastructure && docker compose logs -f\n"
printf "  Poblar    ./seed.sh --sample\n"
printf "  Artisan   cd infrastructure && docker compose exec backend php artisan <comando>\n"
printf "  Detener   ./down.sh\n"
