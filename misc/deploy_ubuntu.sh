# Note: This script is supposed to be used with Vagrant
#  cd ../
#  vagrant up
#
# You can read on the Vagrantfile in ../

echo "Installing Apache 2, MySQL, PHP5, and other dependencies..."

# Updating packages
apt-get update

curl --silent --location https://deb.nodesource.com/setup_0.12 | bash -

# Preparing MySQL root password to be toor
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password toor'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password toor'

# Installing server-side dependencies
# like PHP and everything
apt-get install --yes \
   apache2 \
   mysql-server \
   libapache2-mod-php5 \
   php5-cli \
   php5-mysql \
   nodejs \
   ruby-dev \
   curl \
   git

# Installing Composer (PHP Package Manager)
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename composer \

# Installing npm apps needed by the project
npm install -g grunt-cli yo generator-angular bower

# Installing the Compass CSS authoring framework
gem install compass

# Installing PHP dependencies via composer
cd /vagrant/api
su vagrant -c 'composer install'

# Installing frontend devtools/HTML5 dependencies
# of the app with npm, bower, ...
cd /vagrant/frontend
su vagrant -c 'npm install'
su vagrant -c 'bower -f install' 

# Building the frontend
su vagrant -c 'grunt build'

# Preparing the VirtualHost in it's proper location
cat > /etc/apache2/sites-available/glpiplugindirectory.conf <<EOL
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "/vagrant/frontend/dist"

    <Directory "/vagrant">
       Options FollowSymLinks
       AllowOverride None
       Require all granted
    </Directory>

    <Location /api>
       RewriteEngine On

       # Cross domain access (you need apache header mod : a2enmod headers)
       Header add Access-Control-Allow-Origin "*"
       Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type"
       Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"

       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [QSA,L]
    </Location>
    Alias /api "/vagrant/api"

    ErrorLog "/var/log/apache2/glpiplugindirectory.error.log"
    CustomLog "/var/log/apache2/glpiplugindirectory.access.log" common
</VirtualHost>
EOL

# Copying glpi-plugin-directory backend config file
su vagrant -c 'cat' > /vagrant/api/config.php <<EOL
<?php

$db_settings = [
	'driver'    =>  'mysql',
	'host'      =>  'localhost',
	'database'  =>  'glpiplugindirectory',
	'username'  =>  'glpi',
	'password'  =>  'glpi',
	'charset'   =>  'utf8',
	'collation' =>  'utf8_general_ci',
	'prefix'    =>  ''
];

$log_queries = false;

$recaptcha_secret = '6LcnrwoTAAAAAEARsd1XMadhLthIibXeNZf4EeUZ';

$msg_alerts = [
	"recipients" => [
		"Walid Nouh <wnouh@teclib.com>",
		"Alexandre Delaunay <adelaunay@teclib.com>",
		"Nelson Zamith <nzamith@teclib.com>"
	],
	"subject_prefix" => "[GLPI PLUGINS : MSG] "
];
EOL

# Copying glpi-plugin-directory frontend config file
su vagrant -c 'cat' > /vagrant/frontend/scripts/conf.js <<EOL
var API_URL = 'http://localhost:8080/api';
var RECAPTCHA_PUBLIC_KEY = '6LcnrwoTAAAAAHfjzXrBWOBMkbHY2QuZJtER7Y6M';
EOL

# Creating database and giving rights
echo "Creating database"
mysql -u root -ptoor <<EOL
CREATE DATABASE glpiplugindirectory
CHARACTER SET utf8
COLLATE utf8_general_ci;

GRANT ALL PRIVILEGES ON glpiplugindirectory.*
TO 'glpi'@'localhost' IDENTIFIED BY
'glpi';
EOL

# Loading MySQL schemas
echo "Creating tables in database..."
mysql -u glpi -pglpi glpiplugindirectory < /vagrant/misc/structure.sql

# Landing Original Indepnet data
echo "Installing original Indepnet plugin list and xml urls.."
cd /vagrant
sudo vagrant -c 'php misc/loadcsv.php -h localhost -u glpi -p glpi -d glpiplugindirectory -f misc/indepnet.csv'

# Doing a first fetch of all plugin information
echo "Updating every plugin information via HTTP"
sudo vagrant -c 'php misc/update.php'

# Making the virtualhost available to Apache
a2ensite glpiplugindirectory

# Disabling the default Apache virtualhost
a2dissite 000-default

# Enabling the required Apache modules
a2enmod headers
a2enmod rewrite

# Setting vagrant/vagrant as user/group for Apache
sed -i "s/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=vagrant/g" /etc/apache2/envvars
sed -i "s/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=vagrant/g" /etc/apache2/envvars

# Restarting Apache
service apache2 restart