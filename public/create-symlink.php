<?php

/**
 * Create Storage Symlink Script
 * 
 * This file creates a symbolic link from public/storage to Laravel's storage/app/public
 * Use this if you cannot run 'php artisan storage:link' via SSH
 * 
 * Instructions:
 * 1. Upload this file to public_html/
 * 2. Access: https://your-domain.com/create-symlink.php
 * 3. Delete this file after use for security
 */

try {
  // Include Laravel autoloader and bootstrap
  require_once "../laravel-app/vendor/autoload.php";
  $app = require_once "../laravel-app/bootstrap/app.php";

  // Get Artisan kernel
  $artisan = $app->make("Illuminate\Contracts\Console\Kernel");

  // Run storage:link command
  $exitCode = $artisan->call("storage:link");

  if ($exitCode === 0) {
    echo "✅ Storage symlink created successfully!<br>";
    echo "Files uploaded to storage will now be accessible from the web.<br><br>";
    echo "🔒 <strong>IMPORTANT:</strong> Please delete this file immediately for security!";
  } else {
    echo "❌ Failed to create storage symlink.<br>";
    echo "Please try manual symlink creation or contact support.";
  }
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "<br>";
  echo "Please try manual symlink creation or contact support.";
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
    }

    .warning {
      color: #d63384;
      font-weight: bold;
    }

    .success {
      color: #198754;
    }
  </style>
</head>

<body>
  <h1>Laravel Storage Symlink Creator</h1>
  <div class="warning">
    ⚠️ Delete this file after use!
  </div>
</body>

</html>