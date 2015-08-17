# GLPI Plugin Directory

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

This project is fully managed by, and used only by Teclib'
and won't be shared on Teclib's github (or I don't think so).

## Demo / Vagrantfile

If you want to type the-less-possible-set  
of commands, let's go with Vagrant :

```bash
apt-get install vagrant virtualbox
```

followed by

```
cd glpi-plugin-directory
vagrant up
```

If everything went fine, you're done, go visit  
http://localhost:8080
you should see a running local-copy, or a local running-copy of glpi-plugin-directory  
on your machine.


## Dependencies installation

### Server-Side PHP packages fetching
dans glpi-plugin-directory :
```
cd api
composer install
```

### Client-Side Angular modules fetching
dans glpi-plugin-directory :
```
cd frontend
npm install
bower install
grunt build
```

### Client-Side Developers

```
cd frontend
grunt serve
```

## Configuration (Mandatory)

 + Create a database that you would like to use
 + Load structure.sql

## Loading Indepnet data from CSV file (Optional)

```
php misc/loadcsv.php -h hostname -d database -u username -p password -f csv_path
```

you can give the indepnet.csv file provided in misc
with the -f command line option shown in the example before.

## Parse plugins xml 

You must have a api/config.php file (see api/config.example.php)

```
php misc/update.php
```


## Configuration (Via Apache HTTPd)

This is a configuration example for Apache HTTPd :

```
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
       Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type, x-lang, x-range"
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
