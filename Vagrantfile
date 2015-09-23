# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

$install = <<SCRIPT

    #!/bin/bash
    echo ''
    echo "MAPAS CULTURAIS: Ubuntu-trusty-based distributions install script"
    echo ''
    echo 'Please use refer to https://github.com/hacklabr/mapasculturais/blob/master/doc/deploy-ubuntu-14.04.md as a document for installing MAPAS CULTURAIS dependencies in your distribution:'
    echo ''

    echo "MAPAS CULTURAIS: Installing Dependencies."
    echo ''

    sudo apt-get update

    # dependencias diversas
    sudo apt-get install -y git curl nodejs npm ruby ruby-sass #TODO validar o ruby-sass com rafa!

    # postgresql e postgis
    sudo apt-get install -y postgresql postgresql-contrib postgis postgresql-9.3-postgis-2.1 postgresql-9.3-postgis-2.1-scripts

    # php, php-fpm e extensoes do php utiliazdas no sistema
    sudo apt-get install -y php5 php5-gd php5-cli php5-json php5-curl php5-pgsql php-apc php5-fpm

    sudo update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

    sudo npm install -g uglify-js uglifycss postcss-cli autoprefixer

    # Instalando o gerenciador de dependencias do PHP Composer
    echo "MAPAS CULTURAIS: Checking Composer Dependency Manager for PHP"
    if ! type composer.phar 2>/dev/null; then
        echo "MAPAS CULTURAIS: Installing Composer"
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer.phar
    fi

SCRIPT

$configure = <<SCRIPT

    cd /vagrant

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

SCRIPT

$serviceup = <<SCRIPT

    echo "Starting service..."

    php -S 0.0.0.0:8000 -t /vagrant/src /vagrant/src/router.php &

    echo "All done! Call http://127.0.0.1:8000 in your browser and be happy."

SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

    # Every Vagrant virtual environment requires a box to build off of.
    config.vm.box = "ubuntu/trusty64"
    config.vm.network "forwarded_port", guest: 8000, host: 8000

    # config.ssh.username = "mapas"

    config.vm.provision "shell", inline: $install

    config.vm.provision "shell", inline: $configure,
            privileged: false

    config.vm.provision "shell", inline: $serviceup,
            run: "always",
            privileged: false

    #config.vm.provider :virtualbox do |vb|
    #  vb.gui = true
    #end
end
