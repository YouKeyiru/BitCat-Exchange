#!/bin/sh

cp .env.example .env
composer install --no-dev -vvv

php artisan storage:link
#chmod -R 755 storage/
php artisan key:generate
php artisan jwt:secret
