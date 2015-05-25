# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

$install = <<SCRIPT

cd /vagrant/scripts

./provision.sh

./compile-sass.sh

php -S 0.0.0.0:8000 -t ../src ../src/router.php &

echo "All done! Call http://127.0.0.1:8000 in your browser and be happy."

SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

    # Every Vagrant virtual environment requires a box to build off of.
    config.vm.box = "ubuntu/vivid64"
    config.vm.network "forwarded_port", guest: 8000, host: 8000

    config.vm.provision "shell", inline: $install

    #config.vm.provider :virtualbox do |vb|
    #  vb.gui = true
    #end
end
