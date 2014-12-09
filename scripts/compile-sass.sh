#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")/../" && pwd )"
BaseV1=$DIR/src/protected/application/themes/BaseV1/assets

CDIR=$( pwd )
cd $DIR/src/protected/tools/

ASSETS_FOLDER=$(REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php get-theme-assets-path.php)

sass $BaseV1/css/sass/main.scss:$BaseV1/css/main.css

if [ -f $ASSETS_FOLDER/css/sass/main.scss ]; then
  sass $ASSETS_FOLDER/css/sass/main.scss:$ASSETS_FOLDER/css/main.css
fi

cd $CDIR;