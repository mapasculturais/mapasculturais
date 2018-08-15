# Repositorio del Fork de Libre Coop de Mapas Culturais

## Sobre la aplicación
Esta es una aplicación web server-side basada en lenguaje PHP y base de datos Postgres, entre otras tecnologías y componentes, que propicia un ambiente virtual para mapeo, divulgación y gestión de activos culturales.

## Proyectos relacionados

* [Mapas Culturais APP](https://github.com/hacklabr/mapasculturais-app)
* [Cultural Magazine Theme](https://github.com/hacklabr/cultural)
* [Mapas SDK](https://github.com/centroculturalsp/MapasSDK)
* [Multiple Local Auth](https://github.com/LibreCoopUruguay/MultipleLocalAuth)


### Documentación

La documentación se puede navegar en la dirección (http://docs.mapasculturais.org) en portugués

Toda la documentación de la aplicación está en la carpeta [documentation](documentation) en portugués. 
Principales referencias:
- [Deploy](documentation/docs/mc_deploy.md)
- [Deploy Docker](documentation/docs/mc_developer_docker_enviroment.md)
- [API](documentation/docs/mc_config_api.md)
- [Guia do desenvolvedor](documentation/docs/mc_developer_guide.md)
- [Como contribuir](documentation/docs/mc_developer_contribute.md)
- [Habilitar um novo tema](documentation/docs/mc_deploy_theme.md)
- [Desenvolver um novo tema](documentation/docs/mc_developer_theme.md)
- [Importação de arquivos de dados geoespaciais (Shapefiles)](documentation/docs/mc_deploy_shapefiles.md)

### [Software] Requisitos de Instalación
Lista de los principales softwares que componen la aplicación. Más detalles, véase la documentación de [instalação](documentation/docs/mc_deploy.md) ou [guia do desenvolvedor](documentation/docs/mc_developer_guide.md). 

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

### [Hardware] Requisitos de instalación

Para instalaciones de pequeño / medio porte en las que el número de entidades, es decir, número de agentes, espacios, proyectos y eventos, giran alrededor de 2000 activos, se recomienda el mínimo de recursos para un servidor (aplicación + base de datos ):

* 2 cores de CPU;
* 2gb de RAM;
* 50mbit de red;

Deseable:

*  4 cores de CPU;
* 4gb de RAM;
* 100mbit de rede

Para instalaciones en ciudades de gran tamaño donde el número de entidades, es decir, número de agentes, espacios, proyectos y eventos, giran alrededor de decenas de miles de activos de cada uno, se recomienda el mínimo de recursos para un servidor:

* 4 cores de CPU
* 4gb de RAM
* 100mbit de red

Recomendado:
* 8 cores de CPU
* 8gb de RAM
* 500mbit de rede

Es importante recordar que los requisitos de hardware pueden variar de acuerdo con la latencia de la red, la velocidad de los cores de los cpus, el uso de proxies, entre otros factores. Recomendamos a los sysadmin de la red que en la aplicación se instale un monitoreo de tráfico y uso durante el período de 6 meses a 1 año para la evaluación de escenario de uso.

### Canales de comunicación

* [Desarrollo de Libre Coop](https://libre.coop)
 

### Ambientes de desarrollo y test

 Develop es nuestra rama de desarrollo y master es nuestra rama estable.
 Ver [Guia do desenvolvedor](doc/developer-guide.md) por más info. 

La url de test functiona en:

* http://nuevotest.libre.coop



### Licencia de uso y desarrollo

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 
