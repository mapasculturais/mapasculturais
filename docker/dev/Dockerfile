from debian:jessie-slim

RUN apt-get update -y && apt-get install -y --no-install-recommends \
        curl \
        locales \
        git \
        imagemagick \
        libmagickcore-dev \
        libmagickwand-dev \
        nginx \
        npm \
        php-apc \
        php5 \
        php5-cli \
        php5-curl \
        php5-fpm \
        php5-gd \
        php5-imagick \
        php5-json \
        php5-pgsql \
        ruby2.1 \
        ruby2.1-dev \
        zip \
        # Para rodar o script
        python-psycopg2 \
    && rm -rf /var/lib/apt/lists/* \
    \
    # Instalar node
    && curl -sL https://deb.nodesource.com/setup_4.x | bash - \
    && apt-get install -y nodejs \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer.phar \
    \
    # Instalar dependencias npm
    && npm install -g \
        uglify-js@2.2.0 \
        uglifycss \
        autoprefixer \
    \
    # Instalar gems do ruby
    && gem2.1 install sass -v 3.4.22 \
    \
    # Locale gen
    && sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen \
    && locale-gen \


# Inicia usuário mapas
RUN mkdir -p /srv/mapas

COPY . /srv/mapas/mapasculturais

RUN useradd -G www-data -d /srv/mapas -s /bin/bash mapas; \
    mkdir -p /srv/mapas/mapasculturais/src/assets; \
    mkdir -p /srv/mapas/mapasculturais/src/files; \
    chown -R mapas:www-data /srv/mapas

USER mapas

WORKDIR /srv/mapas/mapasculturais

RUN (cd src/protected \
        && composer.phar -n install --prefer-dist \
        && composer.phar -n dump-autoload --optimize) \
    && mkdir -p src/assets && mkdir -p src/files

COPY src/protected/application/conf/config.docker.php src/protected/application/conf/config.php

USER root

RUN mkdir /var/log/mapasculturais \
    && chown mapas:www-data /var/log/mapasculturais

ADD config_files/nginx/mapas.conf /etc/nginx/sites-available
ADD config_files/php5/mapas.conf /etc/php5/fpm/pool.d
ADD config_files/entrypoint.sh entrypoint.sh

RUN ln -s /etc/nginx/sites-available/mapas.conf /etc/nginx/sites-enabled/mapas.conf \
    && rm /etc/nginx/sites-available/default \
    && echo "daemon off;" >> /etc/nginx/nginx.conf \
    && update-rc.d nginx defaults && update-rc.d php5-fpm defaults \
    && chmod +x entrypoint.sh

ENTRYPOINT ["/srv/mapas/mapasculturais/entrypoint.sh"]

EXPOSE 80
