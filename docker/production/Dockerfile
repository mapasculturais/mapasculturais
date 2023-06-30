FROM php:8.2-cli

RUN apt-get update

RUN apt-get update && apt-get install -y --no-install-recommends \
        locales less vim nano micro sudo git zip \
        imagemagick libmagickcore-dev libmagickwand-dev \
        curl iputils-ping gnupg \
        libcurl4-gnutls-dev libpq-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libzstd-dev

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

RUN npm install -g pnpm
RUN rm -rf /var/lib/apt/lists

ENV PNPM_HOME=/root/.local/share/pnpm
ENV PATH=$PATH:/root/.local/share/pnpm

# Install uglify and terser
RUN npm install -g \
        terser \
        uglifycss \
        autoprefixer \
        postcss \
        sass

# Install extensions
RUN docker-php-ext-install -j$(nproc) opcache pdo_pgsql  zip  xml  curl

# Install GD
RUN docker-php-ext-install -j$(nproc) iconv && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd

# Install APCu
RUN no | pecl install apcu \
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# Install imagick
RUN autodetect | pecl install imagick \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini

# Install xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=develop" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install igbinary
RUN pecl install igbinary \
    && docker-php-ext-enable igbinary

# Install redis
RUN yes | pecl install -o -f redis \
    && docker-php-ext-enable redis

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    rm composer-setup.php

# Copy source
COPY composer.json /var/www/composer.json
COPY composer.lock /var/www/composer.lock

WORKDIR /var/www/
RUN composer.phar install

COPY config /var/www/config
COPY public /var/www/html
COPY scripts /var/www/scripts
COPY src /var/www/src
COPY var /var/www/var

RUN chown www-data:www-data -R /var/www

COPY docker/recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh
COPY docker/jobs-cron.sh /jobs-cron.sh
COPY docker/entrypoint.sh /entrypoint.sh

COPY version.txt /var/www/version.txt

COPY docker/production/php.ini /usr/local/etc/php/php.ini

RUN ln -s /var/www/html /var/www/public

ENTRYPOINT ["/entrypoint.sh"]

WORKDIR /var/www/html/
EXPOSE 9000

CMD ["php-fpm"]
