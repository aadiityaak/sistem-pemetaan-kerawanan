# Pemetaan Kerawanan - Deployment Guide untuk Shared Hosting

## Quick Start - Automated Build

### 🚀 Build & Package untuk Deployment

```bash
# Build assets dan buat deployment package
bun run build:production
```

Script ini akan:

1. Build Vite assets untuk production
2. Membuat struktur folder yang siap upload
3. Memodifikasi `index.php` untuk shared hosting
4. Membuat template `.env.production`
5. Generate file ZIP `crime-map-deployment.zip`

**Output:**

- `crime-map-deployment.zip` - File siap upload
- Berisi folder `laravel-app/` dan `public_html/`
- Instruksi deployment lengkap included

---

## Deployment di aaPanel (Recommended)

### 🚀 Build & Package khusus aaPanel

Gunakan script ini untuk menghasilkan paket yang lebih “pas” untuk struktur aaPanel (web root memakai folder `public/`):

```bash
# Build assets dan buat deployment package untuk aaPanel
composer run build:production-aa
```

**Output:**

- `crime-map-aapanel.zip`
- Berisi folder `laravel-app/` dan `public/`
- Termasuk helper script `POST-DEPLOY-AAPANEL.sh` untuk set permission (opsional)

### 🚀 Build aaPanel tanpa vendor (lebih kecil)

Jika di server aaPanel Anda bisa menjalankan `composer install`, Anda bisa buat paket **tanpa folder `vendor/`** supaya ZIP lebih kecil:

```bash
composer run build:production-aa-novendor
```

**Output:**

- `crime-map-aapanel-novendor.zip`
- Berisi folder `laravel-app/` (tanpa `vendor/`) dan `public/`

Setelah upload & extract di server, jalankan:

```bash
cd /www/wwwroot/nama-domain/laravel-app
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
```

### Step-by-Step Deploy di aaPanel

1. **Buat Website**
    - aaPanel → Website → Add site
    - Pilih PHP Version yang sesuai (minimal PHP 8.2)
2. **Set Document Root ke folder `public/`**
    - Setelah extract ZIP (langkah 3), pastikan Web Root/Document Root mengarah ke:
        - `/www/wwwroot/nama-domain/public`
3. **Upload & Extract Paket**
    - Upload `crime-map-aapanel.zip` ke folder site (misalnya `/www/wwwroot/nama-domain/`)
    - Extract sehingga menjadi:
        - `/www/wwwroot/nama-domain/laravel-app/`
        - `/www/wwwroot/nama-domain/public/`
    - (Opsional, disarankan) Jalankan helper:
        ```bash
        cd /www/wwwroot/nama-domain
        bash POST-DEPLOY-AAPANEL.sh
        ```
4. **Konfigurasi `.env`**
    - Rename `laravel-app/.env.production` → `laravel-app/.env`
    - Update minimal:
        - `APP_URL=https://nama-domain`
        - `DB_*` sesuai database aaPanel
5. **Setup Database**
    - aaPanel → Database → Add database
    - Jalankan migration via SSH/Terminal aaPanel:
        ```bash
        cd /www/wwwroot/nama-domain/laravel-app
        php artisan key:generate
        php artisan migrate --force
        ```
6. **Storage link (untuk upload file)**
    - Jika ada fitur upload (favicon/logo/gallery/video), jalankan:
        ```bash
        cd /www/wwwroot/nama-domain/laravel-app
        php artisan storage:link
        ```
7. **Permissions & Cache**
    - Pastikan folder berikut writable:
        - `laravel-app/storage/`
        - `laravel-app/bootstrap/cache/`
    - Lalu:
        ```bash
        cd /www/wwwroot/nama-domain/laravel-app
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        php artisan cache:clear
        ```
8. **Nginx Rewrite (jika pakai Nginx)**
    - Biasanya sudah otomatis jika Document Root benar ke `public/`.
    - Jika URL tanpa `/index.php` belum jalan, aktifkan rules Laravel pada konfigurasi rewrite di aaPanel.

## Manual Deployment (Advanced)

### 1. Files yang HARUS di-upload ke shared hosting:

```
├── app/
├── bootstrap/
├── config/
├── database/
├── public/ (isi folder ini untuk public_html)
├── resources/
├── routes/
├── storage/
├── vendor/
├── artisan
├── composer.json
├── composer.lock
└── .env (rename dari .env.production)
```

