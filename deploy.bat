@echo off
echo 🚀 Starting deployment process...

REM 1. Build assets untuk production
echo 📦 Building assets...
call npm run build

REM 2. Copy environment file
echo ⚙️ Setting up environment...
copy .env.production .env

REM 3. Install composer dependencies untuk production
echo 📚 Installing composer dependencies...
call composer install --optimize-autoloader --no-dev

REM 4. Generate application key jika belum ada
echo 🔑 Generating application key...
call php artisan key:generate

REM 5. Clear dan cache config
echo 🧹 Clearing caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear
call php artisan route:clear

REM 6. Cache untuk performance
echo ⚡ Caching for performance...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

REM 7. Run migrations
echo 🗄️ Running migrations...
call php artisan migrate --force

REM 8. Seed database if needed
echo 🌱 Seeding database...
call php artisan db:seed --force

echo ✅ Deployment completed!
echo 🌐 Your app should be available at: https://prototype3.sweet.web.id
pause
