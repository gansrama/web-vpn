# Web VPN Application

Aplikasi Web VPN berbasis Laravel + Vue 3 + TypeScript untuk manajemen form dan tanda tangan digital.

## 📋 Tech Stack

- **Backend**: Laravel (PHP 8.2+)
- **Frontend**: Vue 3 + TypeScript + Vite
- **Database**: MySQL
- **Styling**: TailwindCSS
- **Icons**: Iconify

## 🔧 Requirements

- PHP 8.2 atau lebih tinggi
- Composer
- Node.js 18+ dan npm
- MySQL 5.7+ atau MariaDB
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `gd` atau `imagick`

## 📦 Instalasi Lokal

### 1. Clone Repository
```bash
git clone git@git-smartcity.jakarta.go.id:gansrama/web-vpn.git
cd web-vpn/typescript-version
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file dengan konfigurasi database dan environment Anda
```

### 4. Database Setup
```bash
# Buat database MySQL baru
# Lalu jalankan migrations
php artisan migrate

# (Opsional) Seed database
php artisan db:seed
```

### 5. Build Assets
```bash
# Build untuk development
npm run dev

# Build untuk production
npm run build
```

### 6. Storage Link
```bash
php artisan storage:link
```

### 7. Jalankan Development Server
```bash
php artisan serve
```

Aplikasi akan accessible di `http://localhost:8000`

## 🚀 Deployment ke cPanel

### Persiapan Local
```bash
# Build assets production
npm run build

# Install dependencies production
composer install --no-dev --optimize-autoloader

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Upload ke cPanel

1. **Compress folder project**:
   - Compress folder `typescript-version` menjadi `webform-vpn.zip`
   - Exclude: `node_modules/`, `.git/`, `vendor/`

2. **Upload via cPanel File Manager**:
   - Login ke cPanel
   - Buka **File Manager**
   - Navigate ke folder yang diinginkan (misal: `htdocs/`)
   - Upload dan extract `webform-vpn.zip`

3. **Install Composer Dependencies**:
   ```bash
   cd webform-vpn
   composer install --no-dev --optimize-autoloader
   ```

4. **Setup Environment**:
   ```bash
   cp .env.production .env
   php artisan key:generate
   ```

5. **Setup Database**:
   - Buat database di cPanel → MySQL Databases
   - Update credentials di `.env`
   - Run migrations: `php artisan migrate --force`

6. **Setup Permissions**:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 755 public
   ```

7. **Create Storage Link**:
   ```bash
   php artisan storage:link
   ```

8. **Optimize**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Konfigurasi Domain

- **Subfolder**: `https://domain.com/webform-vpn/`
- **Subdomain**: Buat subdomain dan set Document Root ke `htdocs/webform-vpn/public`
- **Main Domain**: Set Document Root ke `htdocs/webform-vpn/public`

## 🛠️ Development

### Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=johnsoncodehk.volar) (dan disable Vetur).

### Type Support untuk `.vue` Imports di TS

Karena TypeScript tidak bisa handle type information untuk `.vue` imports, mereka di-shim menjadi generic Vue component type secara default. Jika Anda ingin actual prop types di `.vue` imports, jalankan `Volar: Switch TS Plugin on/off` dari VS Code command palette.

### Commands

```bash
# Development server
npm run dev

# Build untuk production
npm run build

# Type check
npm run type-check

# Laravel development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📁 Struktur Project

```
typescript-version/
├── app/              # Laravel application logic
├── bootstrap/        # Bootstrap files
├── config/           # Configuration files
├── database/         # Database migrations and seeds
├── public/           # Public accessible files
├── resources/        # Frontend resources (Vue components, assets)
├── routes/           # API and web routes
├── storage/          # Storage files (logs, uploads, cache)
├── tests/            # Test files
├── .env.example      # Environment template
├── .env.production   #生产 environment template
├── composer.json     # PHP dependencies
├── package.json      # Node.js dependencies
└── vite.config.ts    # Vite configuration
```

## 🔍 Troubleshooting

### Error 500
```bash
# Check permissions
chmod -R 755 storage bootstrap/cache

# Check .env configuration
cat .env

# Check logs
tail -f storage/logs/laravel.log
```

### Database Connection Error
- Verify database credentials di `.env`
- Check database exists
- Verify user has privileges

### Assets Not Loading
- Run `php artisan storage:link`
- Check `APP_URL` di `.env`
- Verify `.htaccess` configuration

### Composer Installation Error
```bash
# Increase memory limit
php -d memory_limit=512M composer install --no-dev

# Download composer jika perlu
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev
```

## 📚 Dokumentasi Tambahan

- [Deployment Checklist](DEPLOYMENT.md)
- [cPanel Installation Guide](CPANEL-INSTALL-GUIDE.md)
- [Upload Guide](UPLOAD-GUIDE.md)
- [Security Guide](SECURITY.md)

## 🔐 Security

- Jangan pernah commit `.env` file ke version control
- Gunakan environment variables untuk sensitive data
- Set proper file permissions di production
- Enable HTTPS di production
- Regular security updates

## 📝 License

Proprietary - Jakarta Smart City
