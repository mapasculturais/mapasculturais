# Mapas Culturais
<<<<<<< HEAD
=======

Em julho de 2013, agentes culturais de vários países da América Latina e do Brasil se reuniram para discutir a criação de uma ferramenta de mapeamento de iniciativas culturais e gestão cultural. Desse encontro surgiram as bases para a criação de Mapas Culturais, um software livre que permite o aprimoramento da gestão cultural dos municípios e estados.
>>>>>>> remotes/hacklab/master

En julio de 2013, agentes culturales de diferentes países de América Latina y Brasil se reunieron para discutir la creación de una herramienta de mapeo de iniciativas de gestión cultural y de gestión cultural. De esa reunión surgió la base para la creación de Mapas Culturales, software libre que permite la mejora de la gestión cultural de las ciudades y estados y que en la región lleva el nombre de Mapas Culturales.

<<<<<<< HEAD
Mapas Culturales es una plataforma colaborativa que reúne información sobre los agentes, lugares, eventos y proyectos culturales, dando al gobierno una instantánea de la cultura de la zona y un mapa de los espacios ciudadanos y eventos culturales en la región. La plataforma está alineada con el Sistema Nacional de Información e Indicadores Culturales del Ministerio de Cultura (SNIIC) y contribuye a la realización de algunos de los objetivos del Plan Nacional de Cultura.

