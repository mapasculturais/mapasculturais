# Mapas Culturais
Em julho de 2013, agentes culturais de vários países da América Latina e do Brasil se reuniram para discutir a criação de uma ferramenta de mapeamento de iniciativas culturais e gestão cultural. Desse encontro surgiram as bases para a criação de Mapas Culturais, um software livre que permite o aprimoramento da gestão cultural dos municípios e estados.

Mapas Culturais é uma plataforma colaborativa que reúne informações sobre agentes, espaços, eventos e projetos culturais, fornecendo ao poder público uma radiografia da área de cultura e ao cidadão um mapa de espaços e eventos culturais {{dict: site: of the region}}. A plataforma está alinhada ao Sistema Nacional de Informação e Indicadores Culturais do Ministério da Cultura (SNIIC) e contribui para a realização de alguns dos objetivos do Plano Nacional de Cultura.

A plataforma já está em uso, por exemplo, no município de São Paulo (http://spcultura.prefeitura.sp.gov.br/) e no estado do Rio Grande do Sul (http://mapa.cultura.rs.gov.br/).

### Travis Build Status

[![Build Status](https://secure.travis-ci.org/hacklabr/mapasculturais.png)](http://travis-ci.org/hacklabr/mapasculturais)

### Issue Tracker

Stories: [![Stories in Dev Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:dev-ready)](https://waffle.io/hacklabr/mapasculturais) for Development, [![Stories in Test Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:test-ready)](https://waffle.io/hacklabr/mapasculturais) for Test, [![Stories in Deploy Ready](https://badge.waffle.io/hacklabr/mapasculturais.png?label=status:tested)](https://waffle.io/hacklabr/mapasculturais) for Deploy

### Documentação 
- [Entidades dos Mapas Culturais](doc/entidades.md)
- [API](doc/api.md)
- [Guia do desenvolvedor](doc/developer-guide.md)

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
- PostgreSQL >= 9.3
- Postgis Contrib (for Unaccent extension)
- Postgis >= 2.1
-  PostgreSQL Postgis Scripts

Em distribuições GNU/Linux baseadas em Debian:
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
### Suporte
[<img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-xpa1/v/t1.0-1/p50x50/10346655_10152478219659636_7235974946349859716_n.png?oh=a2dd23e4c9d7daf25bb1f47f7e8bc270&oe=5526F051&__gda__=1433079532_c274f04cda725a7f7c6c560c057d8426">](http://www.hipchat.com/gAMisvNwG) Chat de suporte em tempo real
