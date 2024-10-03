#!/bin/bash

# Remove the existing phpunit.xml file if it exists
if [ -f /var/www/html/civilbook/phpunit.xml ]; then
    rm /var/www/html/civilbook/phpunit.xml
fi

