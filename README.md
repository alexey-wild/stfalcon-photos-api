[![SensioLabsInsight](https://insight.sensiolabs.com/projects/190f016a-eb4c-4c73-9f45-a9efd8f20c32/big.png)](https://insight.sensiolabs.com/projects/190f016a-eb4c-4c73-9f45-a9efd8f20c32)

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
