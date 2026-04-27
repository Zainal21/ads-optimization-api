FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts && \
    chown -R root:root /app/vendor && \
    chmod -R 755 /app/vendor

COPY . .

RUN composer dump-autoload --optimize

FROM dunglas/frankenphp:1-php8.3

WORKDIR /app

COPY --from=vendor /app /app

COPY Caddyfile /etc/caddy/Caddyfile

RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             bootstrap/cache \
             database && \
    touch database/database.sqlite || true && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database && \
    chmod -R 775 /app/storage /app/bootstrap/cache && \
    chmod -R 664 /app/database/database.sqlite

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
