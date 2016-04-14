# Instalación de Mapas Culturales en Ubuntu 14.04

En esta guía mostraremos paso a paso como realizar la instalación de <Mapas Culturales> utilizando nginx + php-fpm en un sistema <Ubuntu 14.04 Server> recién instalado con únicamente OpenSSH Server. La base de datos y la aplicación se ejecutará en el mismo servidor y con el mismo usuario.

No abarcaremos las configuraciones de autenticación, ya sea con <ID da Cultura>, ya sea con <Login Cidadão>. Al final de la guía tendremos la aplicación ejecutando con el método de autenticación falsa (Fake).

Las líneas que comienzan con $ se ejecutan con el usuario creado para ejecutar la aplicación y las líneas que comienzan con @ se ejecutan con el usuario root.

### 1. Instalación de los paquetes necesarios para el funcionamiento del sistema

Primero instalamos los paquetes via apt

# dependencias diversas
@ apt-get install git curl nodejs npm ruby

# postgresql e postgis
@ apt-get install postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts

# php, php-fpm y extenciones de php utilizadas en el sistema
@ apt-get install php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc php5-fpm

# nginx
@ apt-get install nginx

Instalación del generador de dependencias de PHP Composer

@ curl -sS https://getcomposer.org/installer | php

@ mv composer.phar /usr/local/bin/composer.phar

En Ubuntu el ejecutable de NodeJS se llama nodejs, pero para el correcto funcionamiento de las bibliotecas utilizadas, el ejecutable debe llamarse simplemente node. Para ello creamos un enlace simbólico con el siguiente comando

@ update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

Instalación de los minificadores (Code Minifier) para  Javascript y CSS: uglify-js, uglifycss y autoprefixer

@ npm install -g uglify-js uglifycss autoprefixer

Instalación de SASS, utilizado para compilar los archivos CSS

@ gem install sass


### 2. Clonado del repositorio

Primero vamos a crear el usuario que ejecutara la aplicación y que sera propietario del la base de datos, definiendo su directorio home en /srv y colocándolo en el grupo www-data .

@ useradd -G www-data -d /srv/mapas -m mapas

Ahora vamos a clonar el repositorio utilizando el usuario creado antes, entonces primero necesitamos “loguearnos” con este usuario.

@ su - mapas

Ahora clonamos el repositorio.

$ git clone https://github.com/hacklabr/mapasculturais.git

Y cambiamos a la rama estable. Si se tratara de una instalación de prueba, se puede omitir este paso.

$ cd mapasculturais
$ git checkout stable

Ahora instalamos las dependencias de PHP utilizando Composer.
$ cd src/protected/
$ composer.phar install


### 3. Creación de la base de datos.

Cambiamos al usuario root para crear la base de datos.
$ exit

Primero vamos a crear el usuario en la con el mismo nombre de usuario del sistema.
@ sudo -u postgres psql -c "CREATE USER mapas"

Ahora creamos la base de datos para la aplicación con el mismo nombre de usuario.
@ sudo -u postgres createdb --owner mapas mapas

Creamos las extensiones necesarias en el base.

@ sudo -u postgres psql -d mapas -c "CREATE EXTENSION postgis;"
@ sudo -u postgres psql -d mapas -c "CREATE EXTENSION unaccent;"

Volvemos a “loguearnos” con el usuario creado para importar el esquema de la base de datos.

@ su - mapas
$ psql -f mapasculturais/db/schema.sql


### 4. Configuración de la aplicación.

#### Configuración de Mapas Culturales

Primero cree un archivo de configuración copiando el archivo de template de configuración. Este archivo esta preparado para funcionar con esta guía, utilizando el método de autenticación Fake.

$ cp mapasculturais/src/protected/application/conf/config.template.php mapasculturais/src/protected/application/conf/config.php

#### Creación de las carpetas necesarias.

Como root, cree la carpeta para los archivos de log
$ exit
@ mkdir /var/log/mapasculturais
@ chown mapas:www-data /var/log/mapasculturais

Como el usuario creado, cree las carpetas assets y files (para los uploads).

@ su - mapas
$ mkdir mapasculturais/src/assets
$ mkdir mapasculturais/src/files

#### Configuración de nginx

Necesitamos crear el virtual host de nginx para la aplicación. Para esto cree, con root, el archivo /etc/nginx/sites-available/mapas.conf con el contenido indicado abajo:

server {
  set $site_name meu.dominio.gov.br;

  listen *:80;
  server_name  meu.dominio.gov.br;
  access_log   /var/log/mapasculturais/nginx.access.log;
  error_log    /var/log/mapasculturais/nginx.error.log;

  index index.php;
  root  /srv/mapas/mapasculturais/src/;

  location / {
    try_files $uri $uri/ /index.php?$args;
  }

  location ~* \.(js|css|png|jpg|jpeg|gif|ico|woff)$ {
          expires 1w;
          log_not_found off;
  }

  location ~ \.php$ {
    try_files $uri =404;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/var/run/php5-fpm-$site_name.sock;
    client_max_body_size 0;
  }

  charset utf-8;
}

server {
  listen *:80;
  server_name www.meu.dominio.gov.br;
  return 301 $scheme://meu.dominio.gov.br$request_uri;
}


Cree el link para habilitar el virtual host.

ln -s /etc/nginx/sites-available/mapas.conf /etc/nginx/sites-enabled/mapas.conf

#### Creación del pool en php-fpm

Cree el archivo /etc/php5/fpm/pool.d/mapas.conf con el contenido indicado abajo:

[mapas]
listen = /var/run/php5-fpm-meu.dominio.gov.br.sock
listen.owner = mapas
listen.group = www-data
user = mapas
group = www-data
catch_workers_output = yes
pm = dynamic
pm.max_children = 10
pm.start_servers = 1
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500
chdir = /srv/mapas
; php_admin_value[open_basedir] = /srv/mapas:/tmp
php_admin_value[session.save_path] = /tmp/
; php_admin_value[error_log] = /var/log/mapasculturais/php.error.log
; php_admin_flag[log_errors] = on
php_admin_value[display_errors] = 'stderr'


### 5. Conclusión.

Para finalizar , necesitamos rellenar la base de datos con los datos iniciales y ejecutar un script que entre otras cosas compila y minifica los activos (assets), optimiza los autoload de clases del composer y ejecuta actualizaciones de la base.

@ su - mapas
$ psql -f mapasculturais/db/initial-data.sql
$ ./mapasculturais/scripts/deploy.sh

Reinicie los servicios de nginx y php-fpm

@ service nginx restart
@ service php5-fpm restart
