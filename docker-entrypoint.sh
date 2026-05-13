#!/bin/bash
set -e

php artisan config:clear
php artisan migrate --force
php artisan cache:clear

exec "$@"