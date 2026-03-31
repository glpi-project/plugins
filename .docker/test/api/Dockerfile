FROM php:7.4-apache-bullseye

RUN apt-get update -q \
 && apt-get install -y -q --no-install-recommends \
      libssl-dev libcurl4-openssl-dev wget \
 && docker-php-ext-configure curl \
 && docker-php-ext-install pdo pdo_mysql curl \
 && a2enmod headers setenvif \
 && rm -rf /var/lib/apt/lists/*

COPY .docker/test/api/apache.conf /etc/apache2/sites-available/000-default.conf
