FROM php:8.2.22-fpm

# Set working directory
WORKDIR /var/www

# Create user with UID 1000
RUN useradd -u 1000 -m developer

# Install system dependencies, Chrome, and Node.js 20
RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev \
    zip \
    git \
    gnupg \
    libicu-dev \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install -y \
    nodejs \
    && npm install -g yarn

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip intl

# Install Composer 2
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application directory contents
COPY --chown=1000:1000 ../../ /var/www
# Ensure proper permissions
RUN chown -R 1000:1000 /var/www \
    && find /var/www -type d -exec chmod 755 {} \; \
    && find /var/www -type f -exec chmod 644 {} \;

# Switch to non-root user
USER developer

# Start cron and PHP-FPM together
CMD php-fpm

# Expose port 9000
EXPOSE 9000
