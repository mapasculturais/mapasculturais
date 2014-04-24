#!/bin/bash
echo ''
echo "MAPAS CULTURAIS: Debian-Based Distros Install Script"
echo ''
echo 'Please use this script as a document for installing MAPAS CULTURAIS dependencies:'
echo ''
echo 'curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgis-scripts'
echo ''

read -p "Do you want to exit and install these dependencies manually? (Y/n)
" -n 1 -r
echo    # (optional) move to a new line
if [[ ! $REPLY =~ ^[Nn]$ ]]
then
    echo 'Exiting'
    echo ''
    exit 1
fi

read -p "MAPAS CULTURAIS: Do you want to install dependencies from your current distribution repositories? Type N to add PostgreSQL APT repositories. (Y/n)
" -n 1 -r
echo    # (optional) move to a new line
if [[ ! $REPLY =~ ^[Nn]$ ]]
then
    echo 'MAPAS CULTURAIS: Using current APT repositories.'
    echo ''
else
    echo "MAPAS CULTURAIS: Adding PostgreSQL APT Repository"
    sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt/ wheezy-pgdg main" > /etc/apt/sources.list.d/postgresql.list'
    wget --quiet -O - http://apt.postgresql.org/pub/repos/apt/ACCC4CF8.asc | sudo apt-key add -
    echo ''
fi


read -p "MAPAS CULTURAIS: Install without updating APT? (Y/n)
" -n 1 -r
echo    # (optional) move to a new line
if [[ ! $REPLY =~ ^[Nn]$ ]]
then
    echo "MAPAS CULTURAIS: Using current package list"
    echo ''
else
    echo "MAPAS CULTURAIS: Updating APT"
    sudo apt-get update
    echo ''
fi

echo "MAPAS CULTURAIS: Installing Dependencies"
sudo apt-get install -y curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib
sudo apt-get install -y --install-suggests postgis

echo "MAPAS CULTURAIS: Checking Composer Dependency Manager for PHP"
if ! type composer.phar 2>/dev/null; then
    echo "MAPAS CULTURAIS: Installing Composer"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer.phar
fi

echo "MAPAS CULTURAIS: Getting Dependencies using Composer"
composer.phar --working-dir=../src/protected install

echo "MAPAS CULTURAIS: Setting up mapasculturais PostgreSQL Database"
./install.sh

echo "MAPAS CULTURAIS: Please read initial-configuration.sh and run what applies to your system."

echo "MAPAS CULTURAIS: Install Finished"