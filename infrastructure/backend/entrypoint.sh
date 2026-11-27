#!/bin/sh
set -e

cd /app

if [ -f .env ]; then
    echo "[entrypoint] AVISO: existe apps/backend/.env y tiene prioridad sobre envs/.env.dev" >&2
fi

if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] AVISO: APP_KEY vacío en envs/.env.dev" >&2
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --no-progress --prefer-dist
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-database/database.sqlite}"
    [ -f "$DB_FILE" ] || touch "$DB_FILE"
fi

php artisan config:clear >/dev/null

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
