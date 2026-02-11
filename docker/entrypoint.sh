#!/bin/bash
set -e

echo "Starting Mapas Culturais container..."

# Create necessary directories
mkdir -p /var/www/var/DoctrineProxies /var/www/var/logs /var/www/var/private-files
touch /var/www/var/logs/app.log
chown -R www-data: /var/www/var/DoctrineProxies /var/www/var/logs /var/www/var/private-files

# Create web directories if they don't exist
mkdir -p /var/www/html/assets /var/www/html/files
chown -R www-data:www-data /var/www/html/assets /var/www/html/files

# Skip database-dependent operations if database is not available
# The application will handle database connectivity with retry logic
echo "Skipping database-dependent initialization (database connectivity handled by application)"

# Check if we need to compile SASS (skip if sass command not available or database not ready)
if ! cmp /var/www/version.txt /var/www/var/private-files/deployment-version >/dev/null 2>&1
then
    echo "New deployment detected"
    
    # Only compile SASS if sass command is available
    if command -v sass >/dev/null 2>&1; then
        echo "Compiling SASS files..."
        # Try to compile BaseV1 SASS directly without database dependency
        if [ -f /var/www/src/themes/BaseV1/assets/css/sass/main.scss ]; then
            sass /var/www/src/themes/BaseV1/assets/css/sass/main.scss:/var/www/src/themes/BaseV1/assets/css/main.css --quiet || echo "Warning: SASS compilation failed"
        fi
    else
        echo "Skipping SASS compilation (sass command not available)"
    fi
    
    # Skip Doctrine proxy generation (requires database)
    echo "Skipping Doctrine proxy generation (requires database connection)"
    
    # Still update deployment version to avoid repeated attempts
    cp /var/www/version.txt /var/www/var/private-files/deployment-version 2>/dev/null || true
fi

# Build assets if BUILD_ASSETS is set (development only)
if [ "${BUILD_ASSETS:-0}" = "1" ]; then
    echo "Building development assets..."
    chown www-data: /var/www/html/assets
    cd /var/www/src
    pnpm install --recursive 
    pnpm run dev
fi

# Ensure proper ownership
chown -R www-data:www-data /var/www/html /var/www/var

# Start background cron jobs (they will handle their own database connectivity)
echo "Starting background cron jobs..."
cd /
touch /nohup.out
chown www-data: /nohup.out
sudo -E -u www-data nohup /jobs-cron.sh >> /dev/stdout 2>&1 &
sudo -E -u www-data nohup /recreate-pending-pcache-cron.sh >> /dev/stdout 2>&1 &

# Tail logs in background
tail -f /nohup.out > /dev/stdout &

# Create readiness file
touch /mapas-ready

echo "Entrypoint completed - services will start now"
exec "$@"