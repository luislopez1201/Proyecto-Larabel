#!/bin/bash
php artisan storage:link

# Esperar a que la base de datos esté lista
php artisan migrate --force

php artisan serve --host=0.0.0.0 --port=8000