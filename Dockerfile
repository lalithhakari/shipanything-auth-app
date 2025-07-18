# Dockerfile for Auth Service
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    curl-dev \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    postgresql-dev \
    redis \
    autoconf \
    gcc \
    g++ \
    make \
    librdkafka-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    gd \
    xml \
    curl \
    bcmath \
    pcntl \
    posix

# Install igbinary first (Redis dependency)
RUN pecl install igbinary \
    && docker-php-ext-enable igbinary

# Install Redis PHP extension
RUN pecl install --configureoptions 'enable-redis-igbinary="yes"' redis \
    && docker-php-ext-enable redis

# Install rdkafka PHP extension for Kafka support
RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Nginx
RUN apk add --no-cache nginx bash

# Set working directory
WORKDIR /var/www/html

# Create directory structure (application code will be mounted via volume)
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p bootstrap/cache

# Ensure .env exists (will be handled at runtime)
# RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# Set base permissions for directories that will be mounted
RUN chown -R www-data:www-data /var/www/html

# Copy Nginx configuration
COPY ./microservices/auth-app/docker/nginx.conf /etc/nginx/nginx.conf

# Copy PHP-FPM configuration
COPY ./microservices/auth-app/docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy PHP configuration for development (disable OPcache for hot reload)
COPY ./microservices/auth-app/docker/php.ini /usr/local/etc/php/conf.d/99-development.ini


# Copy startup scripts
COPY ./microservices/auth-app/docker/start.sh /start.sh
RUN mkdir -p /scripts
COPY scripts/laravel-manager.sh /scripts/laravel-manager.sh
RUN chmod +x /start.sh /scripts/laravel-manager.sh

EXPOSE 80

CMD ["/start.sh"]
