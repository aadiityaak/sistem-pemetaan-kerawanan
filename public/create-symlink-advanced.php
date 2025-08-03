<?php

/**
 * Create Storage Symlink Script for Shared Hosting
 * 
 * This file creates a symbolic link from public/storage to Laravel's storage/app/public
 * Compatible with shared hosting that may not support symlinks
 * 
 * Instructions:
 * 1. Upload this file to public_html/
 * 2. Access: https://your-domain.com/create-symlink.php
 * 3. Delete this file after use for security
 */

$errors = [];
$success = [];

try {
  // Method 1: Try Laravel's storage:link command
  if (file_exists("../laravel-app/vendor/autoload.php")) {
    require_once "../laravel-app/vendor/autoload.php";
    $app = require_once "../laravel-app/bootstrap/app.php";

    $artisan = $app->make("Illuminate\Contracts\Console\Kernel");
    $exitCode = $artisan->call("storage:link");

    if ($exitCode === 0) {
      $success[] = "✅ Laravel storage:link command executed successfully";
    } else {
      $errors[] = "❌ Laravel storage:link command failed";
    }
  } else {
    $errors[] = "❌ Laravel application not found";
  }
} catch (Exception $e) {
  $errors[] = "❌ Laravel method failed: " . $e->getMessage();
}

// Method 2: Manual symlink creation (fallback)
$publicStoragePath = __DIR__ . '/storage';
$laravelStoragePath = realpath(__DIR__ . '/../laravel-app/storage/app/public');

if (!$laravelStoragePath) {
  $errors[] = "❌ Laravel storage/app/public directory not found";

  // Try to create the Laravel storage directory if it doesn't exist
  $laravelStorageDir = __DIR__ . '/../laravel-app/storage/app/public';
  if (!file_exists($laravelStorageDir)) {
    if (mkdir($laravelStorageDir, 0755, true)) {
      $success[] = "📁 Created Laravel storage/app/public directory";
      $laravelStoragePath = realpath($laravelStorageDir);
    } else {
      $errors[] = "❌ Failed to create Laravel storage/app/public directory";
    }
  }
} else {
  $success[] = "✅ Laravel storage directory found: " . $laravelStoragePath;
}

