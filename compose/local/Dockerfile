FROM php:7.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl libcurl4-gnutls-dev locales imagemagick libmagickcore-dev libmagickwand-dev zip \
        ruby ruby-dev libpq-dev gnupg nano iputils-ping

RUN curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -y nodejs

RUN npm install -g \
        uglify-js@2.2.0 \
        uglifycss \
        autoprefixer

RUN gem install sass -v 3.4.22

# Install extensions
RUN docker-php-ext-install opcache pdo_pgsql gd zip xml curl json

# Install APCu
RUN pecl install apcu \
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# Install imagick
RUN pecl install imagick-beta \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini

# Copy source
COPY src/index.php /var/www/html/index.php
COPY src/protected /var/www/html/protected
RUN ln -s /var/www/html/protected/application/lib/postgis-restful-web-service-framework /var/www/html/geojson

RUN chown -R www-data:www-data /var/www/html/protected/vendor

RUN mkdir /var/www/.composer && chown www-data:www-data /var/www/.composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer.phar

RUN apt-get install -y git

USER www-data
WORKDIR /var/www/html/protected


RUN composer.phar install

USER root
WORKDIR /var/www/html/protected/application/themes/

RUN find . -maxdepth 1 -mindepth 1 -exec echo "compilando sass do tema " {} \; -exec sass {}/assets/css/sass/main.scss {}/assets/css/main.css -E "UTF-8" \;

WORKDIR /var/www/html/

COPY scripts /var/www/scripts

COPY compose/local/php.ini /usr/local/etc/php/php.ini
COPY compose/config.php /var/www/html/protected/application/conf/config.php
COPY compose/config.d /var/www/html/protected/application/conf/config.d

COPY compose/entrypoint.sh /entrypoint.sh

RUN ln -s /var/www/html /var/www/src

COPY src/router.php /var/www/html/router.php

ENTRYPOINT ["/entrypoint.sh"]

RUN echo "php -S 0.0.0.0:8001 -t /var/www/html /var/www/html/router.php" > /mapas.sh && chmod +x /mapas.sh

EXPOSE 80

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

CMD php -S 0.0.0.0:80 -t /var/www/html /var/www/html/router.php
