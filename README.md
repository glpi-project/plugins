# GLPI Plugin Directory

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

This project if fully managed by, and used only by Teclib'
and won't be shared on Teclib's github (or I don't think so).

## Dependencies installation

### Server-Side : PHP
dans glpi-plugin-directory :
```
cd api
compoer install
```

### Client-Side : Angular
dans glpi-plugin-directory :
```
cd frontend
npm install
bower install
grunt build
```

## Configuration (Mandatory)

 + Create a database that you would like to use
 + Load structure.sql

## Loading Indepnet data from CSV file (Optional)

```
php misc/loadcsv.php -h hostname -d database -u username -p password -f csv_path
```

## Configuration (Via Apache HTTPd)

This is a configuration example for Apache HTTPd :

```
<VirtualHost *:80>
    ServerName glpiplugindirectory;
    DocumentRoot "/Users/nelson/Code/glpi-plugin-directory/frontend/dist"

    <Directory "/Users/nelson/Code/glpi-plugin-directory">
       Options FollowSymLinks
       AllowOverride None
       Require all granted
    </Directory>

    <Location /api>
       RewriteEngine On
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [QSA,L]
    </Location>
    Alias /api "/Users/nelson/Code/glpi-plugin-directory/api"

    ErrorLog "/usr/local/var/log/apache2/glpiplugindirectory.error.log"
    CustomLog "/usr/local/var/log/apache2/glpiplugindirectory.access.log" common
</VirtualHost>
```