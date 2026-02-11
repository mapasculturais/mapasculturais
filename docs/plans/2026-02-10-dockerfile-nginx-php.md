# Dockerfile with Nginx for PHP Framework Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Revise Dockerfile to support PHP framework using Nginx, starting from scratch using best practices (multi-stage builds, optimized layers, security).

**Architecture:** Multi-stage Dockerfile with Node.js builder, Composer builder, and production stage including PHP-FPM and Nginx. Nginx will serve static files and proxy PHP requests to PHP-FPM via Unix socket for better performance. Entrypoint script will start both services.

**Tech Stack:** Docker, PHP 8.3, Nginx, Node.js 20, pnpm, Composer, Alpine Linux.

---

### Task 1: Analyze current Nginx and PHP-FPM configurations

**Files:**
- Read: `docker/production/nginx.conf`
- Read: `docker/production/php.ini`
- Read: `docker/production/www.conf`
- Read: `docker/entrypoint.sh`

**Step 1: Examine Nginx configuration**

```bash
cat docker/production/nginx.conf
```
Note: current config expects PHP-FPM at `mapas:9000` (separate container). We'll change to Unix socket.

**Step 2: Examine PHP-FPM pool configuration**

```bash
cat docker/production/www.conf
```
Note: check user, group, listen settings.

**Step 3: Examine PHP settings**

```bash
cat docker/production/php.ini
```

**Step 4: Examine entrypoint script**

```bash
cat docker/entrypoint.sh
```
Note: currently waits for database, runs update scripts, starts cron jobs, and executes PHP-FPM.

**Step 5: Commit initial analysis**

```bash
git add docs/plans/2026-02-10-dockerfile-nginx-php.md
git commit -m "docs: add plan for Dockerfile with Nginx"
```

### Task 2: Create new multi-stage Dockerfile structure

**Files:**
- Create: `Dockerfile.new` (temporary)
- Modify: Eventually replace root `Dockerfile`

**Step 1: Write base structure with ARGs**

```dockerfile
# syntax=docker/dockerfile:1
ARG NODE_VERSION=20
ARG PHP_VERSION=8.3
```

**Step 2: Node.js builder stage**

```dockerfile
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

# Cleanup development files from node_modules
RUN find . -path '*/node_modules/*' -type f \( \
        -name '*.ts' -o -name '*.tsx' -o -name '*.map' -o \
        -name '*.md' -o -name '*.markdown' -o \
        -name 'LICENSE*' -o -name 'CHANGELOG*' -o -name 'README*' -o \
        -name '*.d.ts' -o -name 'tsconfig*' \
    \) -delete 2>/dev/null || true && \
    find . -path '*/node_modules/*' -name '.git' -type d -exec rm -rf {} + 2>/dev/null || true
```

**Step 3: Composer builder stage**

```dockerfile
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
```

**Step 4: Production stage with PHP-FPM and Nginx**

```dockerfile
FROM php:${PHP_VERSION}-fpm-alpine AS production

LABEL org.opencontainers.image.title="MapaCultural"
LABEL org.opencontainers.image.description="Platform for cultural mapping"
LABEL org.opencontainers.image.vendor="RedeMapas"
LABEL org.opencontainers.image.source="https://github.com/redemapas/mapas"
LABEL org.opencontainers.image.licenses="AGPL-3.0"
```

**Step 5: Commit Dockerfile skeleton**

```bash
git add Dockerfile.new
git commit -m "feat: add skeleton for multi-stage Dockerfile with Nginx"
```

### Task 3: Install Nginx and PHP extensions in production stage

**Files:**
- Modify: `Dockerfile.new`

**Step 1: Install Nginx and runtime dependencies**

```dockerfile
# Install runtime dependencies
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
    nginx \
    supervisor
```

**Step 2: Install PHP build dependencies and extensions**

```dockerfile
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
    opcache \
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
RUN pecl install redis && \
    docker-php-ext-enable redis

# Cleanup build dependencies
RUN apk del .build-deps && \
    rm -rf /tmp/pear /var/cache/apk/*
```

**Step 3: Configure PHP-FPM to use Unix socket**

```dockerfile
# Copy PHP-FPM pool configuration
COPY docker/production/www.conf /usr/local/etc/php-fpm.d/www.conf
```

**Step 4: Commit extensions installation**

```bash
git add Dockerfile.new
git commit -m "feat: add Nginx and PHP extensions to production stage"
```

### Task 4: Configure Nginx and copy application files

**Files:**
- Create: `docker/production/nginx-prod.conf` (modified for Unix socket)
- Modify: `Dockerfile.new`

**Step 1: Create Nginx configuration for production**

```nginx
# docker/production/nginx-prod.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 1G;

    server {
        listen 80;
        server_name _;
        root /var/www/html;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$args;
        }

        location ~ /files/.*\.php$ {
            deny all;
            return 403;
        }

        location ~ /asset/.*\.php$ {
            deny all;
            return 403;
        }

        location ~* \.(js|css|png|jpg|jpeg|gif|ico|woff|woff2|ttf|svg)$ {
            expires 1w;
            log_not_found off;
        }

        location ~ \.php$ {
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass unix:/var/run/php-fpm.sock;
            fastcgi_index index.php;
            fastcgi_read_timeout 3600;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info;
        }
    }
}
```

