FROM php:8.4-apache

RUN a2enmod rewrite && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . ./

CMD ["apache2-foreground"]
