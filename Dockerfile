FROM dunglas/frankenphp:1-php8.4-alpine AS base
WORKDIR /app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && rm -rf public/ \
    && install-php-extensions intl

FROM base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/
COPY composer.json composer.lock ./
RUN apk add --no-cache unzip \
    && composer install --no-dev --no-interaction --no-progress --optimize-autoloader --prefer-dist

FROM base AS assets
COPY public ./public/
COPY scripts/hash-assets.sh ./
RUN sh hash-assets.sh

FROM base AS runtime
COPY --from=dependencies /app/vendor ./vendor/
COPY favicon.ico robots.txt ./
COPY Caddyfile /etc/caddy/
COPY --from=assets /app/assets ./assets/
COPY config ./config/
COPY src ./src/

ENV PORT=80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
