# Mapas Culturais

Em julho de 2013, agentes culturais de vários países da América Latina e do Brasil se reuniram para discutir a criação de uma ferramenta de mapeamento de iniciativas culturais e gestão cultural. Desse encontro surgiram as bases para a criação de Mapas Culturais, um software livre que permite o aprimoramento da gestão cultural dos municípios e estados.

Mapas Culturais é uma plataforma colaborativa que reúne informações sobre agentes, espaços, eventos e projetos culturais, fornecendo ao poder público uma radiografia da área de cultura e ao cidadão um mapa de espaços e eventos culturais da região. A plataforma está alinhada ao Sistema Nacional de Informação e Indicadores Culturais do Ministério da Cultura (SNIIC) e contribui para a realização de alguns dos objetivos do Plano Nacional de Cultura.

A plataforma já está em uso em diversos municipios, estados, no governo federal em diversos projetos do ministério da cultura e até mesmo fora do Brasil no Uruguai. Instalações recentes: 

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

## Sobre a aplicação
Mapas Culturais é uma aplicação web server-side baseada em linguagem PHP e banco de dados Postgres, entre outras tecnologias e componentes, que propicia um ambiente virtual para mapeamento, divulgação e gestão de ativos culturais.  

### Documentação 
Toda documentação da aplicação está na pasta [documentation](documentation). Principais referências: 
- [Deploy](documentation/docs/mc_deploy.md)
- [API](documentation/docs/mc_config_api.md)
- [Guia do desenvolvedor](documentation/docs/mc_developer_guide.md)
- [Habilitar um novo tema](documentation/docs/mc_deploy_theme.md)
- [Desenvolver um novo tema](documentation/docs/mc_developer_theme.md)
- [Importação de arquivos de dados geoespaciais (Shapefiles)](documentation/docs/mc_deploy_shapefiles.md)

### [Software] Requisitos para Instalação
Lista dos principais softwares que compõe e aplicação. Maiores detalhes, ver documentação de [instalação](documentation/docs/mc_deploy.md) ou [guia do desenvolvedor](documentation/docs/mc_developer_guide.md). 

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
* 
### Ambientes de desenvolvimento e teste
Estes ambientes possuem integração contínua com os branches e temas atuais. Para maiores detalhes, ver [Guia do desenvolvedor](doc/developer-guide.md). 

* http://master.map.as
* http://rc.map.as
* http://stable.map.as

Para acessar os temas, use a url com nome do tema ou instalação. Ex.: 

* http://museus.minc.stable.map.as

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
