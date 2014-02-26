#!/bin/bash

sudo -u postgres dropdb mapasculturais
sudo -u postgres dropuser mapasculturais

sudo -u postgres createuser mapasculturais
sudo -u postgres createdb --owner mapasculturais mapasculturais

sudo -u postgres psql -d mapasculturais -c 'CREATE EXTENSION postgis;'
sudo -u postgres psql -d mapasculturais -U mapasculturais -f db/schema.sql
sudo -u postgres psql -d mapasculturais -U mapasculturais -f db/initial-data.sql
