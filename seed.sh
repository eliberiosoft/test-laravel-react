#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INFRA_DIR="$ROOT_DIR/infrastructure"

FRESH=false
ASSUME_YES=false
SAMPLE=0
SAMPLE_DEFAULT=50

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
Uso: ./seed.sh [opciones]

  (sin opciones)    Roles, permisos, menú y usuarios demo. Se puede repetir: no duplica y restaura los usuarios demo
  --sample [N]      Además crea N usuarios de ejemplo con estados variados (por defecto $SAMPLE_DEFAULT)
  --fresh           Borra todas las tablas, corre las migraciones y vuelve a poblar
  -y, --yes         No pide confirmación
  -h, --help        Muestra esta ayuda

Ejemplos:
  ./seed.sh
  ./seed.sh --sample 100
  ./seed.sh --fresh --sample
EOF
}

while [ $# -gt 0 ]; do
    case "$1" in
        --sample)
            SAMPLE="$SAMPLE_DEFAULT"
            if [ $# -gt 1 ] && [[ "$2" =~ ^[0-9]+$ ]]; then
                SAMPLE="$2"
                shift
            fi
            ;;
        --sample=*) SAMPLE="${1#--sample=}" ;;
        --fresh) FRESH=true ;;
        -y|--yes) ASSUME_YES=true ;;
        -h|--help) usage; exit 0 ;;
        *) usage; fail "Opción desconocida: $1" ;;
    esac
    shift
done

[[ "$SAMPLE" =~ ^[0-9]+$ ]] || fail "--sample espera un número"

command -v docker >/dev/null 2>&1 || fail "Docker no está instalado"
docker info >/dev/null 2>&1 || fail "Docker no está corriendo"

cd "$INFRA_DIR"

docker compose ps --status running --services 2>/dev/null | grep -qx backend || fail "El backend no está corriendo. Levanta el ambiente con ./up.sh"

artisan() { docker compose exec -T backend php artisan "$@"; }

if [ "$FRESH" = true ]; then
    if [ "$ASSUME_YES" = false ]; then
        warn "Se borrarán TODAS las tablas de la base de desarrollo (usuarios, sesiones, cargas y auditoría)"
        read -r -p "¿Continuar? [s/N] " answer
        [[ "$answer" =~ ^[sS]$ ]] || fail "Cancelado"
    fi
    info "Recreando la base de datos"
    artisan migrate:fresh --force
    ok "Base recreada"
fi

info "Cargando roles, permisos, menú y usuarios demo"
artisan db:seed --force
ok "Datos base cargados"

if [ "$SAMPLE" -gt 0 ]; then
    info "Creando $SAMPLE usuarios de ejemplo"
    artisan users:sample "$SAMPLE"
fi

echo
printf "${GREEN}Base poblada${NC}\n"
printf "  Administrador   Admin001 / Admin!2026\n"
printf "  Usuario         Jpiguave01 / User!2026\n"
[ "$SAMPLE" -gt 0 ] && printf "  Ejemplo         Muestra0001… / Muestra!2026 (1 de cada 10 bloqueado y 1 de cada 10 inactivo)\n"
printf "  Carga masiva    docs/import-sample.csv desde la pantalla Carga masiva\n"
