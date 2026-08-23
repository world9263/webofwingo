#!/bin/bash

# Disable competing Apache MPM modules to prevent crash
echo "Configuring Apache MPM modules..."
a2dismod mpm_event || true
a2dismod mpm_worker || true
a2enmod mpm_prefork || true

# Start MariaDB service using standard system script
echo "Starting MariaDB..."
service mariadb start

# Wait for MariaDB to start up
echo "Waiting for MariaDB to respond..."
for i in {1..30}; do
    if mysqladmin ping --silent; then
        break
    fi
    echo "Waiting..."
    sleep 1
done

# Initialize database, user, and import dump
echo "Initializing Database..."
mysql -e "CREATE DATABASE IF NOT EXISTS aviatorp_demo1;"
mysql -e "CREATE USER 'aviatorp_demo1'@'localhost' IDENTIFIED BY 'aviatorp_demo1';"
mysql -e "GRANT ALL PRIVILEGES ON aviatorp_demo1.* TO 'aviatorp_demo1'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Import the schema (using --force to ignore duplicate table errors)
if [ -f /tmp/indianwatchdogs.sql ]; then
    echo "Importing database dump..."
    mysql --force aviatorp_demo1 < /tmp/indianwatchdogs.sql
    echo "Database import complete!"
else
    echo "Database dump not found at /tmp/indianwatchdogs.sql"
fi

# Run Apache in foreground
echo "Starting Apache..."
apache2-foreground
