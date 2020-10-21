FROM php:7.2-fpm-stretch

RUN unlink /etc/localtime

RUN ln -s /usr/share/zoneinfo/America/Fortaleza /etc/localtime

RUN apt-get update  && apt-get install -y --no-install-recommends && rm -rf /var/lib/apt/lists/* \
        curl libcurl4-gnutls-dev locales imagemagick libmagickcore-dev libmagickwand-dev zip libxml2-dev\
        ruby ruby-dev libpq-dev gnupg nano iputils-ping git \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev

RUN curl -sL https://deb.nodesource.com/setup_10.x | bash
RUN apt-get update && apt-get install -y nodejs && apt-get clean
RUN npm install npm --global

# Install uglify
RUN npm install -g \
        uglify-js@2.2.0 \
        uglifycss \
        terser \
        autoprefixer

# Install sass
RUN gem install sass -v 3.4.22

# Install extensions
RUN docker-php-ext-install opcache pdo_pgsql xml curl json

RUN apt-get update && apt-get install -y --fix-missing \
    zlib1g-dev \
    libzip-dev
RUN docker-php-ext-install zip

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

# Install xdebug
RUN pecl install xdebug \
    docker-php-ext-enable xdebug 

# Copy xdebug configration
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear package lists
RUN rm -rf /var/lib/apt/lists/*

# Copy php.ini configuration
COPY ./php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Permissions
RUN chown -R root:www-data /var/www/html
RUN chmod u+rwx,g+rx,o+rx /var/www/html
RUN find /var/www/html -type d -exec chmod u+rwx,g+rx,o+rx {} +
RUN find /var/www/html -type f -exec chmod u+rw,g+rw,o+r {} +
RUN mkdir /var/log/nginx/
RUN chown -R root:www-data /var/log/nginx/
RUN chmod -R 777 /var/log/nginx/

COPY ./recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh
COPY ./entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

CMD ["php-fpm"]

EXPOSE 9000
