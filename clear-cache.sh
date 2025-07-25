#!/bin/bash

# Script untuk clear cache Laravel
# Gunakan saat troubleshooting atau update aplikasi

echo "Clearing Laravel caches..."

# Clear application cache
php artisan cache:clear
echo "✓ Application cache cleared"

# Clear configuration cache
php artisan config:clear
echo "✓ Configuration cache cleared"

# Clear route cache
php artisan route:clear
echo "✓ Route cache cleared"

# Clear view cache
php artisan view:clear
echo "✓ View cache cleared"

# Clear compiled services
php artisan clear-compiled
echo "✓ Compiled services cleared"

# Optimize autoloader
composer dump-autoload --optimize
echo "✓ Autoloader optimized"

echo "All caches cleared successfully!"
echo "Don't forget to run optimization commands for production:"
echo "php artisan optimize"
