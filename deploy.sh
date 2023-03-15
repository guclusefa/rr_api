#!/bin/bash

# Checkout to production branch
git checkout prod

# Change to the web root directory
cd $HOME/site/wwwroot

# Clone the repository
git clone https://github.com/guclusefa/rr_api.git rr_api

# Install dependencies
cd rr_api
composer install --no-dev --optimize-autoloader

# Clear the cache
php bin/console cache:clear --env=prod --no-debug

# Set the permissions
chmod -R 777 var/

# Restart the web server
touch $HOME/site/wwwroot/web/index.php