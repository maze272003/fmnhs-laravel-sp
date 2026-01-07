# Stage 1: Build the frontend (Vite)
FROM node:20-alpine AS build-stage
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: App and PHP setup
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    wget \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    mariadb-client

RUN docker-php-ext-install pdo_mysql mbstring bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application code
COPY . .
# Copy built assets from Stage 1
COPY --from=build-stage /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Setup permissions
# Mahalaga ito para hindi mag-fail ang storage:link at file uploads 
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx configuration
COPY .docker/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

# --- STARTUP COMMANDS ---
# 1. migrate:fresh --seed (Warning: Rebuilds the DB)
# 2. storage:link (Creates the symlink) 
# 3. Starts PHP-FPM and Nginx
CMD sh -c "php artisan migrate:fresh --seed --force && \
           php artisan storage:link && \
           php-fpm -D && \
           nginx -g 'daemon off;'"