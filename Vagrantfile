# -*- mode: ruby -*-
# vi: set ft=ruby :

# ---------------------------------------
# https://github.com/EloGank/lol-commands
# ---------------------------------------

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # All Vagrant configuration is done here. The most common configuration
  # options are documented and commented below. For a complete reference,
  # please see the online documentation at vagrantup.com.

  config.vm.box = "ubuntu/trusty64"
  config.vm.box_check_update = true
  config.vm.network "private_network", ip: "192.168.100.10"
  config.vm.provision :shell, :path => ".vagrant_bootstrap/bootstrap.sh"
  
  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--memory", 1024]
    v.customize ["modifyvm", :id, "--name", "EloGank - LoL Replay Downloader"]
  end  
end
