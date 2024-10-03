#!/bin/bash

# Change ownership to the Apache user (www-data)
chown -R ubuntu:ubuntu /var/www/html

# Set permissions to allow Apache to read the files
chmod -R 755 /var/www/html

