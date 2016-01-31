Photos Api
=========

# Requirements
1) PHP >=5.6
2) MySql >= 5.6.28

# First install
1) php -r "readfile('https://getcomposer.org/installer');" | php
2) php composer.phar install
3) bin/console doctrine:database:create
4) bin/console doctrine:schema:create

# Run
* bin/console server:run

# Testing
1) wget https://phar.phpunit.de/phpunit.phar
2) cp phpunit.xml.dist phpunit.xml
3) php phpunit.phar