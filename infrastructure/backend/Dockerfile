FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

FROM dunglas/frankenphp:1-php8.5-alpine AS app

RUN install-php-extensions bcmath gd intl pcntl pdo_mysql pdo_pgsql redis zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=infra php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY --from=infra entrypoint.sh /usr/local/bin/entrypoint

ENV SERVER_NAME=:80

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN rm -f .env \
    && composer dump-autoload --optimize --no-scripts \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
