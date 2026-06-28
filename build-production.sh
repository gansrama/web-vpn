#!/bin/bash

echo "🚀 Building Webform VPN for Production..."

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "📦 Installing Node dependencies..."
npm ci

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Build completed!"
