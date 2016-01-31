Photos Api
=========

# Requirements
* PHP >=5.6
* MySql >= 5.6.28

# First install
* php -r "readfile('https://getcomposer.org/installer');" | php
* php composer.phar install
* bin/console doctrine:database:create
* bin/console doctrine:schema:create

# Run
* bin/console server:run

# Testing
* wget https://phar.phpunit.de/phpunit.phar
* cp phpunit.xml.dist phpunit.xml
* php phpunit.phar
