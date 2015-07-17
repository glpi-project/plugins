# GLPI Plugin Directory

This projects aims to serve the new GLPI plugin directory,
for GLPI versions < 1 .

This project if fully managed by, and used only by Teclib'
and won't be shared on Teclib's github (or I don't think so).

## Configuration (Mandatory)

 + Create a database that you would like to use
 + Load tables.sql

## Configuration (Via NGINX)

This is a configuration example for nginx :

```nginx
upstream php-fpm {
    server unix:/var/run/php5-fpm.sock;
}

server {
    listen 80;
    server_name glpiplugindirectory;
    root /home/nelson/Code/glpi-plugin-directory/frontend/app;
   
    location /api {
        alias /home/nelson/Code/glpi-plugin-directory/api;
        try_files $uri /index.php?$args;
        fastcgi_intercept_errors on;
        include fastcgi_params;
        fastcgi_param SERVER_NAME $host;
        fastcgi_param SCRIPT_FILENAME /home/nelson/Code/glpi-plugin-directory/api/index.php;
        fastcgi_pass php-fpm;

        #location ~ ^/(.+\.php)$ {
	#    try_files $uri $uri/ /index.php?$args;
        #    #try_files /index.php?$args;
        #    fastcgi_intercept_errors on;
        #    include        fastcgi_params;
        #    fastcgi_param  SERVER_NAME      $host;
        #    fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        #    fastcgi_pass   php-fpm;
        #}
    }
}
```

## Configuration (Via Apache httpd)

