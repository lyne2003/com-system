#!/bin/bash

# Set correct permissions for storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations
php artisan migrate --force

# Clear any cached config so env vars from Render are used fresh
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Start Apache in foreground
apache2-foreground
