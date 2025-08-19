FROM php:8.3-cli

# Set working directory
WORKDIR /app

# Copy the current directory contents into the container at /app
# COPY . /app

# Update the package list and install dependencies necessary to build PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    git \
	procps \
	file \
	gettext \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    gd \
    mbstring

RUN pecl install xdebug && docker-php-ext-enable xdebug
# Clean up to reduce the image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
