FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libsqlite3-dev \
    nginx supervisor nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy package files and install Node dependencies + build
COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
RUN npm ci && npm run build && rm -rf node_modules

# Copy the rest of the application
COPY . .

# Re-run composer scripts (post-autoload-dump)
RUN composer dump-autoload --optimize

# Create required directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p database

# Create SQLite database file
RUN touch database/database.sqlite

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port (Render uses $PORT)
EXPOSE 10000

ENTRYPOINT ["/entrypoint.sh"]
