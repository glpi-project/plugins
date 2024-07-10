FROM composer:1 AS composer

FROM php:7.4-apache-bullseye

# Workaround to make apache use same UID/GID as host user.
ARG HOST_GROUP_ID=1000
RUN groupmod --gid ${HOST_GROUP_ID} www-data
ARG HOST_USER_ID=1000
RUN usermod --uid ${HOST_USER_ID} www-data

# Make www-data user home persistent for cache purpose.
RUN mkdir --parents /home/www-data \
  && chown www-data:www-data /home/www-data \
  && usermod --home /home/www-data www-data
VOLUME /home/www-data

RUN apt-get update \
  \
  # Enable required apache mods
  && a2enmod headers proxy proxy_http rewrite \
  \
  # Install PDO MySQL PHP extension.
  && docker-php-ext-install pdo pdo_mysql \
  \
  # Install nodejs, npm and build utils.
  && mkdir /opt/nodejs && curl --silent --location https://nodejs.org/download/release/v10.24.1/node-v10.24.1-linux-x64.tar.gz | tar --extract --gzip --strip-components=1 --verbose --directory=/opt/nodejs \
  && ln --symbolic /opt/nodejs/bin/node /usr/bin/node \
  && ln --symbolic /opt/nodejs/bin/npm /usr/bin/npm \
  && npm install -g bower grunt \
  && apt-get install --assume-yes --no-install-recommends --quiet ruby-dev \
  && gem install compass \
  && apt-get install --assume-yes --no-install-recommends --quiet libfontconfig1 libfreetype6 \
  \
  # Install openssl required for PhantomJS.
  && apt-get install --assume-yes --no-install-recommends --quiet openssl \
  \
  # Install git and zip used by composer when fetching dependencies.
  && apt-get install --assume-yes --no-install-recommends --quiet git unzip \
  \
  # Clean sources list
  && rm -rf /var/lib/apt/lists/*

# Add global node installed libs to path
ENV PATH="/opt/nodejs/bin/:${PATH}"

# Required for PhantomJS.
ENV OPENSSL_CONF=/etc/ssl/

# Copy composer binary
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy files to container.
COPY ./files/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

USER www-data
WORKDIR /var/www/plugins.glpi-project.org
