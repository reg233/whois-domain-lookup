FROM php:8.3-apache

RUN apt-get update && apt-get install -y libicu-dev && \
    docker-php-ext-install intl && \
    apt-get clean && rm -rf /var/lib/apt/lists/* && \
    a2enmod rewrite && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . ./

RUN mv entrypoint.sh /usr/local/bin/whois-domain-lookup-entrypoint && \
    chmod +x /usr/local/bin/whois-domain-lookup-entrypoint

ENTRYPOINT ["whois-domain-lookup-entrypoint"]
CMD ["apache2-foreground"]
