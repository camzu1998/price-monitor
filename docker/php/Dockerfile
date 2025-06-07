FROM php:8.3-fpm

# Arguments for user creation
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=appuser

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user with same UID/GID as host
RUN groupadd -g ${GROUP_ID} ${USER_NAME} \
    && useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/bash ${USER_NAME} \
    && usermod -aG sudo ${USER_NAME} \
    && echo "${USER_NAME} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Set working directory
WORKDIR /var/www/html

# Change ownership of working directory
RUN chown -R ${USER_ID}:${GROUP_ID} /var/www/html

# Switch to our user
USER ${USER_NAME}

# Copy composer files
COPY --chown=${USER_ID}:${GROUP_ID} composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY --chown=${USER_ID}:${GROUP_ID} . .

# Install npm dependencies
RUN npm install

# Set permissions for Laravel directories
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
