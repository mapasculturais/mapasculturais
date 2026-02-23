# syntax=docker/dockerfile:1
ARG NODE_VERSION=20
ARG PHP_VERSION=8.4

# =============================================================================
# Stage 1: Node.js builder - Compiles frontend assets
# =============================================================================
FROM node:${NODE_VERSION}-alpine AS builder-node

RUN corepack enable && corepack prepare pnpm@latest --activate
RUN npm install -g sass

WORKDIR /build

COPY src/ ./

RUN pnpm install --frozen-lockfile 2>/dev/null || pnpm install
RUN pnpm run build

# Compile SASS for BaseV1 theme
RUN if [ -f themes/BaseV1/assets/css/sass/main.scss ]; then \
  sass themes/BaseV1/assets/css/sass/main.scss:themes/BaseV1/assets/css/main.css --quiet; \
  fi

# Resolve pnpm symlinks for vendor CSS files needed by the PHP AssetManager at runtime.
# These CSS files are served directly from node_modules (not bundled by webpack).
# The packages are deps of modules/Components, so their node_modules live there.
# -L dereferences symlinks so the real files are copied (pnpm uses symlinks to .pnpm store).
RUN mkdir -p /vendor-css/@vuepic/vue-datepicker/dist \
  /vendor-css/floating-vue/dist \
  /vendor-css/leaflet/dist \
  /vendor-css/@vueform/slider/themes \
  /vendor-css/leaflet.markercluster/dist && \
  cp -L modules/Components/node_modules/@vuepic/vue-datepicker/dist/main.css    /vendor-css/@vuepic/vue-datepicker/dist/main.css && \
  cp -L modules/Components/node_modules/floating-vue/dist/style.css             /vendor-css/floating-vue/dist/style.css && \
  cp -L modules/Components/node_modules/leaflet/dist/leaflet.css                /vendor-css/leaflet/dist/leaflet.css && \
  cp -L modules/Components/node_modules/@vueform/slider/themes/default.css      /vendor-css/@vueform/slider/themes/default.css && \
  cp -L modules/Components/node_modules/leaflet.markercluster/dist/MarkerCluster.css         /vendor-css/leaflet.markercluster/dist/MarkerCluster.css && \
  cp -L modules/Components/node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css /vendor-css/leaflet.markercluster/dist/MarkerCluster.Default.css

# Cleanup development files from node_modules
RUN find . -path '*/node_modules/*' -type f \( \
  -name '*.ts' -o -name '*.tsx' -o -name '*.map' -o \
  -name '*.md' -o -name '*.markdown' -o \
  -name 'LICENSE*' -o -name 'CHANGELOG*' -o -name 'README*' -o \
  -name '*.d.ts' -o -name 'tsconfig*' \
  \) -delete 2>/dev/null || true && \
  find . -path '*/node_modules/*' -name '.git' -type d -exec rm -rf {} + 2>/dev/null || true

# =============================================================================
# Stage 2: Composer builder - Installs PHP dependencies
# =============================================================================
FROM php:${PHP_VERSION}-cli-alpine AS builder-composer

RUN apk add --no-cache git unzip

RUN apk add --no-cache \
  libpng \
  libjpeg-turbo \
  freetype \
  libzip && \
  apk add --no-cache --virtual .build-deps \
  $PHPIZE_DEPS \
  libpng-dev \
  libjpeg-turbo-dev \
  freetype-dev \
  libzip-dev && \
  docker-php-ext-configure gd --with-freetype --with-jpeg && \
  docker-php-ext-install -j$(nproc) gd zip && \
  docker-php-ext-enable gd zip && \
  apk del .build-deps && \
  rm -rf /tmp/pear /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /build

COPY composer.json composer.lock ./

ARG COMPOSER_ARGS="--no-dev --optimize-autoloader --no-interaction"
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install ${COMPOSER_ARGS} --no-scripts

# =============================================================================
# Stage 3: Production image - PHP-FPM + Nginx
# =============================================================================
FROM php:${PHP_VERSION}-fpm-alpine AS production

LABEL org.opencontainers.image.title="Mapas"
LABEL org.opencontainers.image.description="Platform for cultural mapping"
LABEL org.opencontainers.image.vendor="RedeMapas"
LABEL org.opencontainers.image.source="https://github.com/redemapas/mapas"
LABEL org.opencontainers.image.licenses="AGPL-3.0"

# Install runtime dependencies (including nginx)
RUN apk add --no-cache \
  sudo \
  bash \
  git \
  imagemagick \
  libpq \
  libzip \
  libpng \
  libjpeg-turbo \
  freetype \
  icu-libs \
  zstd-libs \
  curl \
  nginx

# Install build dependencies for PHP extensions
RUN apk add --no-cache --virtual .build-deps \
  $PHPIZE_DEPS \
  imagemagick-dev \
  postgresql-dev \
  libzip-dev \
  libpng-dev \
  libjpeg-turbo-dev \
  freetype-dev \
  icu-dev \
  linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
  docker-php-ext-install -j$(nproc) \
  pdo_pgsql \
  zip \
  gd \
  intl

