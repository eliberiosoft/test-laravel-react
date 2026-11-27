#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INFRA_DIR="$ROOT_DIR/infrastructure"

VOLUMES=false
IMAGES=false
ASSUME_YES=false

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
Uso: ./down.sh [opciones]

  --volumes    Borra también los volúmenes (datos de MySQL, Redis, Mailpit, vendor y node_modules)
  --images     Borra también la imagen del backend
  --all        Equivale a --volumes --images
  -y, --yes    No pide confirmación
  -h, --help   Muestra esta ayuda
EOF
}

for arg in "$@"; do
    case "$arg" in
        --volumes) VOLUMES=true ;;
        --images) IMAGES=true ;;
        --all) VOLUMES=true; IMAGES=true ;;
        -y|--yes) ASSUME_YES=true ;;
        -h|--help) usage; exit 0 ;;
        *) usage; fail "Opción desconocida: $arg" ;;
    esac
done

command -v docker >/dev/null 2>&1 || fail "Docker no está instalado"
docker info >/dev/null 2>&1 || fail "Docker no está corriendo"

cd "$INFRA_DIR"

RUNNING="$(docker compose ps -q 2>/dev/null | wc -l | tr -d ' ')"
if [ "$RUNNING" = "0" ] && [ "$VOLUMES" = false ] && [ "$IMAGES" = false ]; then
    ok "No hay contenedores corriendo"
    exit 0
fi

if [ "$VOLUMES" = true ] && [ "$ASSUME_YES" = false ]; then
    warn "Se borrarán los volúmenes: se pierden los datos de MySQL, Redis y Mailpit"
    read -r -p "¿Continuar? [s/N] " answer
    [[ "$answer" =~ ^[sS]$ ]] || fail "Cancelado"
fi

BACKEND_IMAGE="$(docker compose config | awk '/^  backend:/{f=1;next} f&&/^  [^ ]/{f=0} f&&/^    image:/{print $2; exit}')"

ARGS=(--remove-orphans)
[ "$VOLUMES" = true ] && ARGS+=(--volumes)

info "Bajando el ambiente"
docker compose down "${ARGS[@]}"
ok "Contenedores detenidos y eliminados"
[ "$VOLUMES" = true ] && ok "Volúmenes eliminados"

if [ "$IMAGES" = true ]; then
    if [ -n "$BACKEND_IMAGE" ] && docker image inspect "$BACKEND_IMAGE" >/dev/null 2>&1; then
        docker image rm "$BACKEND_IMAGE" >/dev/null
        ok "Imagen $BACKEND_IMAGE eliminada"
    else
        warn "La imagen del backend no existe"
    fi
fi

echo
printf "${GREEN}Ambiente abajo${NC}\n"
printf "  Levantar de nuevo   ./up.sh\n"
