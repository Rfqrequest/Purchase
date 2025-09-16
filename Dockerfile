FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install required packages: git, zip, unzip, and other dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Copy project files
COPY composer.json composer.lock ./

# Install Composer and dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev \
    && rm composer-setup.php

# Copy the rest of the code
COPY public/ ./public/
COPY files/ ./files/