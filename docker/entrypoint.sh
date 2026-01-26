#!/bin/sh
set -e

cd /var/www/html

# Ensure .env exists
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# Ensure composer autoloads are present (safe best-effort)
if command -v composer >/dev/null 2>&1; then
  if [ -f composer.json ]; then
    composer dump-autoload --optimize || composer install --no-dev --no-interaction --optimize-autoloader || true
  fi
fi

# Generate app key if missing
if command -v php >/dev/null 2>&1; then
  php artisan key:generate --force || true
  php artisan config:cache || true
  php artisan route:cache || true
fi

# Start php-fpm and nginx
if command -v php-fpm >/dev/null 2>&1; then
  php-fpm -D || true
fi

nginx -g 'daemon off;'
