composer install
mv .env.example .env
php artisan key:generaate
php artisan jwt:secret
php artisan migrate
