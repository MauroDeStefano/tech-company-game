# Use PHP 8.3 FPM (Laravel 11 requirement)
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies including Node.js e tools per healthcheck
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    redis-tools \
    ca-certificates \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for Laravel + PostgreSQL + Redis
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Install Redis PHP extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Create user for Laravel
RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

# Copy startup script as separate file
COPY docker/scripts/startup.sh /usr/local/bin/startup.sh

# Make startup script executable
RUN chmod +x /usr/local/bin/startup.sh

# Copy application files
COPY --chown=www:www . /var/www

# Switch to www user for dependencies
USER www

# Install dependencies
RUN composer install --optimize-autoloader
RUN npm install

# Switch back to root for final setup
USER root

# Set permissions
RUN chown -R www:www /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/api-docs \
    && chown -R www:www /var/www/storage

# Switch to www user
USER www

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Default command
CMD ["/usr/local/bin/startup.sh"]