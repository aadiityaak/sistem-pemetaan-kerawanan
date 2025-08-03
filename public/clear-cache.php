<?php

/**
 * Clear Laravel Cache Script
 * 
 * This file clears all Laravel caches when SSH access is not available
 * 
 * Instructions:
 * 1. Upload this file to public_html/
 * 2. Access: https://your-domain.com/clear-cache.php
 * 3. Delete this file after use for security
 */

try {
  // Include Laravel autoloader and bootstrap
  require_once "../laravel-app/vendor/autoload.php";
  $app = require_once "../laravel-app/bootstrap/app.php";

  // Get Artisan kernel
  $artisan = $app->make("Illuminate\Contracts\Console\Kernel");

  $results = [];

  // Clear various caches
  $caches = [
    'config:clear' => 'Configuration cache',
    'route:clear' => 'Route cache',
    'view:clear' => 'View cache',
    'cache:clear' => 'Application cache'
  ];

  foreach ($caches as $command => $description) {
    try {
      $exitCode = $artisan->call($command);
      $results[] = "✅ {$description} cleared successfully";
    } catch (Exception $e) {
      $results[] = "❌ Failed to clear {$description}: " . $e->getMessage();
    }
  }

  echo "<h2>Cache Clearing Results:</h2>";
  foreach ($results as $result) {
    echo $result . "<br>";
  }

  echo "<br>🔒 <strong>IMPORTANT:</strong> Please delete this file immediately for security!";
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "<br>";
  echo "Please check your Laravel installation or contact support.";
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Laravel Cache Cleaner</title>
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
  <h1>Laravel Cache Cleaner</h1>
  <div class="warning">
    ⚠️ Delete this file after use!
  </div>
</body>

</html>