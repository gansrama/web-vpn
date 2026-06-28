# 📁 Upload Guide untuk cPanel

## 🎯 Lokasi Upload Berdasarkan Gambar

Berdasarkan struktur cPanel Anda:
```
form-jsc.66ghz.com/
└── htdocs/
    ├── index2.html (existing)
    └── webform-vpn/  ← UPLOAD DI SINI
        ├── app/
        ├── bootstrap/
        ├── config/
        ├── database/
        ├── public/
        ├── resources/
        ├── routes/
        ├── storage/
        ├── vendor/
        ├── .env
        ├── .htaccess
        ├── composer.json
        ├── package.json
        └── ... (file lainnya)
```

## 🚀 Step-by-Step Upload

### 1. Persiapan Local (Sebelum Upload)
```bash
# Build assets production
npm run build

# Install dependencies production
composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Upload ke cPanel

1. **Compress folder project**:
   - Compress seluruh folder `typescript-version` menjadi `webform-vpn.zip`
   - Pastikan tidak termasuk:
     - `node_modules/`
     - `.git/`
     - `vendor/` (akan di-install di server)

2. **Upload via cPanel File Manager**:
   - Login ke cPanel
   - Buka **File Manager**
   - Navigate ke `form-jsc.66ghz.com/htdocs/`
   - Klik **Upload**
   - Upload file `webform-vpn.zip`
   - Extract di folder tersebut

3. **Rename folder**:
   - Rename dari `typescript-version` menjadi `webform-vpn`

### 3. Konfigurasi di Server

1. **Install Composer Dependencies**:
   ```bash
   # Masuk ke folder project
   cd webform-vpn
   
   # Install dependencies
   composer install --no-dev --optimize-autoloader
   ```

2. **Setup Environment**:
   ```bash
   # Copy .env.production ke .env
   cp .env.production .env
   
   # Generate key baru
   php artisan key:generate
   ```

3. **Setup Database**:
   - Buat database di cPanel → MySQL Databases
   - Update credentials di `.env`
   - Run migrations:
   ```bash
   php artisan migrate --force
   ```

4. **Setup Permissions**:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 755 public
   ```

5. **Create Storage Link**:
   ```bash
   php artisan storage:link
   ```

6. **Optimize**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 🔧 Konfigurasi Domain

### Opsi A: Subfolder (Tanpa ubah Document Root)
Aplikasi akan accessible di:
```
https://form-jsc.66ghz.com/webform-vpn/
```

### Opsi B: Subdomain (Recommended)
1. Buat subdomain di cPanel: `webform.form-jsc.66ghz.com`
2. Set Document Root ke: `htdocs/webform-vpn/public`
3. Aplikasi accessible di:
```
https://webform.form-jsc.66ghz.com/
```

### Opsi C: Main Domain (Jika hanya aplikasi ini)
1. Edit Document Root domain di cPanel
2. Ubah dari `htdocs` menjadi `htdocs/webform-vpn/public`
3. Aplikasi accessible di:
```
https://form-jsc.66ghz.com/
```

## 📝 Checklist Setelah Upload

- [ ] File berhasil di-upload ke `htdocs/webform-vpn/`
- [ ] Composer dependencies terinstall
- [ ] `.env` file dikonfigurasi dengan benar
- [ ] Database dibuat dan migrations dijalankan
- [ ] Storage link dibuat
- [ ] Permissions di-set dengan benar
- [ ] Cache di-clear dan di-optimize
- [ ] Aplikasi bisa diakses via browser

## 🔍 Testing

1. **Health Check**:
   - Akses: `https://domain.com/webform-vpn/health-check.php`
   - Pastikan semua checks ✅

2. **Basic Functionality**:
   - Homepage loading
   - Login form
   - Form submission
   - Signature upload

## 🚨 Troubleshooting

### Error 500
```bash
# Check permissions
chmod -R 755 storage bootstrap/cache

# Check .env
cat .env

# Check logs
tail -f storage/logs/laravel.log
```

### Database Connection Error
- Verify database credentials di `.env`
- Check database exists di cPanel
- Verify user has privileges

### Assets Not Loading
- Run `php artisan storage:link`
- Check `APP_URL` di `.env`
- Verify `.htaccess` configuration

## 📞 Support

Jika ada masalah:
1. Check error logs: `storage/logs/laravel.log`
2. Run health check: `health-check.php`
3. Verify cPanel error logs
4. Contact hosting support jika perlu
