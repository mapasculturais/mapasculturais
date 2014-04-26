#!/bin/bash
echo ''
echo "MAPAS CULTURAIS: Debian-Based Distros Install Script"
echo ''
echo 'Please use this script as a document for installing MAPAS CULTURAIS dependencies in your distribution:'
echo ''
echo 'curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts'
echo ''1
echo -n "MAPAS CULTURAIS: Press Enter to continue, or Ctrl-C to abort."
read enter

echo 'MAPASCULTURAIS: The installer will add PostgreSQL APT repositories:'
echo ''
sudo ./apt.postgresql.org.sh

echo "MAPAS CULTURAIS: Installing Dependencies. Please confirm."
echo ''

sudo apt-get install curl apache2 php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts

echo "MAPAS CULTURAIS: Checking Composer Dependency Manager for PHP"
if ! type composer.phar 2>/dev/null; then
    echo "MAPAS CULTURAIS: Installing Composer"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer.phar
fi

echo "MAPAS CULTURAIS: Getting Dependencies using Composer"
composer.phar --working-dir=../src/protected install --prefer-dist

echo "MAPAS CULTURAIS: Setting up mapasculturais PostgreSQL Database"
./install.sh

echo "MAPAS CULTURAIS: Running initial-configuration.sh"
./initial-configuration.sh
echo ""

echo "MAPAS CULTURAIS: Please edit src/protected/application/conf/config.php"
cp $PWD/src/protected/application/conf/config.template.php $PWD/src/protected/application/conf/config.php
echo ""

echo "MAPAS CULTURAIS: Install Finished"
echo ""

echo "The application can be tested running in http://localhost:8000
By the PHP built-in web server you can use with command
php -S 0.0.0.0:8000 -t ../src ../src/router.php &

If you want to serve in other address IP change base.url in src/protected/application/conf/config.php
"
php -S 0.0.0.0:8000 -t ../src ../src/router.php &