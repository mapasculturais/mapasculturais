# Imagem para rodar o mapas em modo e desenvolvimento. Ainda não está
# completamente funcional e são necessários alguns ajustes para a
# compilação de assets.
#
# @depends mdillon/postgis
#
# Quickstart:
#     $ docker build -t hacklab/mapasculturais .
#     $ docker run --name postgis -p 5432:5432 -e POSTGRES_PASSWORD=postgis -d mdillon/postgis
#     $ docker run --name mapas --link postgis:postgis -i -p 80:8000  -t hacklab/mapasculturais
#
# É possível montar o código fonte do desenvolvimento sobre a que está
# clonada no container, assim é possível visualizar suas alterações no browser.
#
#     $ docker run --rm --name mapas --link postgis:postgis -v $PWD:/srv/mapas/mapasculturais -i -p 80:8000  -t hacklab/mapasculturais
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

RUN mkdir -p /srv/mapas \
    && git -C /srv/mapas clone https://github.com/hacklabr/mapasculturais.git

RUN useradd -G www-data -d /srv/mapas -s /bin/bash mapas; \
    mkdir -p /srv/mapas/mapasculturais/src/assets; \
    mkdir -p /srv/mapas/mapasculturais/src/files; \
    chown -R mapas:www-data /srv/mapas

USER mapas
WORKDIR /srv/mapas/mapasculturais

RUN (cd src/protected \
        && composer -n install --prefer-dist \
        && composer -n dump-autoload --optimize)

ENTRYPOINT ["scripts/docker_entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "src/"]

