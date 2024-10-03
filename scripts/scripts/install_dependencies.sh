#!/bin/bash

# Update package index
apt-get update -y

# Install Apache if not already installed
if ! dpkg -l | grep -qw apache2; then
    apt-get install apache2 -y
fi

# Enable and start Apache service
systemctl enable apache2
systemctl start apache2

