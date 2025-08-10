<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created/updated: admin@example.com / password');

        // Create regular user for testing
        User::updateOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Regular user created/updated: user@example.com / user123');
    }
}
