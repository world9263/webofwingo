#!/bin/bash

# Disable competing Apache MPM modules to prevent crash
echo "Configuring Apache MPM modules..."
a2dismod mpm_event || true
a2dismod mpm_worker || true
a2enmod mpm_prefork || true

# Start MariaDB service using standard system script
echo "Starting MariaDB..."
service mariadb start

# Run Apache in foreground
echo "Starting Apache..."
apache2-foreground
