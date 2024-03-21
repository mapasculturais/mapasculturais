FROM php:8.2-fpm

ENV PNPM_HOME=/root/.local/share/pnpm
ENV PATH=$PATH:/root/.local/share/pnpm
ENV COMPOSER_ALLOW_SUPERUSER=1 

# Copy source
COPY composer.json /var/www/composer.json
COPY composer.lock /var/www/composer.lock
COPY config /var/www/config
COPY public /var/www/html
COPY scripts /var/www/scripts
COPY src /var/www/src
COPY plugins /var/www/src/plugins
COPY var /var/www/var
COPY common/config.d /var/www/config/common.d
COPY docker/recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh
COPY docker/jobs-cron.sh /jobs-cron.sh
COPY docker/entrypoint.sh /entrypoint.sh
COPY version.txt /var/www/version.txt


RUN apt-get update && apt-get install -y --no-install-recommends \
	imagemagick libmagickcore-dev libmagickwand-dev \
	libcurl4-gnutls-dev libpq-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libzstd1 && \
	# Instalação do node
	curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
	apt-get install -y nodejs && \
	## Instalação das extensões do node
	npm install -g pnpm terser uglifycss autoprefixer postcss sass && \
	# Install extensions
	docker-php-ext-install -j$(nproc) opcache pdo_pgsql  zip  xml  curl  opcache && \
	# Install GD
	docker-php-ext-install -j$(nproc) iconv && \
	docker-php-ext-configure gd --with-jpeg && \
	docker-php-ext-install -j$(nproc) gd && \
	# Install APCu
	no | pecl install apcu && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini && \
	# Install imagick
	autodetect | pecl install imagick && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini && \
	# Install igbinary
	pecl install igbinary && docker-php-ext-enable igbinary && \
	# Install msgpack
	yes | pecl install msgpack && echo "extension=msgpack.so" > /usr/local/etc/php/conf.d/msgpack.ini && \
	# Install redis
	no | pecl install -o -f redis && docker-php-ext-enable redis && \
	# Install composer
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    rm composer-setup.php && \
	#Execução do composer
	cd /var/www && composer.phar install && \
	# Instalação dos pacotes node
	cd /var/www/src && pnpm install --recursive && pnpm run build && \
	sass themes/BaseV1/assets/css/sass/main.scss:themes/BaseV1/assets/css/main.css && \
	# alteração das permissões 
	ln -s /var/www/html /var/www/public && \
	chown www-data:www-data -R /var/www && \
	# Limpeza do apt
	rm -rf /var/lib/apt/lists

WORKDIR /var/www

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm"]