**Step 2: Copy configuration and application files in Dockerfile**

```dockerfile
# Create necessary directories
RUN mkdir -p /var/www/var/DoctrineProxies \
             /var/www/var/logs \
             /var/www/var/private-files \
             /var/www/html/assets \
             /var/www/html/files \
             /var/log/nginx \
             /var/cache/nginx && \
    chown -R www-data:www-data /var/www && \
    chown -R nginx:nginx /var/log/nginx /var/cache/nginx

WORKDIR /var/www

# Copy composer dependencies
COPY --from=builder-composer /build/vendor ./vendor/

# Copy application source
COPY --chown=www-data:www-data config ./config/
COPY --chown=www-data:www-data public ./html/
COPY --chown=www-data:www-data scripts ./scripts/
COPY --chown=www-data:www-data src ./src/

# Copy built frontend assets from Node.js stage
COPY --from=builder-node --chown=www-data:www-data /build/modules/ ./src/modules/
COPY --from=builder-node --chown=www-data:www-data /build/themes/ ./src/themes/

# Copy version file
COPY version.txt ./version.txt

# Copy PHP configuration
COPY docker/production/php.ini /usr/local/etc/php/php.ini
COPY docker/timezone.ini /usr/local/etc/php/conf.d/timezone.ini

# Copy Nginx configuration
COPY docker/production/nginx-prod.conf /etc/nginx/nginx.conf

# Copy entrypoint and scripts
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/jobs-cron.sh /jobs-cron.sh
COPY docker/recreate-pending-pcache-cron.sh /recreate-pending-pcache-cron.sh

RUN chmod +x /entrypoint.sh /jobs-cron.sh /recreate-pending-pcache-cron.sh

# Create symlink for public
RUN ln -sf /var/www/html /var/www/public

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html/ /var/www/var/
```

**Step 3: Commit Nginx configuration and file copying**

```bash
git add docker/production/nginx-prod.conf Dockerfile.new
git commit -m "feat: add Nginx config and file copying"
```

### Task 5: Modify entrypoint to start Nginx and PHP-FPM

**Files:**
- Modify: `docker/entrypoint.sh`
- Create: `docker/start-services.sh` (optional)

**Step 1: Create a startup script that launches both services**

```bash
#!/bin/bash
# docker/start-services.sh
set -e

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g 'daemon off;'
```

**Step 2: Modify entrypoint.sh to execute startup after initialization**

Current entrypoint does database wait, updates, cron jobs, then exec "$@". We need to change the CMD to start both services. Alternatively, modify entrypoint to start services after initialization but before exec. Simpler: change CMD to start-services.sh.

**Step 3: Update Dockerfile CMD**

```dockerfile
# At the end of Dockerfile
EXPOSE 80

CMD ["/bin/sh", "-c", "/entrypoint.sh && /docker/start-services.sh"]
```

But better to keep entrypoint as init script and CMD as start-services.sh.

**Step 4: Commit entrypoint changes**

```bash
git add docker/entrypoint.sh docker/start-services.sh Dockerfile.new
git commit -m "feat: modify entrypoint to start Nginx and PHP-FPM"
```

### Task 6: Build and test the new Dockerfile

**Files:**
- Test: Build Dockerfile.new

**Step 1: Build the image**

```bash
docker build -f Dockerfile.new -t mapas-nginx-test .
```

**Step 2: Run a container to verify services start**

```bash
docker run -d --name test-mapas -p 8080:80 mapas-nginx-test
sleep 5
docker logs test-mapas
```

**Step 3: Check if Nginx and PHP-FPM are running**

```bash
docker exec test-mapas ps aux
```

**Step 4: Clean up test container**

```bash
docker stop test-mapas && docker rm test-mapas
```

**Step 5: Commit test results**

```bash
git add docs/plans/2026-02-10-dockerfile-nginx-php.md
git commit -m "test: build and verify Dockerfile with Nginx"
```

### Task 7: Replace original Dockerfile and update documentation

**Files:**
- Replace: `Dockerfile` with `Dockerfile.new`
- Update: `README.md` or `docs/` about changes

**Step 1: Move Dockerfile.new to Dockerfile**

```bash
mv Dockerfile.new Dockerfile
```

**Step 2: Update AGENTS.md if necessary**

Check if any build commands changed.

**Step 3: Run final build test**

```bash
docker build -t mapas:latest .
```

**Step 4: Commit final version**

```bash
git add Dockerfile
git commit -m "feat: replace Dockerfile with Nginx support"
```

**Step 5: Push changes (optional)**

```bash
git push origin feature/docker-nginx
```

---
**Plan complete and saved to `docs/plans/2026-02-10-dockerfile-nginx-php.md`. Two execution options:**

**1. Subagent-Driven (this session)** - I dispatch fresh subagent per task, review between tasks, fast iteration

**2. Parallel Session (separate)** - Open new session with executing-plans, batch execution with checkpoints

**Which approach?**