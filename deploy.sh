#!/bin/bash

echo "🚀 Starting deployment process..."

# 1. Build assets untuk production
echo "📦 Building assets..."
npm run build

# 2. Copy environment file
echo "⚙️ Setting up environment..."
cp .env.production .env

# 3. Install composer dependencies untuk production
echo "📚 Installing composer dependencies..."
composer install --optimize-autoloader --no-dev

# 4. Generate application key jika belum ada
echo "🔑 Generating application key..."
php artisan key:generate

# 5. Clear dan cache config
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 6. Cache untuk performance
echo "⚡ Caching for performance..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# 8. Seed database if needed
echo "🌱 Seeding database..."
php artisan db:seed --force

echo "✅ Deployment completed!"
echo "🌐 Your app should be available at: https://prototype3.sweet.web.id"
