# Use official PHP with Apache
FROM php:8.2-apache

# Install common extensions (adjust to your needs)
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libjpeg-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install gd pdo pdo_mysql

# Enable Apache rewrite
RUN a2enmod rewrite

# If you have composer files, copy composer and run install (optional)
# This copies composer from official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy only composer files to install dependencies first (speeds builds)
COPY composer.json composer.lock* /var/www/html/
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi

# Copy application files
COPY . /var/www/html

# Ensure www-data owns files
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80

# Official apache entrypoint
CMD ["apache2-foreground"]
