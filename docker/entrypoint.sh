#!/bin/sh
set -e

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations and seed the database if it's new
php artisan migrate --force

# Seed database if instructed
if [ "$APP_SEED" = "true" ]; then
    php artisan db:seed --force
fi

# Fix permissions for SQLite database and storage so www-data can access them
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

# Start supervisord to run Nginx and PHP-FPM
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
