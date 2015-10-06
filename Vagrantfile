# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

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

    config.vm.provision "shell", path: "./scripts/install_vagrant.sh"

    config.vm.provision "shell", path: "./scripts/configure_vagrant.sh", args: "/vagrant",
            privileged: false

    config.vm.provision "shell", inline: $serviceup,
            run: "always",
            privileged: false

    #config.vm.provider :virtualbox do |vb|
    #  vb.gui = true
    #end
end
