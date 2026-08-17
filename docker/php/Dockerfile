FROM php:8.3-fpm-alpine

# Arguments for UID/GID to match host user
ARG UID=1000
ARG GID=1000

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    icu-dev \
    postgresql-dev \
    linux-headers \
    oniguruma-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        intl \
        zip \
        bcmath \
        opcache \
        mbstring \
        pcntl

# Install Redis extension via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# PHP configuration for production-ready defaults
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Custom PHP configuration
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# OPcache configuration for performance
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create non-root user for security
RUN addgroup -g ${GID} appuser \
    && adduser -u ${UID} -G appuser -s /bin/sh -D appuser

# Entrypoint script for queue-worker and scheduler
# Aguarda PostgreSQL + migrations antes de iniciar o processo
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Set ownership
RUN chown -R appuser:appuser /var/www/html

# Switch to non-root user
USER appuser

EXPOSE 9000

CMD ["php-fpm"]
