# GLPI Plugin Directory

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

This project if fully managed by, and used only by Teclib'
and won't be shared on Teclib's github (or I don't think so).

## Demo

If you're connected in a Teclib agency
or on the Teclib VPN, you can see a demo
of the current bleeding edge:

http://172.28.211.125/


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

You will also need this Chrome plugin
[Allow-Control-Allow-Origin: *](https://chrome.google.com/webstore/detail/allow-control-allow-origi/nlfbmbojpeacfghkpbjhddihlkkiljbi?hl=en)
To allow the client side working on a different
URL from the API, because you're firing the
generator-angular's built-in server to work
with the API serve by your Apache instance.

Or, you can simply fire Chrome with --disable-web-security
flag which will allow XmlHttpRequests on cross-domain urls.

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
    ServerName glpiplugindirectory;
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
       Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type"
       Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"

       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [QSA,L]
    </Location>
    Alias /api "/path/to/api"

    ErrorLog "/usr/local/var/log/apache2/glpiplugindirectory.error.log"
    CustomLog "/usr/local/var/log/apache2/glpiplugindirectory.access.log" common
</VirtualHost>
```