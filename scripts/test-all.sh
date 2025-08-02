#!/usr/bin/env bash
# Script untuk menjalankan semua tests dan linting

echo "🚀 Running Crime Map Test Suite"
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print status
print_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ $2${NC}"
    else
        echo -e "${RED}✗ $2${NC}"
        exit 1
    fi
}

print_info() {
    echo -e "${YELLOW}➜ $1${NC}"
}

# 1. PHP Code Style Check
print_info "Checking PHP code style with Laravel Pint..."
./vendor/bin/pint --test
print_status $? "PHP code style"

# 2. JavaScript/TypeScript Linting
print_info "Running ESLint..."
npm run lint:check
print_status $? "ESLint"

# 3. TypeScript Type Check
print_info "Running TypeScript type check..."
npm run type-check
print_status $? "TypeScript"

# 4. Prettier Format Check
print_info "Checking code formatting..."
npm run format:check
print_status $? "Prettier formatting"

# 5. Build Check
print_info "Building assets..."
npm run build
print_status $? "Asset build"

# 6. PHP Unit Tests (skip if SQLite not available)
print_info "Checking PHP unit tests..."
if php -m | grep -q sqlite3; then
    php artisan test
    print_status $? "PHP tests"
else
    echo -e "${YELLOW}⚠ SQLite not available, skipping PHP tests${NC}"
    echo -e "${YELLOW}  Install SQLite extension for full testing${NC}"
fi

echo ""
echo -e "${GREEN}🎉 All checks completed successfully!${NC}"
echo -e "${GREEN}   Your code is ready for production.${NC}"
