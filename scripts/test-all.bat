@echo off
REM Script untuk menjalankan semua tests dan linting di Windows

echo 🚀 Running Pemetaan Kerawanan Test Suite
echo ================================

REM 1. PHP Code Style Check
echo ➜ Checking PHP code style with Laravel Pint...
./vendor/bin/pint --test
if %errorlevel% neq 0 (
    echo ✗ PHP code style failed
    exit /b 1
)
echo ✓ PHP code style

REM 2. JavaScript/TypeScript Linting
echo ➜ Running ESLint...
call npm run lint:check
if %errorlevel% neq 0 (
    echo ✗ ESLint failed
    exit /b 1
)
echo ✓ ESLint

REM 3. TypeScript Type Check
echo ➜ Running TypeScript type check...
call npm run type-check
if %errorlevel% neq 0 (
    echo ✗ TypeScript failed
    exit /b 1
)
echo ✓ TypeScript

REM 4. Prettier Format Check
echo ➜ Checking code formatting...
call npm run format:check
if %errorlevel% neq 0 (
    echo ✗ Prettier formatting failed
    exit /b 1
)
echo ✓ Prettier formatting

REM 5. Build Check
echo ➜ Building assets...
call npm run build
if %errorlevel% neq 0 (
    echo ✗ Asset build failed
    exit /b 1
)
echo ✓ Asset build

REM 6. PHP Unit Tests (skip if SQLite not available)
echo ➜ Checking PHP unit tests...
php -m | findstr sqlite >nul
if %errorlevel% equ 0 (
    php artisan test
    if %errorlevel% neq 0 (
        echo ✗ PHP tests failed
        exit /b 1
    )
    echo ✓ PHP tests
) else (
    echo ⚠ SQLite not available, skipping PHP tests
    echo   Install SQLite extension for full testing
)

echo.
echo 🎉 All checks completed successfully!
echo    Your code is ready for production.
