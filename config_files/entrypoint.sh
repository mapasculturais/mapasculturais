#!/bin/bash

cmd="$@"

function postgres_ready(){
python << END
import sys
import psycopg2
try:
    conn = psycopg2.connect(dbname="$POSTGRES_DB", user="$POSTGRES_USER", password="$POSTGRES_PASSWORD", host="db")
except psycopg2.OperationalError:
    sys.exit(-1)
sys.exit(0)
END
}

until postgres_ready; do
  >&2 echo "Postgres is unavailable - sleeping"
  sleep 1
done

echo "Postgres is up - continuing..."

echo "Waiting Postgre to get ready - may be necessary to change the sleep time"
sleep 10s


set -e


chown -R mapas:www-data /srv/mapas/mapasculturais/src/protected/application

cp /srv/mapas/mapasculturais/src/protected/application/conf/config.docker.php /srv/mapas/mapasculturais/src/protected/application/conf/config.php


echo "Deploying app"
su - mapas -c "cd /srv/mapas/mapasculturais/scripts/ && ./deploy.sh"

echo "Starting php5-fpm daemon"
service php5-fpm start

echo "Starting nginx daemon"
nginx

exec $cmd
