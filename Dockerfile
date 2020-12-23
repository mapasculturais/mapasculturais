FROM php:7.2-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl libcurl4-gnutls-dev locales imagemagick libmagickcore-dev libmagickwand-dev zip \
        ruby ruby-dev libpq-dev gnupg nano iputils-ping git \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev less vim

RUN curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -y nodejs npm

RUN rm -rf /var/lib/apt/lists

# Install uglify and terser
RUN npm install -g \
        terser \
        uglifycss \
        autoprefixer

# Install sass
RUN gem install sass -v 3.4.22

# Install extensions
RUN docker-php-ext-install opcache pdo_pgsql zip xml curl json 

# Install GD
RUN docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Install APCu
RUN pecl install apcu \
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# Install imagick
RUN pecl install imagick-beta \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini

# Install redis
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --version=1.10.16 --install-dir=/usr/local/bin && \
    rm composer-setup.php

# Copy source
COPY src/index.php /var/www/html/index.php
COPY --chown=www-data:www-data src/protected /var/www/html/protected


RUN mkdir -p /var/www/html/protected/vendor /var/www/html/protected/DoctrineProxies /var/www/.composer && \
    chown -R www-data:www-data /var/www/html/protected/vendor /var/www/html/protected/DoctrineProxies /var/www/.composer

RUN ln -s /var/www/html/protected/application/lib/postgis-restful-web-service-framework /var/www/html/geojson

WORKDIR /var/www/html/protected
RUN composer.phar install

WORKDIR /var/www/html/protected/application/themes/

RUN find . -maxdepth 1 -mindepth 1 -exec echo "compilando sass do tema " {} \; -exec sass {}/assets/css/sass/main.scss {}/assets/css/main.css -E "UTF-8" \;

COPY scripts /var/www/scripts
COPY compose/production/php.ini /usr/local/etc/php/php.ini
COPY compose/config.php /var/www/html/protected/application/conf/config.php
COPY compose/config.d /var/www/html/protected/application/conf/config.d

RUN ln -s /var/www/html /var/www/src

COPY version.txt /var/www/version.txt

COPY compose/recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh
COPY compose/entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

WORKDIR /var/www/html/
EXPOSE 9000

CMD ["php-fpm"]
