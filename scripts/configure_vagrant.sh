#!/bin/bash

cd $1

echo "MAPAS CULTURAIS: Getting Dependencies using Composer"
composer.phar --working-dir=src/protected install --prefer-dist
echo ""

echo "MAPAS CULTURAIS: Setting up mapasculturais PostgreSQL Database"

sudo -u postgres createuser -d vagrant

# Agora vamos criar a base de dados para a aplicacao com o mesmo nome do usuario

echo "MAPAS CULTURAIS: Creating Database..."
createdb mapas
echo ""

echo "MAPAS CULTURAIS: Creating Extensions..."
sudo -u postgres psql -d mapas -c "CREATE EXTENSION postgis;"
sudo -u postgres psql -d mapas -c "CREATE EXTENSION unaccent;"
echo ""

echo "MAPAS CULTURAIS: Database schema, db-update and initial data."
# TODO: try pg_dump --no-owner --no-acl to generate schema.sql
psql -d mapas -f db/schema.sql
./scripts/db-update.sh
psql -d mapas -f db/initial-data.sql
echo ""

echo "MAPAS CULTURAIS: Please edit src/protected/application/conf/config.php"
cp src/protected/application/conf/config.dev.php src/protected/application/conf/config.php
echo ""

echo "MAPAS CULTURAIS: Running initial-configuration.sh"
./scripts/initial-configuration.sh
echo ""

echo "MAPAS CULTURAIS: Compiling sass and minifying js..."
./scripts/compile-sass.sh

echo "MAPAS CULTURAIS: Install Finished"
echo ""

