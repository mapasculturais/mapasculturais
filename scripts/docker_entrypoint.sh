#!/bin/bash

export PG_DB="${PG_DB:-mapasculturais}";
export PG_PASS="${PG_PASS:-mapasculturais}";
export PG_USER="${PG_USER:-mapasculturais}";
export PG_HOST="${PG_HOST:-postgis}";

if ! [ -f "src/protected/application/conf/config.php" ];
then
    cp src/protected/application/conf/config.template.php src/protected/application/conf/config.php
fi

doctrine_conf="'doctrine.database'=>[";
doctrine_conf="$doctrine_conf 'dbname'=>'$PG_DB',";
doctrine_conf="$doctrine_conf 'password'=>'$PG_PASS',";
doctrine_conf="$doctrine_conf 'user'=>'$PG_USER',";
doctrine_conf="$doctrine_conf 'host'=>'$PG_HOST',";
doctrine_conf="$doctrine_conf ]";

su mapas -c sh << SUBSCRIPT
sed -i -z -e "s/'doctrine.database'[^]]*\]/$doctrine_conf/" src/protected/application/conf/config.php
SUBSCRIPT

#
# Executa o que está em CMD no Dockerfile
#
exec "$@"
