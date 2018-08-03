FROM redelivre/php

WORKDIR /var/www/html
RUN mkdir -p /var/www/html/src/protected/vendor

COPY ./src/protected/composer.* /var/www/html/src/protected/
RUN composer config cache-dir -d /var/www/html/src/protected \
    && composer install --no-interaction --no-scripts --no-autoloader -d /var/www/html/src/protected

COPY . /var/www/html

RUN find src/protected/application/themes/ -maxdepth 1 -mindepth 1 -exec echo "compilando sass do tema " {} \; -exec sass {}/assets/css/sass/main.scss {}/assets/css/main.css -E "UTF-8" \; \
    && composer dump-autoload -d /var/www/html/src/protected \
    && chown -R www-data:\root /var/www/html

COPY compose/production/php.ini /usr/local/etc/php/php.ini
COPY compose/ /var/www/html/src/protected/application/conf/
