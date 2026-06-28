# 📋 Deployment Checklist cPanel

## 🔧 Preparation (Local)
- [ ] Run `npm run build` untuk build assets
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Generate new APP_KEY: `php artisan key:generate --show`
- [ ] Update `.env.production` dengan credentials yang benar
- [ ] Test aplikasi di local environment

## 📤 Upload ke cPanel
- [ ] Compress folder `typescript-version` menjadi zip
- [ ] Upload ke cPanel File Manager
- [ ] Extract di folder `public_html/webform` atau subdomain
- [ ] Set permissions: 755 untuk folders, 644 untuk files

## ⚙️ Configuration di cPanel
- [ ] Copy `.env.production` ke `.env`
- [ ] Update database credentials di `.env`
- [ ] Copy `.htaccess.production` ke `.htaccess`
- [ ] Set PHP version ke 8.2+ di **Select PHP Version**
- [ ] Enable required extensions:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `fileinfo`
  - `bcmath`
  - `gd` atau `imagick`

## 🗄️ Database Setup
- [ ] Import database structure (jika ada)
- [ ] Run migrations via SSH atau cPanel Terminal:
  ```bash
  php artisan migrate --force
  ```

## 🔒 Security Setup
- [ ] Set permissions:
  ```bash
  chmod -R 755 storage bootstrap/cache
  chmod -R 644 public
  ```
- [ ] Create storage symlink:
  ```bash
  php artisan storage:link
  ```
- [ ] Clear and cache:
  ```bash
  php artisan config:clear
  php artisan config:cache
  php artisan route:clear
  php artisan route:cache
  php artisan view:clear
  php artisan view:cache
  ```

## 🚀 Final Testing
- [ ] Test homepage loading
- [ ] Test login functionality
- [ ] Test form submission
- [ ] Test signature upload
- [ ] Test print functionality
- [ ] Check error logs di `storage/logs`

## 📊 Monitoring
- [ ] Setup error monitoring
- [ ] Check disk space usage
- [ ] Monitor database performance
- [ ] Backup setup (cPanel backup atau manual)

## 🔐 Security Recommendations
- [ ] Change default admin passwords
- [ ] Enable 2FA jika available
- [ ] Setup SSL certificate (cPanel SSL/TLS)
- [ ] Disable PHP error display di production
- [ ] Setup firewall rules
- [ ] Regular security updates
