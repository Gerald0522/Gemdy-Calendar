FROM serversideup/php:8.5-fpm-nginx-alpine AS build

WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize --no-dev --no-interaction

FROM serversideup/php:8.5-fpm-nginx-alpine

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    HEALTHCHECK_PATH=/up

WORKDIR /var/www/html
COPY --from=build --chown=www-data:www-data /var/www/html .
COPY --chmod=755 bootstrap/20-production-env.sh /etc/entrypoint.d/20-production-env.sh
USER www-data
EXPOSE 8080
