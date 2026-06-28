#!/bin/bash

echo "🎵 Installing Composer Dependencies in cPanel Environment"
echo "======================================================"

# Navigate to project directory
cd ~/form-jsc.66ghz.com/htdocs/webform-vpn

echo "📍 Current directory: $(pwd)"
echo ""

# Check if composer is available
if command -v composer &> /dev/null; then
    echo "✅ Composer found: $(composer --version)"
else
    echo "❌ Composer not found. Please install Composer first."
    echo "💡 In cPanel, Composer is usually pre-installed in Terminal."
    exit 1
fi

# Check composer.json exists
if [ ! -f "composer.json" ]; then
    echo "❌ composer.json not found in current directory"
    exit 1
fi

echo "📦 composer.json found"
echo ""

# Install dependencies
echo "🚀 Installing dependencies (production mode)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "✅ Dependencies installed successfully!"
echo ""

# Check if vendor folder exists
if [ -d "vendor" ]; then
    echo "📁 Vendor folder created"
    echo "📊 Vendor folder size: $(du -sh vendor | cut -f1)"
    echo "📄 Number of packages: $(find vendor -name 'composer.json' | wc -l)"
else
    echo "❌ Vendor folder not created"
fi

echo ""
echo "🔍 Verifying installation..."
if [ -f "vendor/autoload.php" ]; then
    echo "✅ autoload.php found"
else
    echo "❌ autoload.php not found"
fi

echo ""
echo "💡 Next steps:"
echo "1. Copy .env.production to .env"
echo "2. Update database credentials"
echo "3. Run: php artisan key:generate"
echo "4. Run: php artisan migrate --force"
echo "5. Run: php artisan storage:link"
