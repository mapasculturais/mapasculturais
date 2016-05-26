# Mapas Culturais

En julio de 2013, agentes culturales de diferentes países de América Latina y Brasil se reunieron para discutir la creación de una herramienta de mapeo de iniciativas de gestión cultural y de gestión cultural. De esa reunión surgió la base para la creación de Mapas Culturales, software libre que permite la mejora de la gestión cultural de las ciudades y estados y que en la región lleva el nombre de Mapas Culturales.

Mapas Culturales es una plataforma colaborativa que reúne información sobre los agentes, lugares, eventos y proyectos culturales, dando al gobierno una instantánea de la cultura de la zona y un mapa de los espacios ciudadanos y eventos culturales en la región. La plataforma está alineada con el Sistema Nacional de Información e Indicadores Culturales del Ministerio de Cultura (SNIIC) y contribuye a la realización de algunos de los objetivos del Plan Nacional de Cultura.

El Instituto TIM, en colaboración con una serie de Departamentos Cultura, ha actuado para la ejecución de los Mapas Culturales en la gestión pública de las ciudades y estados. La plataforma ya está en uso, por ejemplo, en São Paulo (http://spcultura.prefeitura.sp.gov.br/) y en el estado de Río Grande do Sul (http://mapa.cultura.rs.gov.br/).

En Julio de 2015 (<a href="http://libre.coop/">Libre Coop</a>) comenzó este proyecto de traducción y adaptación de la plataforma para la Dirección de Cultura del <a href="http://www.mec.gub.uy/"> Ministerio de Educación y Cultura</a> de Uruguay.

La plataforma está en uso en diversos municipios, estados, gobierno federal de Brasil, en varios proyectos del ministerio de cultura de Brasil. Las instalaciones son:

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


## Sobre la aplicación
Mapas Culturales es una aplicación web server-side basada en linguage PHP y base de datos Postgres, entre otras tecnologías y componentes, que propicia un ambiente virtual para mapeamiento, divulgación y gestión de activos culturales.  

### Licença de uso e desenvolvimento (portugués)

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 

