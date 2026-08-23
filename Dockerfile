FROM php:8.1-apache

# Install MariaDB (MySQL compatible) server and client without recommends
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

# Copy startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 80
EXPOSE 80

# Execute the startup script
CMD ["/usr/local/bin/start.sh"]
