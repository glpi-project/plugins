# GLPI Plugin Directory

[![Join the chat at https://gitter.im/glpi-project/plugins](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/glpi-project/plugins?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

## Demo / Vagrantfile

If you want to type the-less-possible-set  
of commands, let's go with Vagrant :

```bash
apt-get install vagrant virtualbox
```

followed by

```bash
cd glpi-plugin-directory
vagrant up
```

If everything went fine, you're done, go visit  
http://localhost:8080
you should see a running local-copy, or a local running-copy of glpi-plugin-directory  
on your machine.


## Dependencies installation

### Server-Side PHP packages fetching

```bash
cd api
composer install
```

### Client-Side Angular modules fetching

```bash
cd frontend
npm install
bower install
grunt build
```

### Client-Side Developers

```bash
cd frontend
grunt serve
```

## Configuration (Mandatory)

 + Create a database that you would like to use
 + Load structure.sql

## Loading Indepnet data from CSV file (Optional)

```bash
php misc/loadcsv.php -h hostname -d database -u username -p password -f csv_path
```

you can give the indepnet.csv file provided in misc
with the -f command line option shown in the example before.

## Parse plugins xml 

You must have a api/config.php file (see api/config.example.php)

```bash
php misc/update.php
```


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
