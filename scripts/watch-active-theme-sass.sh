#!/bin/bash
THEMES_PATH="/var/www/src/themes"
THEME=${ACTIVE_THEME:-BaseV1}
ASSETS_PATH="$THEMES_PATH/$THEME/assets/css"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

$DIR/compile-sass.sh

sass --watch $ASSETS_PATH/sass/main.scss:$ASSETS_PATH/main.css