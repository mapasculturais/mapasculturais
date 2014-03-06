#!/bin/bash

echo "MapasCulturais Debian-Based Distros Install Script"
echo "Adding PostgreSQL APT Repository"
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt/ precise-pgdg main" > /etc/apt/sources.list.d/postgresql.list'
wget --quiet -O - http://apt.postgresql.org/pub/repos/apt/ACCC4CF8.asc | sudo apt-key add -

echo "Updating APT"
sudo apt-get update

echo "Installing Packages"
sudo apt-get install -y curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib

sudo apt-get install -y --install-suggests postgis

echo "Checking Composer Dependency Manager for PHP"
if ! type composer.phar 2>/dev/null; then
    echo "Installing Composer"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer.phar
fi

echo "Getting Dependencies using Composer"
composer.phar --working-dir=../src/protected install

echo "Setting up MapasCulturais PostgreSQL Database"
./install.sh