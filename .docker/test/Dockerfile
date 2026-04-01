FROM php:7.4-cli-bullseye

RUN apt-get update -q \
 && apt-get install -y -q --no-install-recommends \
      git unzip libssl-dev libcurl4-openssl-dev libzip-dev \
 && docker-php-ext-install pdo pdo_mysql \
 && docker-php-ext-configure curl \
 && docker-php-ext-install curl \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
