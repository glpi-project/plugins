# GLPI Plugin Directory

[![Join the chat at https://gitter.im/glpi-project/plugins](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/glpi-project/plugins?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

## Have a quick preview of glpi-plugin-directory with Vagrant

If you want to type the-less-possible-set  
of commands, let's go with Vagrant :

```bash
apt-get install vagrant virtualbox
```

followed by

```bash
git clone git@github.com:glpi-project/plugins.git glpi-plugin-directory
cd glpi-plugin-directory
vagrant up
```

If everything went fine, you're done, go visit  
http://localhost:8080
you should see a running local-copy, or a local running-copy of glpi-plugin-directory  
on your machine.


## Install on a machine

### First

clone this repository where you are able to serve it via
PHP and a webserver.

```bash
cd somewhere
git clone git@github.com:glpi-project/plugins.git glpi-plugin-directory
```

### Then, Fetch PHP Components used by server side

in the folder where you cloned this repo, run

```bash
cd glpiapi
composer install
```

### Fetch frontend libraries / Build web application

in the folder where you cloned this repo, run

```bash
cd frontend
npm install
bower install
grunt build
```

## Create MySQL database

First, create a MySQL database, and user,
which you grant rights on the database.

in the folder where you cloned this repo, run

```bash
mysql -u <usercreated> -p<password> <database> < misc/structure.sql
```
be sure to replace &gt;usercreated&lt; &gt;password&lt; and &gt;databasecreated&lt;
with the database and user you created previously.

## Create config files

You must create api/config.php file and frontend/app/scripts/conf.js
Both of them have example provided that you can use to start a new one.

 + api/config.example.php
 + frontend/app/scripts/conf.example.js

## Loading Indepnet data from CSV file (Optional)

```bash
php misc/loadcsv.php -h hostname -d database -u username -p password -f csv_path
```

you can give the indepnet.csv file provided in misc
with the -f command line option shown in the example before.

## Grab plugin information 

### Manually 

```bash
php misc/update.php
```

### With crontab

It is up to you to use a crontab entry to run this script,
per example once every hour.

## Configuration (Via Apache HTTPd)

This is a configuration example for Apache HTTPd :

```apache
<VirtualHost *:80>
    ServerName glpiplugindirectory
    DocumentRoot "/path/to/frontend/dist"

    <Directory "/path/to/glpi-plugin-directory">
       Options FollowSymLinks
       AllowOverride None
       Require all granted
    </Directory>

    <Location /api>
       RewriteEngine On

       # Cross domain access (you need apache header mod : a2enmod headers)
       Header add Access-Control-Allow-Origin "*"
       Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type, x-lang, x-range, accept"
       Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"
       Header add Access-Control-Expose-Headers "content-type, content-range, accept-range"

       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [QSA,L]
    </Location>
    Alias /api "/path/to/api"

    ErrorLog "/usr/local/var/log/apache2/glpiplugindirectory.error.log"
    CustomLog "/usr/local/var/log/apache2/glpiplugindirectory.access.log" common
</VirtualHost>
```
## Configuration (Via NGINX)

This is a configuration example for NGINX :

```nginx
upstream php {
    server unix:/var/run/php-fpm/php-fpm.sock;
}

server {
    listen 80;
    server_name glpiplugindirectory;

    root /path/to/glpi-plugin-directory/frontend/dist;
    index index.html;
}

server {
    listen 80;
    server_name api.glpiplugindirectory;

    root /path/to/glpi-plugin-directory/api;

    add_header Access-Control-Allow-Origin "*";
    add_header Access-Control-Allow-Headers "origin, x-requested-with, content-type, x-lang, x-range, accept";
    add_header Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS";
    add_header Access-Control-Expose-Headers "content-type, content-range, accept-range";

    try_files $uri $uri/ /index.php?$args;
    index index.php;

    location ~ \.php$ {
             #NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini
             include fastcgi.conf;
             fastcgi_intercept_errors on;
             fastcgi_pass php;
    }
}

```

## Start a built-in development server in javascript

This is if you develop locally on the frontend side of glpi-plugin-directory

```bash
cd frontend
grunt serve
```