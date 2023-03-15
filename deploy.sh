# Install Composer packages
composer install --no-dev --prefer-dist --optimize-autoloader

# Clear cache
php bin/console cache:clear --env=prod --no-debug

# Start the web server
php -S 0.0.0.0:$PORT -t public/
