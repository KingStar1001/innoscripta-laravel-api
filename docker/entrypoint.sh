#!/usr/bin/env sh

composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate
