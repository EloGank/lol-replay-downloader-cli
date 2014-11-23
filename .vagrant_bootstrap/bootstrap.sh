#!/usr/bin/env bash

# ----------------------------------------------------
# https://github.com/EloGank/lol-replay-downloader-cli
# based on : https://github.com/Divi/VagrantBootstrap
# ----------------------------------------------------

# Update the box release repositories
# -----------------------------------
apt-get update


# Essential Packages
# ------------------
apt-get install -y build-essential git-core vim curl


# PHP 5.x (last official release)
# See: https://launchpad.net/~ondrej/+archive/php5
# ------------------------------------------------
apt-get install -y libcli-mod-php5
# Install "add-apt-repository" binaries
apt-get install -y python-software-properties
# Install PHP 5.6.x
# Use "ppa:ondrej/php5-oldstable" or "ppa:ondrej/php5" for old and stable release
add-apt-repository ppa:ondrej/php5-5.6
# Update repositories
apt-get update

# PHP tools
apt-get install -y php5-cli php5-curl php5-mcrypt
# APC (only with PHP < 5.5.0, use the "opcache" if >= 5.5.0)
# apt-get install -y php-apc
# Setting the timezone
sed 's#;date.timezone\([[:space:]]*\)=\([[:space:]]*\)*#date.timezone\1=\2\"UTC\"#g' /etc/php5/cli/php.ini > /etc/php5/cli/php.ini.tmp
mv /etc/php5/cli/php.ini.tmp /etc/php5/cli/php.ini
# Showing error messages
sed 's#display_errors = Off#display_errors = On#g' /etc/php5/cli/php.ini > /etc/php5/cli/php.ini.tmp
mv /etc/php5/cli/php.ini.tmp /etc/php5/cli/php.ini
sed 's#display_startup_errors = Off#display_startup_errors = On#g' /etc/php5/cli/php.ini > /etc/php5/cli/php.ini.tmp
mv /etc/php5/cli/php.ini.tmp /etc/php5/cli/php.ini
sed 's#error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT#error_reporting = E_ALL#g' /etc/php5/cli/php.ini > /etc/php5/cli/php.ini.tmp
mv /etc/php5/cli/php.ini.tmp /etc/php5/cli/php.ini

# Composer
# --------
cd /vagrant
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer install
