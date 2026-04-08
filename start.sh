#!/bin/bash

# Set correct permissions for storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations
php artisan migrate --force

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in foreground
apache2-foreground
