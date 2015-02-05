l[![Build Status](https://secure.travis-ci.org/hacklabr/mapasculturais.png)](http://travis-ci.org/hacklabr/mapasculturais)

Stories: [![Stories in Dev Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:dev-ready)](https://waffle.io/hacklabr/mapasculturais) for Development, [![Stories in Test Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:test-ready)](https://waffle.io/hacklabr/mapasculturais) for Test, [![Stories in Deploy Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:tested)](https://waffle.io/hacklabr/mapasculturais) for Deploy

### Documentação 
- [Entidades dos Mapas Culturais](doc/entidades.md)
- [API](doc/api.md)
- [Guia do desenvolvedor](doc/developer-guide.md)

[<img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-xpa1/v/t1.0-1/p50x50/10346655_10152478219659636_7235974946349859716_n.png?oh=a2dd23e4c9d7daf25bb1f47f7e8bc270&oe=5526F051&__gda__=1433079532_c274f04cda725a7f7c6c560c057d8426">](http://www.hipchat.com/gAMisvNwG) Canal de Suporte

### Requisitos para Instalação
- PHP >= 5.4
- Extensões PHP:
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
- PostgreSQL >= 9.1
- Postgis Contrib (for Unaccent extension)
- Postgis >= 1.5
-  PostgreSQL Postgis Scripts
Em distribuições GNU/Linux baseadas em Debian:
```shell
sudo apt-get install php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts
```
- Node.JS
  - NPM
  - UglifyJS
  - UglifyCSS
```shell
sudo npm install -g uglify-js uglifycss autoprefixer
```
- Ruby
  - SASS
```shell
sudo gem install -g sass
```
