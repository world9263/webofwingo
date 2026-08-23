#!/bin/bash

# Ensure runtime directories exist with correct permissions
mkdir -p /var/run/mysqld
chown -R mysql:mysql /var/run/mysqld
chown -R mysql:mysql /var/lib/mysql

# Initialize database directories if needed
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MariaDB data directory..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql
fi

# Start MariaDB in the background
echo "Starting MariaDB..."
mysqld_safe --user=mysql --datadir=/var/lib/mysql &

# Wait for MariaDB to start up
echo "Waiting for MariaDB to respond..."
for i in {1..30}; do
    if mysqladmin ping -u root --silent; then
        break
    fi
    echo "Waiting..."
    sleep 1
done

# Create database, set user permissions, and import sql
echo "Initializing Database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS aviatorp_demo1;"
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'aviatorp_demo1';"
mysql -u root -e "GRANT ALL PRIVILEGES ON aviatorp_demo1.* TO 'root'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# Import the schema
if [ -f /tmp/indianwatchdogs.sql ]; then
    echo "Importing database dump..."
    mysql -u root -p'aviatorp_demo1' aviatorp_demo1 < /tmp/indianwatchdogs.sql
    echo "Database import complete!"
else
    echo "Database dump not found at /tmp/indianwatchdogs.sql"
fi

# Run Apache in foreground
echo "Starting Apache..."
apache2-foreground
