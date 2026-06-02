#!/bin/bash

echo "Esperando a MySQL..."
until php artisan db:show --json 2>/dev/null | grep -q "payment_webhook"; do
    sleep 2
done

echo "MySQL listo. Corriendo migraciones..."
php artisan config:clear
php artisan migrate --force
php artisan cache:clear

exec "$@"
