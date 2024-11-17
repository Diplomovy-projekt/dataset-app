#!/bin/bash

# Exit on error
set -e

# Run Laravel database migrations (optional, comment out if not needed)
# echo "Running Laravel migrations..."
# php artisan migrate --force

# Clear and cache configurations
# echo "Caching Laravel configurations..."
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
# Keep container running
exec "$@"
