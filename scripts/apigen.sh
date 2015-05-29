#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR
if [ ! -f "$DIR/_apigen.phar" ]; then
    wget http://apigen.org/apigen.phar --output-document="_apigen.phar"
    chmod +x _apigen.phar
fi

./_apigen.phar generate -s "$DIR/../src/" -d "$DIR/../api/" --template-theme="bootstrap" --exclude="*vendor*,*DoctrineProxies*,*SpCultura*,*Blumenau*,*JoaoPessoa*,*Ceara*,*RS*,*Sobral*,*SaoJose*,*geojson*,*postgis-restful-web-service-framework*" --annotation-groups="hook,workflow"

cd $CDIR