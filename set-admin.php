<?php
/**
 * Script to set the first user as admin role
 * Run this in production after deployment
 */

// Get the first user
$user = App\Models\User::first();

if ($user) {
    // Update user to admin role and ensure they're active
    $user->update([
        'role' => 'admin',
        'is_active' => true
    ]);
    
    echo "✅ User updated successfully!" . PHP_EOL;
    echo "ID: " . $user->id . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Role: " . $user->role . PHP_EOL;
    echo "Status: " . ($user->is_active ? 'Active' : 'Inactive') . PHP_EOL;
    echo PHP_EOL;
    echo "🎉 The user can now access admin features including:" . PHP_EOL;
    echo "   - User Management (/users)" . PHP_EOL;
    echo "   - Application Settings (/settings)" . PHP_EOL;
    echo "   - All system features" . PHP_EOL;
} else {
    echo "❌ No users found in database." . PHP_EOL;
    echo "Please register a user first, then run this script." . PHP_EOL;
}