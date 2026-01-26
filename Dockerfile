FROM php:8.2-fpm-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS git unzip libzip-dev oniguruma-dev icu-dev zlib-dev curl && \
    apk add --no-cache nginx bash zip ca-certificates openssl && \
    docker-php-ext-install pdo pdo_mysql zip mbstring intl && \
    apk del .build-deps || true

WORKDIR /var/www/html

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies before copying full context to leverage cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist --no-scripts --no-progress --prefer-stable

# Copy app
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
