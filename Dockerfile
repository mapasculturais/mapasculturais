# Imagem para rodar o mapas em modo e desenvolvimento. Ainda não está
# completamente funcional e são necessários alguns ajustes para a
# compilação de assets.
#
# @depends mdillon/postgis
#
# Quickstart:
#     $ docker build -t hacklab/mapasculturais .
#     $ docker run --name postgis -p 5432:5432 -e POSTGRES_PASSWORD=postgis -d mdillon/postgis
#     $ docker run --rm --name mapas --link postgis:postgis -i -p 80:8000  -t hacklab/mapasculturais
#
FROM debian:jessie

RUN apt-get update

RUN apt-get install -y git curl nodejs npm ruby \
    php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc \
    postgresql-client

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10
RUN npm install -g uglify-js uglifycss autoprefixer
RUN gem install sass

RUN git -C /srv clone https://github.com/hacklabr/mapasculturais.git

RUN useradd -G www-data -d /srv/mapasculturais -s /bin/bash mapasculturais; \
    mkdir -p /srv/mapasculturais/src/assets; \
    mkdir -p /srv/mapasculturais/src/files; \
    chown -R mapasculturais:www-data /srv/mapasculturais

USER mapasculturais
WORKDIR /srv/mapasculturais

RUN (cd src/protected \
        && composer -n install --prefer-dist \
        && composer -n dump-autoload --optimize)

RUN cp src/protected/application/conf/config.template.php src/protected/application/conf/config.php
RUN sed -i -z -e "s/'doctrine.database'[^]]*\]/'doctrine.database'=>['dbname'=>'mapasculturais','password'=>'mapasculturais','user'=>'mapasculturais','host'=>'postgis',]/" \
        src/protected/application/conf/config.php

ADD scripts/docker_entrypoint.sh scripts/docker_entrypoint.sh

ENTRYPOINT ["scripts/docker_entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "src/"]

