[![Join the chat at https://telegram.me/#MapasCulturais](https://patrolavia.github.io/telegram-badge/chat.png)](https://telegram.me/joinchat/AVzIFAqpz3VFIPehrPpbZQ)

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
* https://mapas.cultura.mt.gov.br

## Sobre a aplicação
Mapas Culturais é uma aplicação web server-side baseada em linguagem PHP e banco de dados Postgres, entre outras tecnologias e componentes, que propicia um ambiente virtual para mapeamento, divulgação e gestão de ativos culturais. 

## Projetos correlatos

* [Mapas Culturais APP](https://github.com/hacklabr/mapasculturais-app)
* [Cultural Magazine Theme](https://github.com/hacklabr/cultural)
* [Mapas SDK](https://github.com/centroculturalsp/MapasSDK)


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

- [Ubuntu Server >= 14.04](http://www.ubuntu.com) ou [Debian Server >= 8](https://www.debian.org.)
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
- [Node.JS >= 4.x](https://nodejs.org/en/)
  - [NPM](https://www.npmjs.com/)
  - [UglifyJS](https://www.npmjs.com/package/uglify-js)
  - [UglifyCSS](https://www.npmjs.com/package/gulp-uglifycss)
- [Ruby] (https://www.ruby-lang.org/pt)
  - [Sass gem] (https://rubygems.org/gems/sass/versions/3.4.22)

### [Hardware] Requisitos para instalação

Para instalações de pequeno/medio porte nas quais o número de entidades, isto é, número de agentes, espaços, projetos e evento,giram em torno de 2000 ativos, recomenda-se o mínimo de recursos para um servidor (aplicação + base de dados):

* 2 cores de CPU;
* 2gb de RAM;
* 50mbit de rede;

Desejável:

*  4 cores de CPU;
* 4gb de RAM;
* 100mbit de rede;

Para instalações em cidades de grande porte onde o número de entidades, isto é, número de agentes, espaços, projetos e evento, giram em torno de dezenas de milhares de ativos de cada, recomenda-se o mínimo de recursos para um servidor:

* 4 cores de CPU
* 4gb de RAM
* 100mbit de rede

Recomendado:
* 8 cores de CPU
* 8gb de RAM
* 500mbit de rede

Vale lembrar que os requisitos de hardware podem variar de acordo com a latência da rede, velocidade dos cores dos cpus, uso de proxies, entre outros fatores. Recomendamos aos sysadmin da rede em que a aplicação será instalada um monitoramento de tráfego e uso durante o período de 6 meses a 1 ano para avaliação de cenário de uso. 

### Canais de comunicação

* [Lista de discussão](https://groups.google.com/forum/?hl=en#!forum/mapas-culturais)
* [Chat de discussão](http://chat.mapasculturais.org)
 

### Ambientes de desenvolvimento e teste
Estes ambientes possuem integração contínua com os branches e temas atuais. Para maiores detalhes, ver [Guia do desenvolvedor](doc/developer-guide.md). 

As urls de testes functionam da seguinte maneira:

* http://nomedacidade.uf.ambiente.map.as e
* http://uf.ambiente.map.as

Os ambientes possíveis são **stable**, **rc** e **master**, onde:

stable: É a última versão de produção que está disponibilizada do software
rc: Sigla de **Release Candidate**, onde as features (funcionalidades) são homologadas antes de irem para a versão **stable**
master: Ambiente de desenvolvimento, em constante evolução antes de ter pacotes de features para irem para a versão **rc**

Exemplos:
* http://saopaulo.sp.stable.map.as
* http://blumenau.sc.rc.map.as
* http://santoandre.sp.master.map.as
* http://ce.rc.map.as
* http://rs.stable.map.as
* http://df.master.map.as
* http://museus.minc.rc.map.as (minc)
* http://bibliotecas.minc.stable.map.as (minc)

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
