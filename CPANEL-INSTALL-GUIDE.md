# 🛠️ Panduan Install Composer Dependencies di cPanel

## 🔧 Metode 1: cPanel Terminal (Recommended)

### 1. Buka cPanel Terminal
- Login ke cPanel
- Scroll ke bagian **Advanced**
- Klik **Terminal**
- Terminal akan terbuka di browser

### 2. Navigate ke Project Folder
```bash
# Masuk ke direktori utama
cd

# Navigate ke project
cd form-jsc.66ghz.com/htdocs/webform-vpn

# Verifikasi lokasi
pwd
# Seharusnya menunjukkan: /home/username/form-jsc.66ghz.com/htdocs/webform-vpn
```

### 3. Install Dependencies
```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Jika ada error, coba tanpa optimize
composer install --no-dev
```

### 4. Verifikasi Installation
```bash
# Check vendor folder
ls -la vendor/

# Check autoload
ls -la vendor/autoload.php

# Check composer.lock
cat composer.lock | grep "name" | head -5
```

## 🌐 Metode 2: Browser-based Installer

Jika Terminal tidak available:

### 1. Upload Installer Script
- Upload file `install-composer.php` ke folder `htdocs/webform-vpn/`
- Access via browser: `https://form-jsc.66ghz.com/webform-vpn/install-composer.php`

### 2. Follow Instructions
- Script akan otomatis:
  - Check Composer availability
  - Download Composer jika perlu
  - Install dependencies
  - Show next steps

### 3. Security
- **Delete** `install-composer.php` setelah selesai!

## 📋 Commands Setelah Install

Setelah dependencies terinstall, jalankan:

```bash
# 1. Setup environment
cp .env.production .env

# 2. Generate app key
php artisan key:generate

# 3. Setup database (jika sudah dibuat)
php artisan migrate --force

# 4. Create storage link
php artisan storage:link

# 5. Optimize performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔍 Troubleshooting

### Error: Composer not found
```bash
# Check composer location
which composer

# Try full path
/usr/local/bin/composer install --no-dev

# Download composer jika perlu
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev
```

### Error: Memory limit
```bash
# Increase memory limit
php -d memory_limit=512M /usr/local/bin/composer install --no-dev
```

### Error: Permissions
```bash
# Fix permissions
chmod -R 755 .
chmod -R 644 composer.json composer.lock
```

### Error: Timeouts
```bash
# Increase execution time
export COMPOSER_PROCESS_TIMEOUT=600
composer install --no-dev
```

## 📊 Expected Output

Successful installation akan menunjukkan:
```
Loading composer repositories with package information
Installing dependencies from lock file
Package operations: XX installs, 0 updates, 0 removals
...
Generating optimized autoload files
```

Folder structure setelah install:
```
webform-vpn/
├── vendor/
│   ├── autoload.php
│   ├── composer/
│   ├── laravel/
│   └── ... (package folders)
├── composer.lock
└── ... (existing files)
```

## 🎯 Verifikasi Akhir

### 1. Check Laravel Status
```bash
php artisan --version
# Should show Laravel version

php artisan config:cache
# Should complete without errors
```

### 2. Test Application
Access via browser:
```
https://form-jsc.66ghz.com/webform-vpn/
```

### 3. Run Health Check
```bash
php health-check.php
# Atau access: /health-check.php
```

## 📞 Jika Masih Ada Masalah

1. **Check cPanel Error Logs**: cPanel → Metrics → Errors
2. **Contact Hosting Support**: Minta bantuan install Composer
3. **Try Alternative Method**: Gunakan `install-composer.php`

## ✅ Checklist Installation

- [ ] Composer dependencies installed
- [ ] vendor/autoload.php exists
- [ ] .env file configured
- [ ] App key generated
- [ ] Database migrated
- [ ] Storage link created
- [ ] Application accessible via browser
- [ ] Security files deleted (install-composer.php)
