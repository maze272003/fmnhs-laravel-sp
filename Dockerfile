# Stage 1: Build the Frontend (Vite/Node)
FROM node:20-alpine AS build-stage
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Build the Backend (PHP/Laravel)
FROM php:8.3-fpm-alpine

# Install system dependencies
# We add 'supervisor' for process management and 'linux-headers' for pcntl extension
RUN apk add --no-cache \
    nginx \
    supervisor \
    wget \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    mariadb-client \
    linux-headers

# Install PHP extensions
# 'pcntl' is REQUIRED for WebSocket servers to run correctly
RUN docker-php-ext-install pdo_mysql mbstring bcmath gd pcntl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=build-stage /app/public/build ./public/build

# Install PHP dependencies (Production mode)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Setup Permissions
# Ensures Laravel can write to logs and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Configuration Files
# 1. Copy your Nginx config (Ensure this file exists in your project at .docker/nginx.conf)
COPY .docker/nginx.conf /etc/nginx/nginx.conf

# 2. Copy the Supervisor config (The file below)
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set Environment Variables for the WebSocket Server
ENV CONFERENCE_SIGNALING_BIND_HOST=0.0.0.0
ENV CONFERENCE_SIGNALING_PORT=6001

# Expose ports: 80 (Web) and 6001 (WebSocket)
EXPOSE 80 6001

# Start Supervisor
# This will run Nginx, PHP-FPM, and your WebSocket server simultaneously
CMD ["/bin/sh", "-c", "php artisan storage:link && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]