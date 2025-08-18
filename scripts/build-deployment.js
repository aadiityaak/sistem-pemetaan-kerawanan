#!/usr/bin/env bun

import { cpSync, existsSync, mkdirSync, readdirSync, readFileSync, rmSync, writeFileSync } from 'fs';
import JSZip from 'jszip';
import { dirname, join, resolve } from 'path';

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
    // 'storage', // Excluded - should be created fresh on deployment
    'vendor',
    'artisan',
    'composer.json',
    'composer.lock',
];

// Files to include in laravel-app directory
const laravelAppPublicFiles = [
    'public/build/manifest.json',
];

const includePublicFiles = [
    'public/build',
    'public/favicon.ico',
    'public/favicon.svg',
    'public/apple-touch-icon.png',
    'public/robots.txt',
    'public/.htaccess',
    'public/index.php',
    'public/create-symlink.php',
    'public/create-symlink-advanced.php',
    'public/clear-cache.php',
];

// Create laravel-app folder (untuk di atas public_html)
const laravelAppDir = join(buildDir, 'laravel-app');
mkdirSync(laravelAppDir, { recursive: true });

// Copy Laravel files
console.log('📦 Copying Laravel application files...');
process.stdout.write('   ');
includeFiles.forEach((file) => {
    const srcPath = join(projectRoot, file);
    const destPath = join(laravelAppDir, file);

    if (existsSync(srcPath)) {
        try {
            cpSync(srcPath, destPath, { recursive: true });
            // Reduced logging - only show progress dot
            process.stdout.write('.');
        } catch (error) {
            console.log(`\n   ✗ Failed to copy ${file}: ${error.message}`);
        }
    } else {
        console.log(`   ⚠ ${file} not found, skipping...`);
    }
});
console.log(' ✓');

// Copy specific public files to laravel-app directory (for Laravel to find)
console.log('📦 Copying public files to laravel-app...');
process.stdout.write('   ');
laravelAppPublicFiles.forEach((file) => {
    const srcPath = join(projectRoot, file);
    const destPath = join(laravelAppDir, file);
    
    if (existsSync(srcPath)) {
        try {
            // Create directory if needed
            const destDir = dirname(destPath);
            if (!existsSync(destDir)) {
                mkdirSync(destDir, { recursive: true });
            }
            
            cpSync(srcPath, destPath, { recursive: true });
            process.stdout.write('.');
        } catch (error) {
            console.log(`\n   ✗ Failed to copy ${file}: ${error.message}`);
        }
    } else {
        console.log(`\n   ⚠ ${file} not found, skipping...`);
    }
});
console.log(' ✓\n');

// Create public_html folder
const publicHtmlDir = join(buildDir, 'public_html');
mkdirSync(publicHtmlDir, { recursive: true });

// Copy public files to public_html
console.log('📦 Copying public files...');
process.stdout.write('   ');
includePublicFiles.forEach((file) => {
    const srcPath = join(projectRoot, file);
    const fileName = file.replace('public/', '').replace('public\\', '');
    const destPath = join(publicHtmlDir, fileName);

    // Skip hot file that might reference dev server
    if (fileName === 'hot') {
        console.log(`   ⚠ Skipping ${fileName} (dev server reference)...`);
        return;
    }

    // Skip any Vite dev server references
    if (fileName.includes('vite') && fileName.includes('dev')) {
        console.log(`   ⚠ Skipping ${fileName} (dev server reference)...`);
        return;
    }

    if (existsSync(srcPath)) {
        try {
            // Create directory if needed
            const destDir = dirname(destPath);
            if (!existsSync(destDir)) {
                mkdirSync(destDir, { recursive: true });
            }

            cpSync(srcPath, destPath, { recursive: true });
            // Reduced logging - only show progress dot
            process.stdout.write('.');
        } catch (error) {
            console.log(`   ✗ Failed to copy ${fileName}: ${error.message}`);
        }
    } else {
        console.log(`   ⚠ ${file} not found, skipping...`);
    }
});
console.log(' ✓\n');

// Modify index.php for shared hosting
console.log('🔧 Modifying index.php for shared hosting...');
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

// Copy existing .env.production if exists, otherwise create template
console.log('⚙️ Setting up .env.production...');
const sourceEnvProd = join(projectRoot, '.env.production');
const targetEnvProd = join(laravelAppDir, '.env.production');

