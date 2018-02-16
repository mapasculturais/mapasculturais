echo "Executando config_db.sh..."

#psql -c "CREATE USER mapas"
#createdb --owner mapas mapas
#psql -d mapas -c "CREATE EXTENSION postgis;"

psql -U mapas -d mapas -c "CREATE EXTENSION unaccent;"
psql -U mapas -f /srv/mapas/db/schema.sql
psql -U mapas -f /srv/mapas/db/initial-data.sql
