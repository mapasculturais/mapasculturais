#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")/../" && pwd )"
BaseV1=$DIR/src/protected/application/themes/BaseV1/assets

if [ $1 ]; then
	CONFIG=$1
else 
	CONFIG=config.php
fi

CDIR=$( pwd )
cd $DIR/src/protected/tools/

ASSETS_FOLDER=$(MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST='localhost' REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php get-theme-assets-path.php)

echo "compilando main.css do tema BaseV1"
sass $BaseV1/css/sass/main.scss:$BaseV1/css/main.css

echo "aplicando o autoprefixer no main.css do tema BaseV1"
autoprefixer $BaseV1/css/main.css

if [ -f $ASSETS_FOLDER/css/sass/main.scss ]; then
  echo "compilando main.css do tema ativo"
  sass $ASSETS_FOLDER/css/sass/main.scss:$ASSETS_FOLDER/css/main.css

  echo "aplicando o autoprefixer no main.css do tema ativo"
  autoprefixer $ASSETS_FOLDER/css/main.css
fi

cd $CDIR;
