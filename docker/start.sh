#!/usr/bin/env bash

set -e

echo "ROLE $CONTAINER_ROLE"

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

if [ ! -z "$WWWUSER" ]; then
    usermod -u $WWWUSER sail
fi

if [ ! -d /.composer ]; then
    mkdir /.composer
fi

chmod -R ugo+rw /.composer


echo "Caching configuration..."
php artisan config:cache

if [ "$role" = "app" ]; then 
    echo "It's an app!"
    # exec apache2-foreground
    if [ $# -gt 0 ];then
        exec gosu $WWWUSER "$@"
    else
        /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
    fi
elif [ "$role" = "queue" ]; then
    echo "Running the queue..."
    php /var/www/html/artisan queue:work --verbose --sleep=0.1
elif [ "$role" = "scheduler" ]; then
    while [ true ]
    do
      php /var/www/html/artisan schedule:run --verbose --no-interaction &
      sleep 60
    done
elif [ "$role" = "reverb" ]; then
    echo "Running the reverb..."
    php /var/www/html/artisan reverb:start
else
    echo "Could not match the container role \"$role\""
    exit 1
fi
