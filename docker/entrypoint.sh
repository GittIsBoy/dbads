#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate
fi

php artisan config:cache || true
php artisan route:cache || true

php-fpm -D
nginx -g 'daemon off;'
