FROM composer:2 as vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist

FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx bash git libzip-dev zip unzip oniguruma-dev icu-dev zlib-dev && \
    docker-php-ext-install pdo pdo_mysql zip mbstring intl && \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS && \
    apk del .build-deps || true

WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
