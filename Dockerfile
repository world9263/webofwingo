FROM php:8.1-apache

# Install MariaDB (MySQL compatible) server and client
RUN apt-get update && apt-get install -y mariadb-server mariadb-client && rm -rf /var/lib/apt/lists/*

# Install MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Disable competing MPM modules by removing symlinks
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf
RUN rm -f /etc/apache2/mods-enabled/mpm_worker.load
RUN rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Enable prefork MPM and mod_rewrite
RUN ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
RUN ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
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
