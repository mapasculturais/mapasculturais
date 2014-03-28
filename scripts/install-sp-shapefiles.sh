#!/bin/bash

tar xC /tmp -f ../db/sp-shapefile-sql.tar.xz

sudo -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -c "DROP table IF EXISTS sp_regiao;"
sudo -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -f /tmp/sp-shapefile-sql/sp_regiao.sql

sudo -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -c "DROP table IF EXISTS sp_distrito;"
sudo -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -f /tmp/sp-shapefile-sql/sp_distrito.sql

sudo -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -c "DROP table IF EXISTS sp_subprefeitura;"
sudo -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -f /tmp/sp-shapefile-sql/sp_subprefeitura.sql

read -p "Mapas Culturais: Delete temporary /tmp/sp-shapefiles-sql/ files? (Y/n) 
" -n 1 -r
echo    # (optional) move to a new line
if [[ ! $REPLY =~ ^[Nn]$ ]]
then
    rm -rf /tmp/sp-shapefile-sql/
    echo 'SQL files deleted.'
    echo ''
else
    echo 'SQL files kept stored in /tmp/sp-shapefile-sql/ :'
    echo ''
    ls -la /tmp/sp-shapefile-sql
    echo ''
fi
