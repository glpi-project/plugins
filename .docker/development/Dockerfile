FROM composer:latest AS composer

FROM php:7.4-apache

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
  # Install cron service.
  && apt-get install --assume-yes --no-install-recommends --quiet cron \
  \
  # Install nodejs, npm and build utils.
  && apt-get install --assume-yes --no-install-recommends --quiet gnupg \
  && curl --silent --location https://deb.nodesource.com/setup_10.x | bash - \
  && apt-get install --assume-yes --no-install-recommends --quiet nodejs \
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

# Required for PhantomJS.
ENV OPENSSL_CONF=/etc/ssl/

# Copy composer binary
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy files to container.
COPY ./files/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./files/opt/startup.sh /opt/startup.sh
COPY ./files/etc/cron.d/plugins.glpi-project.org /etc/cron.d/plugins.glpi-project.org

# Install crontab.
RUN crontab -u www-data /etc/cron.d/plugins.glpi-project.org

# Define application path as volume and working dir
VOLUME /var/www/plugins.glpi-project.org
WORKDIR /var/www/plugins.glpi-project.org

# Make startup script executable and executes it as default command.
RUN chmod u+x /opt/startup.sh
CMD /opt/startup.sh
