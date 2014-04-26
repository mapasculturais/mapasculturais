#!/bin/bash

DBPASS=mapasculturais

tar xC /tmp -f ../db/sp-shapefile-sql.tar.xz

sudo PGPASSWORD=${DBPASS} -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -f /tmp/sp-shapefile-sql/sp_regiao.sql
sudo PGPASSWORD=${DBPASS} -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -f /tmp/sp-shapefile-sql/sp_distrito.sql
sudo PGPASSWORD=${DBPASS} -u postgres psql -U mapasculturais -d mapasculturais -h 127.0.0.1 -f /tmp/sp-shapefile-sql/sp_subprefeitura.sql

rm -rf /tmp/sp-shapefile-sql/
