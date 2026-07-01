# Web VPN Application

Aplikasi Web VPN berbasis Laravel + Vue 3 + TypeScript untuk manajemen form dan tanda tangan digital.

## 📋 Tech Stack

- **Backend**: Laravel (PHP 8.2+)
- **Frontend**: Vue 3 + TypeScript + Vite
- **Database**: PostgreSQL
- **Styling**: TailwindCSS
- **Icons**: Iconify

## 🔧 Requirements

- PHP 8.2 atau lebih tinggi
- Composer
- Node.js 18+ dan npm
- PostgreSQL 12+ atau MySQL 5.7+
- Ekstensi PHP: `pdo_pgsql`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `gd` atau `imagick`

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
# Buat database PostgreSQL baru
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

## � Instalasi dengan Docker (Recommended untuk Production)

**Docker adalah metode deployment yang direkomendasikan untuk production environment.**

Project ini menyediakan setup Docker yang lengkap dengan:
- Laravel Application (PHP 8.2)
- PostgreSQL Database
- Redis Cache
- Nginx Web Server
- Node.js untuk Vite Development
- pgAdmin (opsional)
- MailHog untuk email testing (opsional)

### Prerequisites
- Docker Desktop terinstall
- Docker Compose terinstall

### Quick Start dengan Docker Compose

#### Opsi 1: Docker Compose Simple (Recommended untuk Development)
```bash
# Build dan jalankan semua services
docker-compose -f docker-compose.simple.yml up -d

# Akses aplikasi di http://localhost:8000
# Akses pgAdmin di http://localhost:8081
```

#### Opsi 2: Docker Compose Full (Complete Setup)
```bash
# Build dan jalankan semua services
docker-compose up -d

# Akses aplikasi di http://localhost:8000
# Akses pgAdmin di http://localhost:8080
# Akses MailHog di http://localhost:8025
```

### Docker Commands

```bash
# Jalankan containers
docker-compose up -d

# Stop containers
docker-compose down

# Stop dan hapus volumes
docker-compose down -v

# View logs
docker-compose logs -f app

# Masuk ke container app
docker-compose exec app bash

# Run migrations di dalam container
docker-compose exec app php artisan migrate

# Install dependencies di dalam container
docker-compose exec app composer install

# Build assets di dalam container
docker-compose exec node npm run build
```

### Konfigurasi Environment di Docker

Environment variables sudah di-set di `docker-compose.yml` dan `docker-compose.simple.yml`. Jika perlu mengubah:

1. Edit file `docker-compose.yml` atau `docker-compose.simple.yml`
2. Update environment variables di section `app` service
3. Restart containers: `docker-compose down && docker-compose up -d`

### Docker Structure

```
docker/
├── nginx/
│   ├── nginx.conf
│   ├── sites/
│   └── conf.d/
└── entrypoint.sh
```

### Troubleshooting Docker

#### Container tidak start
```bash
# Check container status
docker-compose ps

# View logs
docker-compose logs app
docker-compose logs postgres
docker-compose logs nginx
```

#### Database connection error
```bash
# Pastikan PostgreSQL container running
docker-compose ps postgres

# Restart PostgreSQL
docker-compose restart postgres
```

#### Permission issues
```bash
# Fix permissions di dalam container
docker-compose exec app chmod -R 755 storage bootstrap/cache
```

#### Rebuild containers
```bash
# Rebuild tanpa cache
docker-compose build --no-cache

# Rebuild specific service
docker-compose build app
```

## ️ Development

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
- [Security Guide](SECURITY.md)

## 🔐 Security

- Jangan pernah commit `.env` file ke version control
- Gunakan environment variables untuk sensitive data
- Set proper file permissions di production
- Enable HTTPS di production
- Regular security updates

## 📝 License

Proprietary - Jakarta Smart City
