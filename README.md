# GLPI Plugin Directory

[![Join the chat at https://gitter.im/glpi-project/plugins](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/glpi-project/plugins?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This is the server side and webapp of GLPI Plugin Directory.  

It went on production mode on Teclib servers since GLPi project is managed by the company,
see http://plugins.glpi-project.org .
if you want to collaborate, don't hesitate to fork this project, there is a lot
you might want to do.

## Installation

### First

clone this repository where you are able to,
and serve it via PHP and a local webserver 
(Apache currently has a better support).

```bash
cd somewhere
git clone git@github.com:glpi-project/plugins.git glpi-plugin-directory
```

### Then, Fetch PHP Components used by server side

in the folder where you cloned this repo, run

```bash
cd api
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
which you grant CRUD and CREATE rights on the database.

```bash
mysql -u <usercreated> -p<password> <database>
```

```sql
CREATE DATABASE <database> CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL PRIVILEGES ON glpiplugindirectory.* TO '<usercreated>'@'localhost' IDENTIFIED BY '<password>'
```

in the folder where you cloned this repo, run

```bash
mysql -u <usercreated> -p<password> <database> < misc/structure.sql
```

be sure to replace &lt;usercreated&gt; &lt;password&gt; and &lt;database&gt;
with the database and user you created previously.

## Create config files

You must create `api/config.php` file and `frontend/app/scripts/conf.js`  
Both of them have example provided that you can use to start a new one.

 + `api/config.example.php`
 + `frontend/app/scripts/conf.example.js`

## Loading Indepnet data from CSV file (Optional)

This is the original list of XML Plugin declaration
files Indepnet was tracking, it is the the data on which
we built this new plugin directory.
We added a way to copy that original list into a fresh
database, described here

in the folder where you cloned this repo, run

```bash
php misc/loadcsv.php -h hostname -d database -u username -p password -f misc/indepnet.csv
```

you can give the indepnet.csv file provided in misc
with the -f command line option shown in the example before.

## Run the BackgroundTasks

### Manually 

```bash
php misc/run_tasks.php
```

will run all the normal tasks
 + updating all the plugins if possible
 + deleting expired access tokens
 + deleting lonely refresh tokens
 + deleting lonely sessions

```bash
php misc/run_tasks.php -k genericobject -t update
```

will run the update of the genericobject plugin.

Here, the -k and -t options helps when you want
per example to update a specific plugin
from the command line.

### With crontab

It is up to you to use a crontab entry to run this script,
per example once every hour.
Specify no arguments to the run_tasks commands in
the crontab, this way all the normal tasks will
run.

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

## Start to develop

If you wanted to serve glpi-plugin-directory on a server, and simply
want to use the functionality provided by it, the previous configuration
works all fine, but due to cross-domain related issues, a developer
currently needs to use the node-js/yeoman/grunt based development server,
BUT, he also need to serve it proxied by the Apache instance, this way,
the webapp and api are served on the same /etc/hosts domain (which is
what a web developer might use to handle multiple local projects).

There is the new configuration for developers

```apache
<VirtualHost *:80>
    ServerName glpiplugindirectory;
    ProxyRequests off
    ProxyPass /app/ http://127.0.0.1:9000/
    ProxyPassReverse /app/ http://127.0.0.1:9000/

     <Proxy *>
         Order deny,allow
         Allow from all
     </Proxy>

    <Directory "/Users/me/Code/glpi-plugin-directory">
       Options FollowSymLinks
       AllowOverride None
       Require all granted
    </Directory>

    # This force the Authorization header to be passed to PHP
    SetEnvIf Authorization "(.+)" HTTP_AUTHORIZATION=$1

    <Location /api>
       Header add Access-Control-Allow-Origin "*"
       Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type, x-lang, x-range, authorization"
       Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"
       Header add Access-Control-Expose-Headers "content-type, content-Range, accept-range"
       RewriteEngine On
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [QSA,L]
    </Location>
    Alias /api "/Users/me/Code/glpi-plugin-directory/api"

    ErrorLog "/usr/local/var/log/apache2/glpiplugindirectory.error.log"
    CustomLog "/usr/local/var/log/apache2/glpiplugindirectory.access.log" common
</VirtualHost>
```

As the web app, in this case, is served through the development server,
the developer might also need to run it ...

```bash
cd frontend
grunt serve
```

Everything should turn fine.

NOTE: The original Apache configuration might be outdated,
this one is up-to-date, what changed is especially the 
SetEnvIf for Authorization header, and the Access-Control-
directives that are used.

open up http://glpiplugindirectory/app/ (trailing slash !) in your web browser
