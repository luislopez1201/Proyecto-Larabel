#!/bin/bash

if [ ! -L "public/storage" ]; then
    rm -rf public/storage
    php artisan storage:link
fi

# Ejecutar migraciones forzadas
php artisan migrate --force

# Iniciar el servidor Laravel
php artisan serve --host=0.0.0.0 --port=8000
