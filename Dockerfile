# Base image with PHP 8.2 and Apache
FROM php:8.2-apache

# Set working directory inside container
WORKDIR /var/www/html

# Copy Composer files and install dependencies
COPY composer.json composer.lock ./
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev \
    && rm composer-setup.php

# Copy the rest of your project
COPY . .

# Set Apache to serve 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Enable Apache rewrite module (optional, useful for clean URLs)
RUN a2enmod rewrite

# Expose default HTTP port (Render will map $PORT automatically)
EXPOSE 10000

# Start Apache in foreground
CMD ["apache2-foreground"]