FROM php:8.2-fpm

RUN apt-get update

RUN apt-get update && apt-get install -y --no-install-recommends \
        locales less vim nano micro sudo git zip procps \
        imagemagick libmagickcore-dev libmagickwand-dev \
        curl iputils-ping ca-certificates  gnupg \
        libcurl4-gnutls-dev libpq-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libzstd-dev


RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | sudo tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update && apt-get install -y nodejs

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

# Install redis
RUN no | pecl install -o -f redis \
    && docker-php-ext-enable redis

# Install opcache
RUN docker-php-ext-install opcache

# Install xdebug
# não habilita o xdebug por padrão
RUN yes | pecl install xdebug \
    && ln -s $(find /usr/local/lib/php/extensions/ -name xdebug.so) /usr/local/lib/php/extensions/xdebug.so

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    rm composer-setup.php

# Copy source
COPY composer.json /var/www/composer.json
COPY composer.lock /var/www/composer.lock

WORKDIR /var/www/

ENV COMPOSER_ALLOW_SUPERUSER=1 
RUN composer.phar install

COPY config /var/www/config
COPY public /var/www/html
COPY scripts /var/www/scripts
COPY src /var/www/src
COPY var /var/www/var

WORKDIR /var/www/src/
RUN pnpm install && pnpm run build

RUN sass themes/BaseV1/assets/css/sass/main.scss:themes/BaseV1/assets/css/main.css

RUN chown www-data:www-data -R /var/www

COPY docker/recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh
COPY docker/jobs-cron.sh /jobs-cron.sh
COPY docker/entrypoint.sh /entrypoint.sh

COPY version.txt /var/www/version.txt

COPY docker/production/php.ini /usr/local/etc/php/php.ini

COPY docker/timezone.ini /usr/local/etc/php/conf.d/timezone.ini

RUN ln -s /var/www/html /var/www/public

# arquivos exclusivos dos ambientes de desenvolvimento
COPY docker/development/router.php /var/www/dev/router.php
COPY docker/development/start.sh /var/www/dev/start.sh

ENTRYPOINT ["/entrypoint.sh"]

WORKDIR /var/www
EXPOSE 9000

CMD ["php-fpm"]
