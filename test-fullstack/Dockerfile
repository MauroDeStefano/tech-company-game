# Use PHP 8.3 FPM (Laravel 11 requirement)
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies including Node.js
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
    postgresql-client \
    ca-certificates \
    procps \
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

# Configure PHP-FPM per health check
RUN echo "pm.status_path = /ping" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "ping.path = /ping" >> /usr/local/etc/php-fpm.d/www.conf

# Create user for Laravel (IMPORTANTE: usa l'UID dell'host)
RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

# Copy startup script
COPY docker/scripts/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# === QUESTA È LA PARTE CRUCIALE ===
# Copy application files PRIMA di cambiare utente
COPY --chown=www:www . /var/www

# Crea le directory necessarie con i permessi corretti
RUN mkdir -p /var/www/vendor \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/api-docs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www:www /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Ora switcha all'utente www per le operazioni di build
USER www

# Install dependencies SOLO se composer.json esiste e DOPO aver settato i permessi
RUN if [ -f composer.json ]; then \
        composer install --optimize-autoloader --no-dev --no-scripts; \
    fi

# Install npm dependencies SOLO se package.json esiste
RUN if [ -f package.json ]; then \
        npm install --production; \
    fi

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Default command
CMD ["/usr/local/bin/startup.sh"]