FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    netcat-openbsd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*




# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath xml

# Install Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/

# Copy existing application directory contents
COPY . .

# Install Laravel dependencies
RUN composer install
# Clear cache and generate key
RUN php artisan key:generate

# Copy entrypoint script
COPY ./docker/app/app-entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
# Expose port 9000
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
