FROM php:8.1-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy public_html files into apache default web directory
COPY public_html/ /var/www/html/

# Copy startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 80
EXPOSE 80

# Execute the startup script
CMD ["/usr/local/bin/start.sh"]