# Install APCu
RUN pecl install apcu && \
  docker-php-ext-enable apcu

# Install Imagick
RUN pecl install imagick && \
  docker-php-ext-enable imagick

# Install Redis
# RUN pecl install redis && \
#   docker-php-ext-enable redis

# Cleanup build dependencies
RUN apk del .build-deps && \
  rm -rf /tmp/pear /var/cache/apk/*

# Create www-data user if not exists and setup directories
RUN mkdir -p /var/www/var/DoctrineProxies \
  /var/www/var/logs \
  /var/www/var/private-files \
  /var/www/html/assets \
  /var/www/html/files && \
  chown -R www-data:www-data /var/www

WORKDIR /var/www

# Copy composer dependencies
COPY --from=builder-composer /build/vendor ./vendor/

# Copy application source
COPY --chown=www-data:www-data config ./config/
COPY --chown=www-data:www-data public ./html/
COPY --chown=www-data:www-data health.php /var/www/html/health.php
COPY --chown=www-data:www-data scripts ./scripts/
COPY --chown=www-data:www-data src ./src/

# Copy built frontend assets from Node.js stage
COPY --from=builder-node --chown=www-data:www-data /build/modules/ ./src/modules/
COPY --from=builder-node --chown=www-data:www-data /build/themes/ ./src/themes/
COPY --from=builder-node --chown=www-data:www-data /build/plugins/ ./src/plugins/

# The builder-node COPY above brings node_modules symlinks from the pnpm store.
# Docker cannot COPY real files over existing symlinks, so remove them first.
RUN rm -rf ./src/modules/Components/node_modules

# Copy vendor CSS files from node_modules that the PHP AssetManager serves at runtime.
# These are referenced via '../node_modules/...' relative to the Components module assets dir.
# /vendor-css was pre-populated in the builder stage with real files (pnpm symlinks resolved).
COPY --from=builder-node --chown=www-data:www-data /vendor-css/ ./src/modules/Components/node_modules/

# Copy version file
COPY version.txt ./version.txt

# Copy PHP configuration
COPY docker/production/php.ini /usr/local/etc/php/php.ini
COPY docker/timezone.ini /usr/local/etc/php/conf.d/timezone.ini

# Copy PHP-FPM pool configuration and modify for Unix socket
COPY docker/production/www.conf /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/^listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm\/php-fpm.sock/' /usr/local/etc/php-fpm.d/www.conf && \
  sed -i 's/^;listen.owner = www-data/listen.owner = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
  sed -i 's/^;listen.group = www-data/listen.group = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
  sed -i 's/^;listen.mode = 0660/listen.mode = 0660/' /usr/local/etc/php-fpm.d/www.conf

# Copy Nginx configuration for Unix socket
COPY docker/production/nginx.conf /etc/nginx/nginx.conf

# Create socket directory and set permissions
RUN mkdir -p /var/run/php-fpm && \
  chown -R www-data:www-data /var/run/php-fpm
# Copy entrypoint and cron scripts
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/jobs-cron.sh /jobs-cron.sh
COPY docker/recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh
RUN chmod +x /entrypoint.sh /jobs-cron.sh /recreate-pending-pcache-cron.sh

# Create start script that starts PHP-FPM and Nginx
RUN cat > /start.sh << 'EOF'
#!/bin/sh
set -e

# Start PHP-FPM in background
php-fpm --daemonize

# Wait for socket to be created
max_wait=10
count=0
while [ ! -S /var/run/php-fpm/php-fpm.sock ] && [ $count -lt $max_wait ]; do
sleep 1
count=$((count+1))
done

# Start nginx in foreground
exec nginx -g "daemon off;"
EOF
RUN chmod +x /start.sh

# Create symlink for public
RUN ln -sf /var/www/html /var/www/public

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html/ /var/www/var/

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
CMD ["/start.sh"]

# Health check using the health.php endpoint
HEALTHCHECK --interval=30s --timeout=3s --start-period=60s --retries=3 \
  CMD curl -f http://localhost/health.php || exit 1

# =============================================================================
# Stage 4: Development image - Includes build tools
# =============================================================================
FROM production AS development

# Install Node.js and pnpm for hot-reload development
RUN apk add --no-cache nodejs npm && \
  npm install -g pnpm sass terser uglifycss autoprefixer postcss

# Install xdebug (but don't enable by default)
RUN apk add --no-cache --virtual .xdebug-deps $PHPIZE_DEPS linux-headers && \
  pecl install xdebug && \
  apk del .xdebug-deps && \
  ln -s $(find /usr/local/lib/php/extensions/ -name xdebug.so) /usr/local/lib/php/extensions/xdebug.so

# Copy development files
COPY docker/development/router.php /var/www/dev/router.php
COPY docker/development/start.sh /var/www/dev/start.sh

RUN chmod +x /var/www/dev/start.sh

# Development uses built-in PHP server
EXPOSE 80

CMD ["/var/www/dev/start.sh"]
