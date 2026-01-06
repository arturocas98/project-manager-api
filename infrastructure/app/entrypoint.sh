#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-app}

if [ "$role" = "app" ]; then
    exec /usr/local/sbin/php-fpm -F
elif [ "$role" = "worker" ]; then
    exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
elif [ "$role" = "scheduler" ]; then
    while [ true ]
    do
        php /var/www/html/artisan schedule:run --verbose --no-interaction &
        sleep 1
    done
else
    echo "Could not match the container role \"$role\""
    exit 1
fi
