#!/bin/bash

# Disable competing Apache MPM modules to prevent crash
echo "Configuring Apache MPM modules..."
a2dismod mpm_event || true
a2dismod mpm_worker || true
a2enmod mpm_prefork || true

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

# Initialize database only if it doesn't already exist
if ! mysql -u root -e "use aviatorp_demo1" >/dev/null 2>&1; then
    echo "Initializing Database..."
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS aviatorp_demo1;"
    mysql -u root -e "CREATE USER 'aviatorp_demo1'@'localhost' IDENTIFIED BY 'aviatorp_demo1';"
    mysql -u root -e "GRANT ALL PRIVILEGES ON aviatorp_demo1.* TO 'aviatorp_demo1'@'localhost';"
    mysql -u root -e "FLUSH PRIVILEGES;"

    # Import the schema
    if [ -f /tmp/indianwatchdogs.sql ]; then
        echo "Importing database dump..."
        mysql -u root aviatorp_demo1 < /tmp/indianwatchdogs.sql
        echo "Database import complete!"
    else
        echo "Database dump not found at /tmp/indianwatchdogs.sql"
    fi
else
    echo "Database 'aviatorp_demo1' already initialized, skipping setup."
fi

# Run Apache in foreground
echo "Starting Apache..."
apache2-foreground
