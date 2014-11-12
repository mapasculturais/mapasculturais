#!/bin/bash


PGUSER=postgres
DBNAME=mapasculturais
DBUSER=mapasculturais
DBPASS=mapasculturais

sudo -u ${PGUSER} dropdb --if-exists ${DBNAME}
sudo -u ${PGUSER} dropuser --if-exists ${DBUSER}

sudo -u ${PGUSER} psql -d postgres -c "CREATE USER ${DBUSER} WITH PASSWORD '${DBPASS}';"
sudo -u ${PGUSER} createdb --owner ${DBUSER} ${DBNAME}

sudo -u ${PGUSER} psql -d ${DBNAME} -c 'CREATE EXTENSION postgis;'
sudo -u ${PGUSER} psql -d ${DBNAME} -c 'CREATE EXTENSION unaccent;'

sudo PGPASSWORD=${DBPASS} -u ${PGUSER} psql -d ${DBNAME} -U ${DBUSER} -h 127.0.0.1 -f ../db/schema.sql
sudo PGPASSWORD=${DBPASS} -u ${PGUSER} psql -d ${DBNAME} -U ${DBUSER} -h 127.0.0.1 -f ../db/initial-data.sql

./db-update.sh
