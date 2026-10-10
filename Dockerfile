FROM composer:2 AS composer
FROM php:8.3-fpm-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libonig-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite intl zip mbstring dom exif \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache
COPY docker/entrypoint.sh /usr/local/bin/template-entrypoint
RUN chmod +x /usr/local/bin/template-entrypoint
ENTRYPOINT ["template-entrypoint"]
CMD ["php-fpm"]