El Instituto TIM, en colaboración con una serie de Departamentos Cultura, ha actuado para la ejecución de los Mapas Culturales en la gestión pública de las ciudades y estados. La plataforma ya está en uso, por ejemplo, en São Paulo (http://spcultura.prefeitura.sp.gov.br/) y en el estado de Río Grande do Sul (http://mapa.cultura.rs.gov.br/).

En Julio de 2015 comenzamos este proyecto de traducción y adaptación de la plataforma.
=======
A plataforma já está em uso em diversos municipios, estados, no governo federal em diversos projetos do ministério da cultura e até mesmo fora do Brasil no Uruguai. Instalações recentes: 
>>>>>>> remotes/hacklab/master

* http://spcultura.prefeitura.sp.gov.br
* http://estadodacultura.sp.gov.br
* http://jpcultura.joaopessoa.pb.gov.br
* http://cultura.sobral.ce.gov.br
* http://mapa.cultura.ce.gov.br
* http://blumenaumaiscultura.com.br
* http://mapa.cultura.rs.gov.br
* http://culturaz.santoandre.sp.gov.br
* http://mapa.cultura.to.gov.br
* https://mapas.cultura.mt.gov.br
* http://mapaculturalbh.pbh.gov.br
* http://lugaresdacultura.org.br
* http://mapas.cultura.gov.br
* http://culturaviva.gov.br
* http://bibliotecas.cultura.gov.br
* http://museus.cultura.gov.br

<<<<<<< HEAD
Stories: [![Stories in Dev Ready](https://badge.waffle.io/LibreCoopUruguay/mapasculturais.png?label=status:dev-ready)](https://waffle.io/LibreCoopUruguay/mapasculturais) for Development, [![Stories in Test Ready](https://badge.waffle.io/LibreCoopUruguay/mapasculturais.png?label=status:test-ready)](https://waffle.io/LibreCoopUruguay/mapasculturais) for Test, [![Stories in Deploy Ready](https://badge.waffle.io/LibreCoopUruguay/mapasculturais.png?label=status:tested)](https://waffle.io/LibreCoopUruguay/mapasculturais) for Deploy

### Documentación 
- [Entidades de los Mapas Culturales](doc/entidades.md)
- [API](doc/api.md) (En portugués)
- [Guía del desarrollador](doc/developer-guide.md) (En portugués)
- [Creando un tema personalizado](doc/developer-guide/themes.md) (En portugués)
- [Deploy en Ubuntu 14.04 con nginx y php-fpm](doc/deploy-ubuntu-14.04.md) (En Español)
- [Importación de archivos de datos geoespaciales (Shapefiles)](doc/shapefiles.md)

### Requisitos de Instalación
- PHP >= 5.4
- Extensiones PHP:
  - php5-gd
  - php5-cli
  - php5-json
  - php5-curl
  - php5-pgsql
  - php-apc
  - Zip extension enabled in php.ini
- Composer
```shell
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
```
- PostgreSQL >= 9.3 o superior
- Postgis Contrib (for Unaccent extension)
- Postgis >= 2.1
-  PostgreSQL Postgis Scripts

#### En distribuiciones GNU/Linux basadas en Debian:
```shell
sudo apt-get install php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts
```
- Node.JS >= 0.10
  - NPM
  - UglifyJS
  - UglifyCSS
```shell
sudo npm install -g uglify-js uglifycss autoprefixer
```
- Ruby  >= 1.9.3
  - SASS
```shell
sudo gem install sass
```
### Soporte
[Chat general en portugués] (http://chat.mapasculturais.org/channel/general)

[Chat en español] (http://chat.mapasculturais.org/channel/spanish)

=======
## Sobre a aplicação
Mapas Culturais é uma aplicação web server-side baseada em linguagem PHP e banco de dados Postgres, entre outras tecnologias e componentes, que propicia um ambiente virtual para mapeamento, divulgação e gestão de ativos culturais.  

### Documentação 
Toda documentação da aplicação está na pasta [doc](doc). Principais referências: 
- [Deploy](doc/deploy-ubuntu-14.04.md)
- [API](doc/api.md)
- [Guia do desenvolvedor](doc/developer-guide.md)
- [Criando um tema personalizado](doc/developer-guide/themes.md)
- [Importação de arquivos de dados geoespaciais (Shapefiles)](doc/shapefiles.md)

### Requisitos para Instalação
Lista dos principais softwares que compõe e aplicação. Maiores detalhes, ver documentação de [instalação](doc/deploy-ubuntu-14.04.md) ou [guia do desenvolvedor](doc/developer-guide.md). 

- [PHP >= 5.4](http://php.net)
  - [php5-gd](http://php.net/manual/pt_BR/book.image.php)
  - [php5-cli] (https://packages.debian.org/pt-br/jessie/php5-cli)
  - [php5-json](http://php.net/manual/pt_BR/book.json.php)
  - [php5-curl](http://php.net/manual/pt_BR/book.curl.php)
  - [php5-pgsql](http://php.net/manual/pt_BR/book.pgsql.php)
  - [php-apc](http://php.net/manual/pt_BR/book.apc.php)
- [Composer](https://getcomposer.org/)
- [PostgreSQL >= 9.3](http://www.postgresql.org/)
- [Postgis >= 2.1](http://postgis.net)
  - [PostgreSQL-Postgis-Scripts](http://packages.ubuntu.com/trusty/misc/postgresql-9.3-postgis-2.1)
- [Node.JS >= 0.10](https://nodejs.org/en/)
  - [NPM](https://www.npmjs.com/)
  - [UglifyJS](https://www.npmjs.com/package/uglify-js)
  - [UglifyCSS](https://www.npmjs.com/package/gulp-uglifycss)
- [Ruby] (https://www.ruby-lang.org/pt)
  - [Sass gem] (https://rubygems.org/gems/sass/versions/3.4.22)

### Canais de comunicação

* [Lista de discussão](https://groups.google.com/forum/?hl=en#!forum/mapas-culturais)
* [Chat de discussão](http://chat.mapasculturais.org)

### Stories & Tests

- Stories for development: 
[![Stories in Dev Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:dev-ready)](https://waffle.io/hacklabr/mapasculturais) 
- Stories for test: 
[![Stories in Test Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:test-ready)](https://waffle.io/hacklabr/mapasculturais)
- Stories for deploy: [![Stories in Deploy Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:tested)](https://waffle.io/hacklabr/mapasculturais)
- Travis:
[![Build Status](https://secure.travis-ci.org/hacklabr/mapasculturais.png)](http://travis-ci.org/hacklabr/mapasculturais)

### Licença de uso e desenvolvimento

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 
>>>>>>> remotes/hacklab/master
