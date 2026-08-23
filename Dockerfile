FROM php:8.1-apache

# Install MariaDB (MySQL compatible) server and client
RUN apt-get update && apt-get install -y --no-install-recommends mariadb-server mariadb-client && rm -rf /var/lib/apt/lists/*

# Install MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Disable competing MPM modules
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork || true
RUN a2enmod rewrite

# Copy public_html files into apache default web directory
COPY public_html/ /var/www/html/

# Copy database dump to temp directory
COPY indianwatchdogs.sql /tmp/indianwatchdogs.sql

# Initialize database, user, and import dump at BUILD TIME in a single line (no backslashes)
RUN mkdir -p /var/run/mysqld && chown -R mysql:mysql /var/run/mysqld && mysql_install_db --user=mysql --datadir=/var/lib/mysql && mysqld_safe --user=mysql --datadir=/var/lib/mysql --skip-grant-tables & sleep 5 && mysql -e "CREATE DATABASE IF NOT EXISTS aviatorp_demo1;" && mysql -e "CREATE USER 'aviatorp_demo1'@'localhost' IDENTIFIED BY 'aviatorp_demo1';" && mysql -e "GRANT ALL PRIVILEGES ON aviatorp_demo1.* TO 'aviatorp_demo1'@'localhost';" && mysql -e "FLUSH PRIVILEGES;" && mysql aviatorp_demo1 < /tmp/indianwatchdogs.sql && mysqladmin shutdown

# Copy startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 80
EXPOSE 80

# Execute the startup script
CMD ["/usr/local/bin/start.sh"]
