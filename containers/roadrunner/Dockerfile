ARG RR_VERSION
ARG RR_IMAGE=spiralscout/roadrunner:${RR_VERSION}
ARG PHP_IMAGE_VERSION
ARG PHP_IMAGE=php:${PHP_IMAGE_VERSION}

FROM ${RR_IMAGE} as rr

FROM ${PHP_IMAGE}

RUN apk update && \
  apk add --no-cache --virtual wget zip unzip libzip-dev freetype-dev libjpeg-turbo-dev libpng-dev sqlite sqlite-dev openssl-dev \
  git bash freetype-dev \
  libpng-dev libjpeg-turbo-dev \
  libxml2-dev autoconf \
  g++ imagemagick-dev \
  libtool make postgresql-dev \
  libzip-dev curl-dev gnu-libiconv


RUN docker-php-ext-install zip \
  && docker-php-ext-install sockets \
  # && docker-php-ext-install opcache \
  && docker-php-ext-install pdo_pgsql \
  && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
  && docker-php-ext-install -j$(nproc) gd \
  # && docker-php-ext-enable opcache \
  # && docker-php-ext-install gd \
  && docker-php-ext-install intl \
  && docker-php-ext-install opcache \
  && docker-php-ext-install xml \
  && docker-php-ext-install curl \
  # && docker-php-ext-install iconv \
  && docker-php-ext-install xmlwriter \
  && docker-php-ext-install simplexml

# Copy Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy RoadRunner
COPY --from=rr /usr/bin/rr /usr/bin/rr

# Copy RoadRunner config
COPY --from=rr /etc/rr.yaml /etc/rr.yaml

CMD ["/usr/bin/rr", "serve", "-d", "-c", "/etc/rr.yaml"]
