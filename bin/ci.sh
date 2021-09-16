#!/usr/bin/env bash

# Generate Key
php artisan key:generate

# Migrate DB
php artisan migrate:fresh

# Directory Permissions
php artisan storage:link
chmod -R 777 ./storage
chmod -R 777 ./bootstrap/cache
php artisan config:cache
chmod -R 0755 ./vendor/laravel/dusk/bin




