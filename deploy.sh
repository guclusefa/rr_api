# Install Composer packages
composer install --no-dev --prefer-dist --optimize-autoloader

# Clear cache
php bin/console cache:clear --env=prod --no-debug

# Install Apache web server
apt-get update
apt-get install -y apache2

# Set up Apache virtual host
cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerName cesi-rr.azurewebsites.net
    DocumentRoot /home/site/wwwroot/public
    <Directory /home/site/wwwroot/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

# Restart Apache web server
service apache2 restart
