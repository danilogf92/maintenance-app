FROM composer:2 AS composer
FROM php:8.3-fpm-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libonig-dev libxml2-dev libsqlite3-dev weasyprint fonts-dejavu-core \
    && python3 -c "from weasyprint import HTML; HTML(string='<p>PDF renderer OK</p>').write_pdf('/tmp/pdf-smoke.pdf')" \
    && rm /tmp/pdf-smoke.pdf \
    && docker-php-ext-install pdo_mysql pdo_sqlite intl zip mbstring dom exif pcntl opcache \
    && rm -rf /var/lib/apt/lists/*
COPY docker/performance.ini /usr/local/etc/php/conf.d/maintenance-performance.ini
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs storage/app/public storage/app/private bootstrap/cache \
    && composer install --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache
COPY docker/entrypoint.sh /usr/local/bin/template-entrypoint
RUN chmod +x /usr/local/bin/template-entrypoint
ENTRYPOINT ["template-entrypoint"]
CMD ["php-fpm"]
