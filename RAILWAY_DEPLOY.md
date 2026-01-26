Deploy to Railway
=================

Quick steps to deploy this Laravel app on Railway using the included Dockerfile:

1. Push this repo to GitHub (already done).
2. In Railway, create a new project and choose "Deploy from GitHub" and connect the `GittIsBoy/dbads` repo.
3. Railway will detect the `Dockerfile` and build the image.
4. Set required environment variables in Railway (Environment tab):
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY` (can be left empty — entrypoint will generate from `.env.example`)
   - Database variables: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - Add any other service keys (mail, services, etc.) from `config/*.php` as needed.
5. Set the service port to `80` (Railway usually exposes the container's port automatically).
6. After deploy, run migrations (Railway has a console to run commands):

   php artisan migrate --force

Notes:
- The `Dockerfile` boots `php-fpm` and `nginx`. The `entrypoint.sh` copies `.env.example` to `.env` if none exists and runs `php artisan key:generate`.
- If you prefer Railway buildpacks instead of Docker, remove the `Dockerfile` and use Railway's PHP build options.