if ($laravelStoragePath) {
  // Remove existing storage if it exists
  if (file_exists($publicStoragePath)) {
    if (is_link($publicStoragePath)) {
      unlink($publicStoragePath);
      $success[] = "🗑️ Removed existing symlink";
    } elseif (is_dir($publicStoragePath)) {
      // If it's a directory, try to remove it (only if empty)
      $files = array_diff(scandir($publicStoragePath), array('.', '..'));
      if (empty($files)) {
        rmdir($publicStoragePath);
        $success[] = "🗑️ Removed empty storage directory";
      } else {
        $errors[] = "⚠️ Storage directory exists and is not empty - manual cleanup needed";
        $errors[] = "Files found: " . implode(', ', array_slice($files, 0, 5)) . (count($files) > 5 ? '...' : '');
      }
    }
  }

  // Try to create symlink only if public storage path doesn't exist
  if (!file_exists($publicStoragePath)) {
    // Ensure Laravel storage directory is accessible
    if (!is_readable($laravelStoragePath)) {
      $errors[] = "❌ Laravel storage directory is not readable";
    } elseif (!is_dir($laravelStoragePath)) {
      $errors[] = "❌ Laravel storage path exists but is not a directory";
    } else {
      // Double check that the Laravel storage directory actually exists and is accessible
      if (!file_exists($laravelStoragePath)) {
        $errors[] = "❌ Laravel storage directory does not exist: " . $laravelStoragePath;
      } else {
        // Try symlink with error suppression to catch the specific error
        $symlinkResult = @symlink($laravelStoragePath, $publicStoragePath);
        if ($symlinkResult) {
          $success[] = "✅ Symbolic link created successfully";
        } else {
          $lastError = error_get_last();
          $errorMsg = $lastError ? $lastError['message'] : 'Unknown symlink error';
          $errors[] = "❌ Failed to create symbolic link: " . $errorMsg;
          $errors[] = "Source: " . $laravelStoragePath;
          $errors[] = "Target: " . $publicStoragePath;

          // Method 3: Copy files manually (shared hosting fallback)
          if (!file_exists($publicStoragePath)) {
            if (mkdir($publicStoragePath, 0755, true)) {
              $success[] = "📁 Created storage directory as fallback";
            } else {
              $errors[] = "❌ Failed to create storage directory";
            }
          }

          if (is_dir($publicStoragePath)) {
            $success[] = "📁 Storage directory is ready for file copying";

            // Copy files from Laravel storage to public storage
            $iterator = new RecursiveIteratorIterator(
              new RecursiveDirectoryIterator($laravelStoragePath, RecursiveDirectoryIterator::SKIP_DOTS),
              RecursiveIteratorIterator::SELF_FIRST
            );

            $copiedFiles = 0;
            foreach ($iterator as $file) {
              $relativePath = str_replace($laravelStoragePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
              $target = $publicStoragePath . DIRECTORY_SEPARATOR . $relativePath;

              if ($file->isDir()) {
                if (!is_dir($target)) {
                  mkdir($target, 0755, true);
                }
              } else {
                $targetDir = dirname($target);
                if (!is_dir($targetDir)) {
                  mkdir($targetDir, 0755, true);
                }
                copy($file->getPathname(), $target);
                $copiedFiles++;
              }
            }

            $success[] = "📄 Copied {$copiedFiles} files to public storage";
            $success[] = "⚠️ Note: You'll need to manually sync files when uploading new content";
          } else {
            $errors[] = "❌ Storage directory could not be created or accessed";
          }
        }
      }
    }
  }
}

// Test the storage link
$testFile = $publicStoragePath . '/test.txt';
if (file_put_contents($testFile, 'test') !== false) {
  unlink($testFile);
  $success[] = "✅ Storage is writable and accessible";
} else {
  $errors[] = "❌ Storage is not writable";
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>Storage Symlink Creator</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      max-width: 800px;
      margin: 0 auto;
    }

    .warning {
      color: #d63384;
      font-weight: bold;
    }

    .success {
      color: #198754;
      margin: 5px 0;
    }

    .error {
      color: #dc3545;
      margin: 5px 0;
    }

    .info {
      color: #0dcaf0;
      margin: 5px 0;
    }

    .box {
      padding: 15px;
      margin: 15px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
    }

    .delete-warning {
      background: #fff3cd;
      border-color: #ffc107;
      color: #664d03;
    }

    pre {
      background: #f8f9fa;
      padding: 10px;
      border-radius: 3px;
      overflow-x: auto;
    }
  </style>
</head>

<body>
  <h1>Laravel Storage Symlink Creator</h1>

  <div class="box delete-warning">
    <strong>⚠️ SECURITY WARNING:</strong> Delete this file immediately after use!
  </div>

  <?php if (!empty($success)): ?>
    <div class="box">
      <h3>✅ Success Messages:</h3>
      <?php foreach ($success as $msg): ?>
        <div class="success"><?php echo $msg; ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="box">
      <h3>❌ Issues Found:</h3>
      <?php foreach ($errors as $error): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="box">
    <h3>📋 System Information:</h3>
    <div class="info">PHP Version: <?php echo PHP_VERSION; ?></div>
    <div class="info">Current Directory: <?php echo __DIR__; ?></div>
    <div class="info">Laravel Storage Path: <?php echo $laravelStoragePath ?: 'Not found'; ?></div>
    <div class="info">Public Storage Path: <?php echo $publicStoragePath; ?></div>
    <div class="info">Storage Link Exists: <?php echo file_exists($publicStoragePath) ? 'Yes' : 'No'; ?></div>
    <div class="info">Storage Link Type: <?php
                                          if (file_exists($publicStoragePath)) {
                                            echo is_link($publicStoragePath) ? 'Symbolic Link' : (is_dir($publicStoragePath) ? 'Directory' : 'File');
                                          } else {
                                            echo 'None';
                                          }
                                          ?></div>
  </div>

  <div class="box">
    <h3>🔧 Manual Setup (if needed):</h3>
    <p>If automatic symlink creation failed, you can manually create the storage directory:</p>
    <pre>1. Create folder: public_html/storage/
2. Set permissions: 755
3. Upload files from: laravel-app/storage/app/public/
4. Copy future uploads manually to both locations</pre>
  </div>

  <div class="box">
    <h3>🧪 Test Your Storage:</h3>
    <p>Upload a test file via your Laravel app and check if it's accessible at:</p>
    <pre><?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/storage/filename'; ?></pre>
  </div>
</body>

</html>