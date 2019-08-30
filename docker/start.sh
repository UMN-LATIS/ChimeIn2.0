#!/usr/bin/env bash

set -e

echo "ROLE $CONTAINER_ROLE"

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

# if [ "$env" != "local" ]; then
    echo "Caching configuration..."
    (cd /var/www/html && php artisan config:cache)
# fi

if [ "$role" = "app" ]; then 
    echo "It's an app!"
    exec apache2-foreground

elif [ "$role" = "queue" ]; then

    echo "Running the queue..."
    php /var/www/html/artisan queue:work --verbose --sleep=0.1


elif [ "$role" = "scheduler" ]; then

    while [ true ]
    do
      php /var/www/html/artisan schedule:run --verbose --no-interaction &
      sleep 60
    done

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
