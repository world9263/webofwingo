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

# Self-healing mysql connection helper
run_mysql() {
    if mysql -u root -p'aviatorp_demo1' -e "select 1" >/dev/null 2>&1; then
        mysql -u root -p'aviatorp_demo1' "$@"
    else
        mysql -u root "$@"
    fi
}

# Initialize database and root credentials safely
echo "Initializing Database..."
run_mysql -e "CREATE DATABASE IF NOT EXISTS aviatorp_demo1;"
run_mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'aviatorp_demo1';"
run_mysql -e "GRANT ALL PRIVILEGES ON aviatorp_demo1.* TO 'root'@'localhost';"
run_mysql -e "FLUSH PRIVILEGES;"

# Import the schema (using --force to ignore duplicate table errors)
if [ -f /tmp/indianwatchdogs.sql ]; then
    echo "Importing database dump..."
    run_mysql --force aviatorp_demo1 < /tmp/indianwatchdogs.sql
    echo "Database import complete!"
else
    echo "Database dump not found at /tmp/indianwatchdogs.sql"
fi

# Run Apache in foreground
echo "Starting Apache..."
apache2-foreground
