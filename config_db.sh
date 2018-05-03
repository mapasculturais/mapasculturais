#!/bin/bash
set -e
set -u
echo "Executando config_db.sh..."

#psql -c "CREATE USER mapas"
#createdb --owner mapas mapas
#psql -d mapas -c "CREATE EXTENSION postgis;"
echo "Executando config for database $POSTGRES_DB:"
psql -U mapas -d mapas -c "CREATE EXTENSION unaccent;"
psql -U mapas -f /srv/mapas/db/schema.sql
psql -U mapas -f /srv/mapas/db/initial-data.sql

echo "Executando config for database $POSTGRES_DB_TEST:"
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL	    
	    DROP DATABASE IF EXISTS $POSTGRES_DB_TEST;
        CREATE DATABASE $POSTGRES_DB_TEST;
	    GRANT ALL PRIVILEGES ON DATABASE $POSTGRES_DB_TEST TO $POSTGRES_USER;
EOSQL
psql -U $POSTGRES_USER -d $POSTGRES_DB_TEST -f /srv/mapas/db/db-test.sql