### 2. Files yang TIDAK perlu di-upload:

```
├── node_modules/
├── .git/
├── tests/
├── .env.local
├── .env.example
├── package.json
├── package-lock.json
├── vite.config.ts
├── tsconfig.json
└── README.md
```

## Automated Deployment Process

### Step 1: Build Production Package

```bash
bun run build:production
```

Ini akan:

- ✅ Build assets dengan Vite
- ✅ Copy semua file Laravel yang diperlukan
- ✅ Modifikasi `index.php` untuk shared hosting
- ✅ Buat template `.env.production`
- ✅ Buat file ZIP siap upload

### Step 2: Extract & Upload

1. **Extract `crime-map-deployment.zip`**
2. **Upload `laravel-app/` ke home directory hosting**
    ```
    /home/username/laravel-app/
    ```
3. **Upload isi `public_html/` ke public_html hosting**
    ```
    /public_html/
    ```

### Step 3: Configure Environment

1. **Rename `.env.production` menjadi `.env`**
2. **Edit database settings di `.env`**
3. **Generate APP_KEY:**
    ```bash
    php artisan key:generate
    ```

### Step 4: Setup Database

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Step 5: Optimize

```bash
php artisan optimize
```

---

## Manual Deployment (Advanced)

Jika ingin deploy manual tanpa script otomatis:

## Langkah-langkah Deployment

### Step 1: Persiapan Database

1. Buat database MySQL di hosting panel
2. Catat detail database (host, nama database, username, password)

### Step 2: Upload Files

1. **Upload semua folder KECUALI public ke folder di atas public_html**

    ```
    /home/username/laravel-app/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── artisan
    ├── composer.json
    └── .env
    ```

2. **Upload isi folder public ke public_html**
    ```
    /public_html/
    ├── build/
    ├── favicon.ico
    ├── index.php
    ├── .htaccess
    └── robots.txt
    ```

### Step 3: Konfigurasi

1. **Edit file .env di /home/username/laravel-app/.env**

    ```env
    APP_NAME="Pemetaan Kerawanan"
    APP_ENV=production
    APP_KEY=base64:GENERATE_NEW_KEY
    APP_DEBUG=false
    APP_URL=https://yourdomain.com

    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```

2. **Generate APP_KEY baru**
   Via terminal hosting atau online generator:

    ```bash
    php artisan key:generate
    ```

3. **Edit index.php di public_html**
   Ubah path di index.php:
    ```php
    require __DIR__.'/../laravel-app/vendor/autoload.php';
    $app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';
    ```

### Step 4: Set Permissions

Set permission untuk folder storage dan bootstrap/cache:

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Step 5: Database Migration

Jalankan migration via terminal hosting:

```bash
cd /home/username/laravel-app
php artisan migrate --force
php artisan db:seed --force
```

### Step 6: Optimize untuk Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Error "Class not found"

```bash
composer install --no-dev --optimize-autoloader
```

### Error "Storage link"

```bash
php artisan storage:link
```

### Error "Permission denied"

- Set folder storage/ dan bootstrap/cache/ ke 755 atau 777
- Set .env file ke 644

### Error "Database connection"

- Pastikan detail database di .env benar
- Test koneksi database melalui hosting panel

## File Structure Final di Hosting

```
/home/username/
├── laravel-app/           # Aplikasi Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
└── public_html/           # Web root
    ├── build/             # Assets hasil build
    ├── index.php          # Entry point (dimodifikasi)
    ├── .htaccess
    └── favicon.ico
```

## Security Checklist

- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secured
- [ ] Storage folder permissions correct
- [ ] .env file tidak dapat diakses public

## Performance Optimization

- [ ] Run `php artisan optimize`
- [ ] Enable OPcache di hosting
- [ ] Setup CloudFlare (optional)
- [ ] Compress images
- [ ] Enable GZIP compression

## Post-Deployment

1. Test semua fitur aplikasi
2. Test upload file (jika ada)
3. Test database operations
4. Monitor error logs
5. Setup backup schedule

## Maintenance

- Backup database reguler
- Monitor disk space
- Update Laravel dependencies berkala
- Monitor application logs
