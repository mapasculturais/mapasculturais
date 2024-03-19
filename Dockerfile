FROM dunglas/frankenphp

# add additional extensions here:
RUN install-php-extensions \
    pdo_pgsql \
    gd \
    intl \
    zip \
    opcache \
    xml \
    curl \
    iconv \
    xmlwriter \
    simplexml