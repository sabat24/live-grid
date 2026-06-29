FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# Create user with UID 1000
RUN useradd -u 1000 -m developer

# System libraries for Playwright (Chromium/Firefox/WebKit). WebKit needs GStreamer's
# "bad" plugin set (libgstcodecparsers) — "bad" is upstream GStreamer naming, not a security label.
RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev \
    zip \
    git \
    gnupg \
    libicu-dev \
    ca-certificates \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libcups2 \
    libdbus-1-3 \
    libdrm2 \
    libgbm1 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libxcomposite1 \
    libxdamage1 \
    libxfixes3 \
    libxkbcommon0 \
    libxrandr2 \
    libgtk-4-1 \
    libgraphene-1.0-0 \
    libxslt1.1 \
    libevent-2.1-7 \
    libopus0 \
    libgstreamer1.0-0 \
    gstreamer1.0-plugins-base \
    libgstreamer-plugins-base1.0-0 \
    libgstreamer-plugins-bad1.0-0 \
    gstreamer1.0-plugins-bad \
    flite \
    libwebpdemux2 \
    libwebpmux3 \
    libavif16 \
    libharfbuzz-icu0 \
    libmanette-0.2-0 \
    libenchant-2-2 \
    libhyphen0 \
    libsecret-1-0 \
    libwoff1 \
    libx264-164 \
    libjxl0.11 \
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

CMD php-fpm

EXPOSE 9000
