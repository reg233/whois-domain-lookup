FROM dunglas/frankenphp:1-php8.4-alpine AS base
WORKDIR /app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN install-php-extensions intl

FROM base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache unzip
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

FROM base AS runtime
COPY --from=dependencies /app/vendor ./vendor
COPY config ./config
COPY public ./public
RUN rm /app/public/index.php
COPY src ./src
COPY Caddyfile /etc/caddy/Caddyfile
COPY favicon.ico ./favicon.ico
ENV PORT=80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