if (existsSync(sourceEnvProd)) {
    // Copy existing .env.production
    cpSync(sourceEnvProd, targetEnvProd);
    console.log('   ✓ Copied existing .env.production');
} else {
    // Create .env.production template if not exists
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

    writeFileSync(targetEnvProd, envTemplate);
    console.log('   ✓ .env.production template created');
}

// Create deployment instructions
console.log('📝 Creating deployment instructions...');
const instructions = `// Create deployment instructions
console.log('📝 Creating deployment instructions...');
const deploymentInstructions = [
    '# Crime Map Deployment Instructions',
    '',
    '## File Structure Setelah Upload:',
    '/root-hosting-directory/',
    '├── laravel-app/           # Laravel application files',
    '└── public_html/           # Public web files',
    '    ├── build/             # Built assets',
    '    ├── index.php         # Modified entry point',
    '    └── .htaccess         # URL rewriting rules',
    '',
    '## Step-by-Step Deployment:',
    '',
    '### 1. Upload Files',
    '- Extract crime-map-deployment.zip',
    '- Upload folder laravel-app/ to root hosting directory (bukan di public_html)',
    '- Upload isi folder public_html/ ke direktori public_html hosting',
    '',
    '### 2. Configure Environment',
    '- Copy .env.production di laravel-app/ menjadi .env',
    '- Edit .env sesuai database dan domain hosting:',
    '  APP_URL=https://your-domain.com',
    '  DB_DATABASE=your_database_name',
    '  DB_USERNAME=your_database_user',
    '  DB_PASSWORD=your_database_password',
    '',
    '### 3. Create Storage Directories & Set Permissions',
    'Storage folder tidak disertakan dalam deployment untuk keamanan.',
    'Buat struktur storage manually:',
    '',
    'Option A: Via SSH',
    'mkdir -p laravel-app/storage/app/public',
    'mkdir -p laravel-app/storage/framework/cache',
    'mkdir -p laravel-app/storage/framework/sessions',
    'mkdir -p laravel-app/storage/framework/views',
    'mkdir -p laravel-app/storage/logs',
    '',
    'Option B: Via cPanel File Manager',
    'Buat folder secara manual dengan struktur di atas',
    '',
    'Option C: Via PHP Script (jika tidak ada SSH)',
    'Upload file create-storage-dirs.php ke public_html:',
    '<?php',
    '$dirs = [',
    '    "../laravel-app/storage/app/public",',
    '    "../laravel-app/storage/framework/cache",',
    '    "../laravel-app/storage/framework/sessions",',
    '    "../laravel-app/storage/framework/views",',
    '    "../laravel-app/storage/logs"',
    '];',
    'foreach($dirs as $dir) {',
    '    if(!is_dir($dir)) mkdir($dir, 0755, true);',
    '}',
    'echo "Storage directories created! Delete this file.";',
    '?>',
    '',
    'Set File Permissions (via cPanel File Manager atau FTP):',
    'laravel-app/ - 755',
    'laravel-app/storage/ - 755 (recursive)',
    'laravel-app/bootstrap/cache/ - 755 (recursive)',
    'public_html/ - 755',
    '',
    '### 4. Create Storage Symlink (PENTING!)',
    'Untuk file upload seperti favicon dan logo, perlu symlink storage:',
    '',
    'Option A: Via SSH',
    'cd laravel-app',
    'php artisan storage:link',
    '',
    'Option B: Via File PHP (jika tidak ada SSH)',
    'Upload file create-symlink-advanced.php ke public_html/',
    'Akses: https://your-domain.com/create-symlink-advanced.php',
    'Script akan otomatis mencoba multiple metode untuk membuat symlink',
    'HAPUS file ini setelah digunakan!',
    '',
    'Option C: Manual Copy (jika A & B gagal)',
    'Via cPanel File Manager atau FTP:',
    '1. Masuk ke public_html/',
    '2. Buat folder bernama "storage"',
    '3. Copy semua file dari laravel-app/storage/app/public/ ke public_html/storage/',
    '4. Set permission folder storage menjadi 755',
    '5. Setiap kali upload file baru via Laravel, copy manual ke public_html/storage/',
    '',
    '### 5. Clear Cache (PENTING!)',
    'Via SSH atau buat file PHP temporary untuk clear cache:',
    '',
    'Option A: Via SSH',
    'cd laravel-app',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan cache:clear',
    '',
    'Option B: Via File PHP (jika tidak ada SSH)',
    'Create file clear-cache.php di public_html:',
    '',
    '<?php',
    '// clear-cache.php - Delete after use!',
    'require_once "../laravel-app/vendor/autoload.php";',
    '$app = require_once "../laravel-app/bootstrap/app.php";',
    '$artisan = $app->make("Illuminate\\Contracts\\Console\\Kernel");',
    '$artisan->call("config:clear");',
    '$artisan->call("route:clear");  ',
    '$artisan->call("view:clear");',
    '$artisan->call("cache:clear");',
    'echo "Cache cleared successfully! Please delete this file.";',
    '?>',
    '',
    'Akses: https://your-domain.com/clear-cache.php',
    'HAPUS file ini setelah digunakan!',
    '',
    '### 6. Run Database Migration',
    'cd laravel-app',
    'php artisan migrate --force',
    'php artisan db:seed --force  # Optional: seed data',
    '',
    '### 7. Troubleshooting',
    '',
    'Error: Still showing Vite dev server references',
    '- Clear browser cache (Ctrl+F5)',
    '- Delete hosting cache if any',
    '- Run clear-cache.php script',
    '- Check .env APP_URL is correct',
    '',
    'Error: 500 Internal Server Error',
    '- Check file permissions',
    '- Ensure .env exists and is readable',
    '- Check error logs in hosting panel',
    '',
    'Error: Database Connection',
    '- Verify database credentials in .env',
    '- Ensure database exists',
    '- Check if hosting requires specific host (not 127.0.0.1)',
    '',
    'Error: CSS/JS Not Loading',
    '- Verify build/ folder uploaded correctly',
    '- Check .htaccess file exists in public_html',
    '- Clear browser cache',
    '',
    'Error: File Upload/Storage Issues (Favicon, Logo)',
    '- Ensure storage symlink created (Step 4)',
    '- Check public_html/storage/ folder exists and writable',
    '- Verify laravel-app/storage/app/public/ has correct permissions',
    '- Try manual symlink creation if automatic failed',
    '',
    '### 8. Post-Deployment Checks',
    '- Homepage loads without errors',
    '- Assets (CSS/JS) loading correctly',
    '- Login/register functionality works',
    '- Maps are displaying',
    '- No console errors in browser',
    '',
    '## Security Notes:',
    '- Change APP_KEY if needed: php artisan key:generate',
    '- Set APP_DEBUG=false in production',
    '- Use HTTPS in production',
    '- Regular backup of database',
    '- Keep Laravel updated',
    '',
    '## Support:',
    'If you encounter issues, check Laravel logs at:',
    'laravel-app/storage/logs/laravel.log',
    '',
    'Happy deploying!'
].join('\n');

writeFileSync(join(buildDir, 'DEPLOYMENT-INSTRUCTIONS.txt'), deploymentInstructions);
console.log('   ✓ Deployment instructions created');

writeFileSync(join(buildDir, 'DEPLOYMENT-INSTRUCTIONS.txt'), deploymentInstructions);
console.log('   ✓ Deployment instructions created');
`;

writeFileSync(join(buildDir, 'DEPLOYMENT-INSTRUCTIONS.txt'), instructions);
console.log('   ✓ Deployment instructions created');

// Create ZIP file
// Check what's in build directory before zipping
console.log('📋 Build directory contents:');
const buildContents = readdirSync(laravelAppDir);
console.log('   laravel-app/', buildContents.join(', '));

console.log('📦 Creating ZIP file...');
const zip = new JSZip();

// Function to add directory to zip (synchronous version)
function addDirectoryToZip(dirPath, zipFolder = '') {
    const items = readdirSync(dirPath, { withFileTypes: true });

    for (const item of items) {
        const itemPath = join(dirPath, item.name);
        const zipPath = zipFolder ? `${zipFolder}/${item.name}` : item.name;

        if (item.isDirectory()) {
            addDirectoryToZip(itemPath, zipPath);
        } else {
            const fileContent = readFileSync(itemPath);
            zip.file(zipPath, fileContent);
            // Only show progress for ZIP creation - no individual file logging
            if (Math.random() < 0.01) process.stdout.write('.'); // Show occasional progress
        }
    }
}

// Add all files to zip
addDirectoryToZip(buildDir);

// Generate ZIP
console.log('🔄 Generating ZIP file...');
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
