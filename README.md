# GLPI Plugin Directory

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

This project if fully managed by, and used only by Teclib'
and won't be shared on Teclib's github (or I don't think so).

## 

## Configuration (Mandatory)

 + Create a database that you would like to use
 + Load tables.sql

## Configuration (Via Apache HTTPd)

This is a configuration example for Apache HTTPd :

```
>>>>>>> Markdown block in ApacheConf format
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
