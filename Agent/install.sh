#!/bin/sh

cp .env.example .env
composer install --no-dev -vvv

php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
php artisan vendor:publish --tag=laravel-admin-grid-lightbox

php artisan key:generate

php artisan storage:link


