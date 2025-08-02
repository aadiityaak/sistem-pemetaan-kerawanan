#!/usr/bin/env bun

import { cpSync, existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'fs';
import JSZip from 'jszip';
import { join, resolve } from 'path';

console.log('🚀 Building deployment package for Crime Map...\n');

const projectRoot = resolve(process.cwd());
const buildDir = join(projectRoot, 'build-deployment');
const zipFile = join(projectRoot, 'crime-map-deployment.zip');

// Clean previous build
if (existsSync(buildDir)) {
    console.log('🧹 Cleaning previous build...');
    rmSync(buildDir, { recursive: true, force: true });
}

if (existsSync(zipFile)) {
    rmSync(zipFile);
}

// Create build directory
mkdirSync(buildDir, { recursive: true });

console.log('📁 Creating deployment structure...\n');

// Define what files/folders to include
const includeFiles = [
    'app',
    'bootstrap',
    'config',
    'database',
    'resources',
    'routes',
    'storage',
    'vendor',
    'artisan',
    'composer.json',
    'composer.lock',
];

const includePublicFiles = [
    'public/build',
    'public/favicon.ico',
    'public/favicon.svg',
    'public/apple-touch-icon.png',
    'public/robots.txt',
    'public/.htaccess',
    'public/index.php',
];

// Create laravel-app folder (untuk di atas public_html)
const laravelAppDir = join(buildDir, 'laravel-app');
mkdirSync(laravelAppDir, { recursive: true });

// Copy Laravel files
console.log('📦 Copying Laravel application files...');
includeFiles.forEach((file) => {
    const srcPath = join(projectRoot, file);
    const destPath = join(laravelAppDir, file);

    if (existsSync(srcPath)) {
        try {
            cpSync(srcPath, destPath, { recursive: true });
            console.log(`   ✓ ${file}`);
        } catch (error) {
            console.log(`   ✗ Failed to copy ${file}: ${error.message}`);
        }
    } else {
        console.log(`   ⚠ ${file} not found, skipping...`);
    }
});

// Create public_html folder
const publicHtmlDir = join(buildDir, 'public_html');
mkdirSync(publicHtmlDir, { recursive: true });

// Copy public files to public_html
console.log('\n📦 Copying public files...');
includePublicFiles.forEach((file) => {
    const srcPath = join(projectRoot, file);
    const fileName = file.replace('public/', '');
    const destPath = join(publicHtmlDir, fileName);

    if (existsSync(srcPath)) {
        try {
            // Create directory if needed
            const destDir = destPath.substring(0, destPath.lastIndexOf('/'));
            if (destDir !== publicHtmlDir && !existsSync(destDir)) {
                mkdirSync(destDir, { recursive: true });
            }

            cpSync(srcPath, destPath, { recursive: true });
            console.log(`   ✓ ${fileName}`);
        } catch (error) {
            console.log(`   ✗ Failed to copy ${fileName}: ${error.message}`);
        }
    } else {
        console.log(`   ⚠ ${file} not found, skipping...`);
    }
});

// Modify index.php for shared hosting
console.log('\n🔧 Modifying index.php for shared hosting...');
const indexPhpPath = join(publicHtmlDir, 'index.php');
if (existsSync(indexPhpPath)) {
    let indexContent = readFileSync(indexPhpPath, 'utf8');

    // Update paths for shared hosting structure
    indexContent = indexContent.replace("require __DIR__.'/../vendor/autoload.php';", "require __DIR__.'/../laravel-app/vendor/autoload.php';");

    indexContent = indexContent.replace(
        "$app = require_once __DIR__.'/../bootstrap/app.php';",
        "$app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';",
    );

    writeFileSync(indexPhpPath, indexContent);
    console.log('   ✓ index.php modified for shared hosting');
}

// Create .env.production template
console.log('\n⚙️ Creating .env.production template...');
const envTemplate = `APP_NAME="Crime Map"
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
`;

writeFileSync(join(laravelAppDir, '.env.production'), envTemplate);
console.log('   ✓ .env.production template created');

// Create deployment instructions
console.log('\n📝 Creating deployment instructions...');
const instructions = `# Crime Map - Deployment Instructions

## Folder Structure:
- laravel-app/ -> Upload ke folder di atas public_html (contoh: /home/username/laravel-app/)
- public_html/ -> Upload isi folder ini ke public_html/ di hosting

## Quick Setup:

1. **Upload Files:**
   - Upload folder 'laravel-app' ke home directory hosting
   - Upload isi folder 'public_html' ke public_html directory hosting

2. **Database Setup:**
   - Buat database MySQL di hosting panel
   - Import file database jika ada

3. **Environment Configuration:**
   - Rename .env.production menjadi .env
   - Edit .env dengan detail database dan domain
   - Generate APP_KEY baru: php artisan key:generate

4. **Set Permissions:**
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/

5. **Run Migrations:**
   php artisan migrate --force
   php artisan db:seed --force

6. **Optimize for Production:**
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

## File sudah dimodifikasi:
- index.php sudah disesuaikan untuk shared hosting
- Path autoload dan bootstrap sudah diupdate

## Important Notes:
- Jangan lupa generate APP_KEY baru di production
- Set APP_DEBUG=false untuk production
- Test semua fungsi setelah deployment

Built on: ${new Date().toLocaleString()}
`;

writeFileSync(join(buildDir, 'DEPLOYMENT-INSTRUCTIONS.txt'), instructions);
console.log('   ✓ Deployment instructions created');

// Create ZIP file
console.log('\n📦 Creating ZIP file...');
const zip = new JSZip();

// Function to add directory to zip
async function addDirectoryToZip(dirPath, zipFolder = '') {
    const items = (await Bun.file(dirPath).exists()) ? (await import('fs')).readdirSync(dirPath, { withFileTypes: true }) : [];

    for (const item of items) {
        const itemPath = join(dirPath, item.name);
        const zipPath = zipFolder ? `${zipFolder}/${item.name}` : item.name;

        if (item.isDirectory()) {
            await addDirectoryToZip(itemPath, zipPath);
        } else {
            const fileContent = readFileSync(itemPath);
            zip.file(zipPath, fileContent);
        }
    }
}

// Add all files to zip
await addDirectoryToZip(buildDir);

// Generate ZIP
const zipContent = await zip.generateAsync({
    type: 'nodebuffer',
    compression: 'DEFLATE',
    compressionOptions: { level: 6 },
});

writeFileSync(zipFile, zipContent);

// Calculate file size
const stats = await Bun.file(zipFile).size;
const sizeInMB = (stats / (1024 * 1024)).toFixed(2);

console.log(`   ✓ ZIP file created: crime-map-deployment.zip (${sizeInMB} MB)`);

// Clean up build directory
rmSync(buildDir, { recursive: true, force: true });

console.log('\n🎉 Deployment package ready!');
console.log(`📄 File: ${zipFile}`);
console.log(`📏 Size: ${sizeInMB} MB`);
console.log('\n📋 Next steps:');
console.log('1. Extract crime-map-deployment.zip');
console.log('2. Upload laravel-app/ to hosting root directory');
console.log('3. Upload public_html/ contents to public_html/ directory');
console.log('4. Follow DEPLOYMENT-INSTRUCTIONS.txt');
console.log('\n✨ Happy deploying!');
