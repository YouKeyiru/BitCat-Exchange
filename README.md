### API
```
cp .env.example .env
composer install 
php artisan storage:link 
chmod -R 755 storage/ 
php artisan key:generate
php artisan jwt:secret 
```

### Admin
### Init
```
cp .env.example .env
composer install
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
php artisan vendor:publish --tag=laravel-admin-grid-lightbox
php artisan vendor:publish --tag=laravel-admin-wangEditor


php artisan key:generate


php artisan migrate --path database\migrations\
php artisan db:seed
```




### Back
```
php artisan migration:generate
php artisan admin:export-seed --users
php artisan iseed products_contract
php artisan iseed products_exchange
php artisan iseed wallet_code
```

QQ:58787244 <br/>
Telegram:@youkeyi

