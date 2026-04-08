FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    curl \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite
RUN a2enmod rewrite

# Tell Apache to serve Laravel from /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Install PHP dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Set storage and cache permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy and make startup script executable
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
