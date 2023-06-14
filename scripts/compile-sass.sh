#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")/../" && pwd )"
BaseV1=$DIR/src/protected/application/themes/BaseV1/assets

if [ $1 ]; then
	DOMAIN=$1
        echo $DOMAIN
else
	DOMAIN=localhost
fi

if [ $2 ]; then
	CONFIG=$2
else
	CONFIG=config.php
fi

CDIR=$( pwd )
cd $DIR/src/protected/tools/

ASSETS_FOLDER=$(MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php get-theme-assets-path.php)

echo "compilando main.css do tema BaseV1"
sass $BaseV1/css/sass/main.scss $BaseV1/css/main.css -E "UTF-8"

#echo "aplicando o autoprefixer no main.css do tema BaseV1"
#autoprefixer-cli $BaseV1/css/main.css

if [ -f $ASSETS_FOLDER/css/sass/main.scss ]; then
  echo "compilando main.css do tema ativo $ASSETS_FOLDER/"
  sass $ASSETS_FOLDER/css/sass/main.scss $ASSETS_FOLDER/css/main.css -E "UTF-8"

#  echo "aplicando o autoprefixer no main.css do tema ativo $ASSETS_FOLDER/"
#  autoprefixer-cli $ASSETS_FOLDER/css/main.css
fi

cd $CDIR;
