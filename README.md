## Yoga Studio ERP

## Install command (locally dev environment)
composer install
php artisan migrate
# from the Laravel project root
npm install
npm run build

## Start (locally) command
php artisan view:clear 
php artisan view:cache
php artisan optimize:clear
php artisan route:clear  
php artisan serve --host=127.0.0.1 --port=8000

## Software needs
Php 8.4.x
MySql Database
Composer (just for development environment)
Node.js (just for development environment)

## Deploy on service provider (no Node.js needs)
Upload PHP files (app/, routes/, resources/ if you like, etc.)
Upload public/ (including public/build)
Upload vendor/ (when Service Provider do not support Composer)
Set .env on server
Run commands:
    php artisan config:cache
    php artisan route:cache

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
