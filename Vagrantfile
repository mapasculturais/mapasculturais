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
    config.vm.network "forwarded_port", guest: 8000, host: 3132


    # backing providers for Vagrant. These expose provider-specific options.
    config.vm.provider :virtualbox do |vb|

        # Set VM memory size
        vb.customize ["modifyvm", :id, "--memory", "512"]

        # these 2 commands massively speed up DNS resolution, which means outbound
        # connections don't take forever (eg the WP admin dashboard and update page)
        vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
        vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]

    end

    config.landrush.enabled = true
    config.landrush.tld = 'fdev'

    config.vm.network "private_network", ip: "192.168.50.5"
    config.vm.hostname = "mapa.fdev"
    config.hostsupdater.remove_on_suspend = true

    # config.landrush.host 'mapa.local', '192.168.50.5'




    config.vm.provision "shell", path: "./scripts/install_vagrant.sh"

    config.vm.provision "shell", path: "./scripts/configure_vagrant.sh", args: "/vagrant",
            privileged: false

    # config.vm.provision "shell", inline: $serviceup,
    #         run: "always",
            # privileged: false

    #config.vm.provider :virtualbox do |vb|
    #  vb.gui = true
    #end
end