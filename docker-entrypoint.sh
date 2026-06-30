#!/bin/bash
set -e

log() {
    echo "[entrypoint] $1"
}

log "Starting Laravel application..."

# -------------------------------------------------------
# Generate .env from Railway environment variables
# -------------------------------------------------------
log "Generating .env from environment variables..."
cat > .env <<EOF
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_TIMEZONE="${APP_TIMEZONE:-UTC}"
APP_URL="${APP_URL:-http://localhost}"

APP_LOCALE="${APP_LOCALE:-en}"
APP_FALLBACK_LOCALE="${APP_FALLBACK_LOCALE:-en}"
APP_FAKER_LOCALE="${APP_FAKER_LOCALE:-en_US}"

APP_MAINTENANCE_DRIVER="${APP_MAINTENANCE_DRIVER:-file}"

PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-4}"

BCRYPT_ROUNDS="${BCRYPT_ROUNDS:-12}"

LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_STACK="${LOG_STACK:-single}"
LOG_DEPRECATIONS_CHANNEL="${LOG_DEPRECATIONS_CHANNEL:-null}"
LOG_LEVEL="${LOG_LEVEL:-error}"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-laravel}"
DB_USERNAME="${DB_USERNAME:-postgres}"
DB_PASSWORD="${DB_PASSWORD:-}"

SUPABASE_URL="${SUPABASE_URL:-}"
SUPABASE_KEY="${SUPABASE_KEY:-}"

N8N_REGISTER_WEBHOOK="${N8N_REGISTER_WEBHOOK:-}"
N8N_SESSION_WEBHOOK="${N8N_SESSION_WEBHOOK:-}"

SESSION_DRIVER="${SESSION_DRIVER:-file}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
SESSION_ENCRYPT="${SESSION_ENCRYPT:-false}"
SESSION_PATH="${SESSION_PATH:-/}"
SESSION_DOMAIN="${SESSION_DOMAIN:-null}"

BROADCAST_CONNECTION="${BROADCAST_CONNECTION:-log}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

CACHE_STORE="${CACHE_STORE:-file}"
CACHE_PREFIX="${CACHE_PREFIX:-}"

VITE_APP_NAME="${APP_NAME:-Laravel}"
VITE_API_BASE_URL="${VITE_API_BASE_URL:-}"
EOF

log "Environment variables written to .env"

# -------------------------------------------------------
# Generate APP_KEY if not set
# -------------------------------------------------------
if [ -z "${APP_KEY}" ]; then
    log "APP_KEY not set — generating a new key..."
    php artisan key:generate --force
    log "APP_KEY generated successfully"
else
    log "APP_KEY is already set"
fi

# -------------------------------------------------------
# Clear and cache configuration
# -------------------------------------------------------
log "Clearing old caches..."
php artisan config:clear || true
php artisan view:clear  || true
php artisan route:clear || true

log "Caching configuration..."
if php artisan config:cache; then
    log "Config cached successfully"
else
    log "WARNING: config:cache failed — continuing without cache"
fi

log "Caching views..."
if php artisan view:cache; then
    log "Views cached successfully"
else
    log "WARNING: view:cache failed — continuing without view cache"
fi

# -------------------------------------------------------
# Run database migrations (non-fatal)
# -------------------------------------------------------
log "Running database migrations..."
php artisan migrate --force --no-interaction || log "WARNING: Migrations failed — continuing without database"

# -------------------------------------------------------
# Ensure storage symlink exists
# -------------------------------------------------------
if [ ! -L public/storage ]; then
    log "Creating storage symlink..."
    php artisan storage:link || true
fi

# -------------------------------------------------------
# Start Laravel server
# -------------------------------------------------------
PORT="${PORT:-80}"
log "Starting server on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
