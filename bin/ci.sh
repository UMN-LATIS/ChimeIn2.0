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

# Upgrade Chrome Driver
# php artisan dusk:chrome-driver `/opt/google/chrome/chrome --version | cut -d " " -f3 | cut -d "." -f1`

# Start Chrome Driver
# ./vendor/laravel/dusk/bin/chromedriver-linux &



