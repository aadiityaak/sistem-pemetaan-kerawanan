# Crime Map - Deployment Checklist ✅

## Pre-Deployment

- [ ] `npm run build` berhasil ✅
- [ ] `php artisan config:cache` berhasil ✅
- [ ] `php artisan route:cache` berhasil ✅
- [ ] `php artisan view:cache` berhasil ✅
- [ ] File .env.production siap
- [ ] File DEPLOYMENT.md siap
- [ ] File index-production.php siap

## Database Setup

- [ ] Database MySQL dibuat di hosting
- [ ] Username dan password database dicatat
- [ ] Database host/server dicatat

## File Upload

- [ ] Folder laravel-app/ uploaded (semua kecuali public/)
- [ ] Isi folder public/ uploaded ke public_html/
- [ ] File .env.production di-rename menjadi .env
- [ ] File index-production.php di-rename menjadi index.php

## Configuration

- [ ] Edit .env dengan detail database yang benar
- [ ] Generate APP_KEY baru: `php artisan key:generate`
- [ ] Update APP_URL dengan domain yang benar
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false

## Permissions

- [ ] Set chmod 755 untuk storage/
- [ ] Set chmod 755 untuk bootstrap/cache/
- [ ] Set chmod 644 untuk .env

## Database Migration

- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --force`

## Production Optimization

- [ ] `php artisan optimize`
- [ ] `composer install --no-dev --optimize-autoloader`

## Testing

- [ ] Website dapat diakses
- [ ] Login berfungsi
- [ ] Dashboard muncul dengan benar
- [ ] Data kriminal dapat ditambah/edit
- [ ] Map berfungsi dengan benar
- [ ] All pages load without errors

## Security Check

- [ ] .env file tidak dapat diakses langsung via browser
- [ ] APP_DEBUG=false
- [ ] Database credentials aman
- [ ] Error reporting tidak menampilkan info sensitif

## Performance Check

- [ ] Page load time < 3 detik
- [ ] Assets (CSS/JS) ter-compress
- [ ] Images ter-optimize
- [ ] GZIP compression aktif

## Post-Deployment

- [ ] Backup database pertama
- [ ] Setup monitoring/logging
- [ ] Test all CRUD operations
- [ ] Test map functionality
- [ ] Check mobile responsiveness

## Emergency Contacts

- Hosting Provider: ******\_\_\_******
- Database Admin: ******\_\_\_******
- Domain Registrar: ******\_\_\_******

## Notes

- Backup original files sebelum upload
- Catat semua credentials dengan aman
- Test di staging environment jika memungkinkan
