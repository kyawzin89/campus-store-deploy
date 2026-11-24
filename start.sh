#!/usr/bin/env bash
set -e

mkdir -p /app/storage/database
touch /app/storage/database/database.sqlite

php artisan migrate --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
