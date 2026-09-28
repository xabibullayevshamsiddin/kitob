#!/bin/sh
set -e

# Render provides the $PORT environment variable (usually 10000). Default to 80 if unset.
PORT="${PORT:-80}"
echo "==> Configuring Nginx port to ${PORT}..."
sed -i "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/nginx.conf

# Ensure essential storage directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public

# Fix storage and cache permissions for www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if not already created
if [ ! -L /var/www/html/public/storage ]; then
    echo "==> Linking storage..."
    php artisan storage:link || true
fi

# Run database migrations if RUN_MIGRATIONS is set to true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "==> Running database migrations..."
    php artisan migrate --force || echo "Migration encountered an issue, proceeding with startup..."
fi

# Optimize Laravel caching if in production
if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "prod" ]; then
    echo "==> Optimizing configurations, routes, and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "==> Starting Nginx and PHP-FPM via Supervisord..."
exec supervisord -c /etc/supervisord.conf
