#!/bin/sh
set -e

echo "Deploying application ..."

# Run app artisan commands
php artisan app:install

# Restart queues.
supervisorctl restart all

# Delete temporary artifacts
rm -rf /tmp/artifacts

echo "Application deployed!"
