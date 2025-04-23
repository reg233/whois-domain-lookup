FROM php:8.3-apache

RUN apt-get update && apt-get install -y libicu-dev && \
    docker-php-ext-install intl && \
    apt-get clean && rm -rf /var/lib/apt/lists/* && \
    a2enmod rewrite && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . ./

CMD ["apache2-foreground"]
