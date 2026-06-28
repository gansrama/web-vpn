#!/bin/bash

echo "🚀 Webform VPN Deployment Script for cPanel"
echo "=========================================="

# Configuration
PROJECT_NAME="webform-vpn"
DEPLOY_PATH="/home/$USER/public_html/webform"
BACKUP_PATH="/home/$USER/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in cPanel
if [ ! -d "/usr/local/cpanel" ]; then
    print_error "This script must be run on a cPanel server"
    exit 1
fi

# Create backup
print_status "Creating backup..."
mkdir -p $BACKUP_PATH
if [ -d "$DEPLOY_PATH" ]; then
    tar -czf "$BACKUP_PATH/${PROJECT_NAME}_backup_${TIMESTAMP}.tar.gz" -C "$(dirname $DEPLOY_PATH)" "$(basename $DEPLOY_PATH)"
    print_status "Backup created: $BACKUP_PATH/${PROJECT_NAME}_backup_${TIMESTAMP}.tar.gz"
fi

# Create deploy directory
print_status "Creating deployment directory..."
mkdir -p $DEPLOY_PATH

# Check dependencies
print_status "Checking dependencies..."

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "-" -f 1)
print_status "PHP version: $PHP_VERSION"

# Check required PHP extensions
REQUIRED_EXTENSIONS=("pdo_mysql" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json" "fileinfo" "bcmath" "gd")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        print_status "✓ $ext extension found"
    else
        print_warning "✗ $ext extension missing - please enable in cPanel"
    fi
done

# Check Composer
if command -v composer &> /dev/null; then
    print_status "✓ Composer found"
else
    print_error "✗ Composer not found"
    exit 1
fi

# Check Node.js (for build)
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    print_status "✓ Node.js found: $NODE_VERSION"
else
    print_warning "✗ Node.js not found - you'll need to build assets locally"
fi

# Deploy Laravel application
print_status "Deploying Laravel application..."

# Install dependencies
print_status "Installing PHP dependencies..."
cd $DEPLOY_PATH
composer install --no-dev --optimize-autoloader --no-interaction

# Create .env if not exists
if [ ! -f ".env" ]; then
    if [ -f ".env.production" ]; then
        cp .env.production .env
        print_status "Created .env from .env.production"
    else
        cp .env.example .env
        print_warning "Created .env from .env.example - please update manually"
    fi
fi

# Generate application key
print_status "Generating application key..."
php artisan key:generate --force

# Set permissions
print_status "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 644 public

# Create storage symlink
print_status "Creating storage symlink..."
php artisan storage:link

# Clear and cache
print_status "Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Run migrations (with confirmation)
echo ""
print_warning "Do you want to run database migrations? (y/N)"
read -r response
if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    print_status "Running database migrations..."
    php artisan migrate --force
fi

# Setup cron job for Laravel scheduler
print_status "Setting up Laravel scheduler..."
(crontab -l 2>/dev/null; echo "* * * * * cd $DEPLOY_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -

print_status "Deployment completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "1. Update .env file with your database credentials"
echo "2. Update APP_URL with your domain"
echo "3. Run 'php artisan migrate' if not done already"
echo "4. Test your application at: https://yourdomain.com/webform"
echo ""
echo "🔒 Security Recommendations:"
echo "1. Ensure SSL certificate is installed via cPanel"
echo "2. Update database passwords"
echo "3. Set up regular backups"
echo "4. Monitor application logs"
echo ""
echo "📁 Important Paths:"
echo "Application: $DEPLOY_PATH"
echo "Logs: $DEPLOY_PATH/storage/logs"
echo "Backups: $BACKUP_PATH"
