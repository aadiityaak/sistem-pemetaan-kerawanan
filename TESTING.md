# 🧪 Crime Map - Test & Lint Configuration

Konfigurasi ini mencakup setup lengkap untuk testing dan linting pada proyek Crime Map Laravel + Vue.js.

## 📋 Overview

### ✅ Komponen yang Telah Dikonfigurasi

- **Laravel Pint** - PHP Code Style Fixer
- **ESLint** - JavaScript/Vue.js Linting
- **Prettier** - Code Formatting
- **TypeScript** - Type Checking
- **PHPUnit** - PHP Unit Testing
- **GitHub Actions** - CI/CD Pipeline

## 🚀 Quick Start

### Menjalankan Semua Tests

```bash
# Test lengkap (tanpa PHP unit tests)
npm run test:all

# Test lengkap termasuk PHP unit tests (membutuhkan SQLite)
npm run test:all:with-php

# Script Windows batch
scripts/test-all.bat

# Script Unix/Linux bash
scripts/test-all.sh
```

### Individual Tests

```bash
# Frontend Tests
npm run lint:check        # ESLint check
npm run type-check         # TypeScript check
npm run format:check       # Prettier format check

# PHP Tests
npm run lint:php:check     # Laravel Pint check
npm run test:php           # PHPUnit tests

# Build
npm run build              # Asset build
```

### Auto-Fix Commands

```bash
# Fix semua masalah secara otomatis
npm run fix

# Individual fixes
npm run lint               # Auto-fix ESLint issues
npm run format             # Auto-format dengan Prettier
npm run lint:php           # Auto-fix PHP style dengan Pint
```

## ⚙️ Konfigurasi Files

### 📁 Frontend

- **`.eslintrc.js`** - ESLint configuration
- **`.prettierrc.json`** - Prettier configuration
- **`.prettierignore`** - Prettier ignore patterns
- **`tsconfig.json`** - TypeScript configuration

### 📁 Backend

- **`pint.json`** - Laravel Pint configuration
- **`phpunit.xml`** - PHPUnit configuration

### 📁 CI/CD

- **`.github/workflows/tests.yml`** - GitHub Actions workflow

### 📁 Scripts

- **`scripts/test-all.bat`** - Windows test script
- **`scripts/test-all.sh`** - Unix/Linux test script

## 🎯 GitHub Actions Workflow

### Lint Job

- ✅ PHP Code Style (Laravel Pint)
- ✅ ESLint Check
- ✅ Prettier Format Check
- ✅ TypeScript Type Check

### Test Job

- ✅ PHP 8.3 & 8.4 Matrix Testing
- ✅ Database Migration & Seeding
- ✅ Asset Building
- ✅ PHPUnit Testing with Coverage
- ✅ Codecov Integration

## 📊 Package.json Scripts

```json
{
    "scripts": {
        "build": "vite build",
        "dev": "vite",
        "format": "prettier --write resources/",
        "format:check": "prettier --check resources/",
        "lint": "eslint . --fix",
        "lint:check": "eslint .",
        "lint:php": "php ./vendor/bin/pint",
        "lint:php:check": "php ./vendor/bin/pint --test",
        "type-check": "vue-tsc --noEmit",
        "test": "npm run lint:check && npm run type-check && npm run format:check",
        "test:php": "php artisan test",
        "test:coverage": "php artisan test --coverage",
        "test:all": "npm run test && npm run lint:php:check && npm run build",
        "test:all:with-php": "npm run test:all && npm run test:php",
        "ci": "npm run test:all",
        "fix": "npm run lint && npm run format && npm run lint:php"
    }
}
```

## 🛠️ Development Workflow

### Pre-commit Checklist

1. `npm run fix` - Auto-fix semua issues
2. `npm run test:all` - Jalankan semua tests
3. `git add` dan `git commit`

### Debugging

- **ESLint Issues**: Check `.eslintrc.js` configuration
- **Prettier Issues**: Check `.prettierrc.json` configuration
- **PHP Style Issues**: Check `pint.json` configuration
- **Type Issues**: Check `tsconfig.json` configuration

## 💡 Tips

### Performance

- Gunakan `npm run fix` sebelum commit untuk auto-fix issues
- Jalankan `npm run test` untuk quick check tanpa build
- Gunakan `npm run test:all` untuk full testing

### Development

- Setup IDE integration untuk real-time linting
- Configure pre-commit hooks untuk otomatis
- Gunakan `--fix` flags saat development

## 🔧 Troubleshooting

### SQLite Issues

```bash
# Check PHP extensions
php -m | grep sqlite

# Install SQLite (tergantung OS)
# Windows: Download PHP with sqlite
# Ubuntu: sudo apt-get install php-sqlite3
# macOS: brew install php --with-sqlite
```

### Node Dependencies

```bash
# Clean install
rm -rf node_modules package-lock.json
npm install
```

### PHP Dependencies

```bash
# Clean install
rm -rf vendor composer.lock
composer install
```

---

## 📈 Status

### ✅ Working

- Laravel Pint PHP linting
- ESLint JavaScript/Vue linting
- Prettier code formatting
- TypeScript type checking
- Asset building
- GitHub Actions CI/CD

### ⚠️ Requires Setup

- SQLite extension untuk PHP unit tests
- Codecov token untuk coverage reporting

### 🎯 Ready for Production

Konfigurasi ini siap digunakan untuk development dan production dengan:

- Automated testing pipeline
- Code quality enforcement
- Consistent formatting
- Type safety
- Build verification
