FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize

FROM php:8.4-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache bash postgresql-dev sqlite-dev icu-dev \
    && docker-php-ext-install intl pdo_pgsql pdo_sqlite

COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
