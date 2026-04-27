FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize

FROM dunglas/frankenphp:1-php8.3
WORKDIR /app
COPY --from=vendor /app /app
COPY Caddyfile /etc/caddy/Caddyfile
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache database \
    && test -f database/database.sqlite || touch database/database.sqlite
EXPOSE 80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
