#!/bin/bash

echo ''
echo "MAPAS CULTURAIS: Ubuntu-trusty-based distributions install script"
echo ''
echo 'Please use refer to https://github.com/hacklabr/mapasculturais/blob/master/doc/deploy-ubuntu-14.04.md as a document for installing MAPAS CULTURAIS dependencies in your distribution:'
echo ''

echo "MAPAS CULTURAIS: Installing Dependencies."
echo ''


# dependencias diversas
sudo apt-get install -y git curl 

# instalando referências para a última versão do node
curl -sL https://deb.nodesource.com/setup_4.x | sudo -E bash -

sudo apt-get install -y nodejs ruby

# postgresql e postgis
sudo apt-get install -y postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts

# php, php-fpm e extensoes do php utiliazdas no sistema
sudo apt-get install -y php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc php5-fpm

sudo update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

sudo npm install -g uglify-js uglifycss postcss-cli autoprefixer autoprefixer-cli

sudo gem install sass

# Instalando o gerenciador de dependencias do PHP Composer
echo "MAPAS CULTURAIS: Checking Composer Dependency Manager for PHP"
if ! type composer.phar 2>/dev/null; then
	echo "MAPAS CULTURAIS: Installing Composer"
	curl -sS https://getcomposer.org/installer | php
	sudo mv composer.phar /usr/local/bin/composer.phar
fi


