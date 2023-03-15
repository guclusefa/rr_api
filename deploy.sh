# Install Composer packages
composer install --no-dev --prefer-dist --optimize-autoloader

# Clear cache
php bin/console cache:clear --env=prod --no-debug

# start symfony server
php bin/console server:start
