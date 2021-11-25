cp .env.example .env
composer install
php artisan storage:link
chmod -R 755 storage/
php artisan key:generate
php artisan jwt:secret

