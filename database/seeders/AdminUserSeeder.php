<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        if (!User::where('role', 'admin')->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created: admin@example.com / admin123');
        } else {
            $this->command->info('Admin user already exists.');
        }

        // Create a regular user for testing
        if (!User::where('email', 'user@example.com')->exists()) {
            User::create([
                'name' => 'Test User',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Regular user created: user@example.com / user123');
        } else {
            $this->command->info('Regular user already exists.');
        }
    }
}
