#!/bin/bash

# Physically delete competing Apache MPM configurations to prevent crash
echo "Configuring Apache MPM modules..."
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load || true
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf || true

# Start MariaDB service
echo "Starting MariaDB..."
service mariadb start

# Wait for MariaDB to start up
echo "Waiting for MariaDB to respond..."
for i in {1..30}; do
    if mysqladmin --socket=/var/run/mysqld/mysqld.sock ping --silent; then
        break
    fi
    echo "Waiting..."
    sleep 1
done

# Initialize database, user, and import dump (forcing socket to bypass TCP auth locks)
echo "Initializing Database..."
mysql --socket=/var/run/mysqld/mysqld.sock -e "CREATE DATABASE IF NOT EXISTS aviatorp_demo1;"
mysql --socket=/var/run/mysqld/mysqld.sock -e "CREATE USER IF NOT EXISTS 'aviatorp_demo1'@'localhost' IDENTIFIED BY 'aviatorp_demo1';"
mysql --socket=/var/run/mysqld/mysqld.sock -e "GRANT ALL PRIVILEGES ON aviatorp_demo1.* TO 'aviatorp_demo1'@'localhost';"
mysql --socket=/var/run/mysqld/mysqld.sock -e "FLUSH PRIVILEGES;"

# Import the schema (using --force to ignore duplicate table errors)
if [ -f /tmp/indianwatchdogs.sql ]; then
    echo "Importing database dump..."
    mysql --socket=/var/run/mysqld/mysqld.sock --force aviatorp_demo1 < /tmp/indianwatchdogs.sql
    echo "Database import complete!"
else
    echo "Database dump not found at /tmp/indianwatchdogs.sql"
fi

# Run Apache in foreground
echo "Starting Apache..."
apache2-foreground
