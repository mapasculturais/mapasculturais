#!/bin/bash
echo ''
echo "MAPAS CULTURAIS: Ubuntu-vivid-based distributions install script"
echo ''
echo 'Please use this script as a document for installing MAPAS CULTURAIS dependencies in your distribution:'
echo ''
echo 'git ssh openssh-server curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts'

echo 'MAPASCULTURAIS: The installer will add PostgreSQL APT repositories:'
echo ''
sudo ../scripts/apt.postgresql.org.sh

echo "MAPAS CULTURAIS: Installing Dependencies."
echo ''

sudo add-apt-repository ppa:rwky/nodejs

sudo apt-get update

sudo apt-get -y install git ssh openssh-server curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgresql-9.4-postgis-2.1 postgresql-9.4-postgis-2.1-scripts ruby-sass nodejs

sudo npm install -g uglify-js uglifycss autoprefixer

sudo sed -i "s/local   all             all                                     peer/local   all             all                                     password/g" /etc/postgresql/9.4/main/pg_hba.conf

sudo sed -i "s/host    all             all             127.0.0.1\/32            ident/host    all             all             127.0.0.1\/32            password/g" /etc/postgresql/9.4/main/pg_hba.conf

sudo service postgresql restart

echo "MAPAS CULTURAIS: Checking Composer Dependency Manager for PHP"
if ! type composer.phar 2>/dev/null; then
    echo "MAPAS CULTURAIS: Installing Composer"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer.phar
fi

echo "MAPAS CULTURAIS: Getting Dependencies using Composer"
composer.phar --working-dir=../src/protected install --prefer-dist

echo "MAPAS CULTURAIS: Setting up mapasculturais PostgreSQL Database"
../scripts/install.sh


echo "MAPAS CULTURAIS: Please edit src/protected/application/conf/config.php"
cp ../src/protected/application/conf/config.template.php ../src/protected/application/conf/config.php
echo ""

echo "MAPAS CULTURAIS: Running initial-configuration.sh"
../scripts/initial-configuration.sh
echo ""

echo "MAPAS CULTURAIS: Install Finished"
echo ""

