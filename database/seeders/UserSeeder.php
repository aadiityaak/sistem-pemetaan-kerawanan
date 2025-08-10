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

        // Create regular users for testing with different provinces
        $provinsiJakarta = \App\Models\Provinsi::where('nama', 'DKI Jakarta')->first();
        $provinciBali = \App\Models\Provinsi::where('nama', 'Bali')->first();
        $provinsiJabar = \App\Models\Provinsi::where('nama', 'Jawa Barat')->first();

        User::updateOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Test User Jakarta',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
            'provinsi_id' => $provinsiJakarta?->id,
        ]);

        User::updateOrCreate([
            'email' => 'user.bali@example.com',
        ], [
            'name' => 'Test User Bali',
            'email' => 'user.bali@example.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
            'provinsi_id' => $provinciBali?->id,
        ]);

        User::updateOrCreate([
            'email' => 'user.jabar@example.com',
        ], [
            'name' => 'Test User Jawa Barat',
            'email' => 'user.jabar@example.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
            'provinsi_id' => $provinsiJabar?->id,
        ]);

        $this->command->info('Regular users created/updated:');
        $this->command->info('- user@example.com / user123 (Jakarta)');
        $this->command->info('- user.bali@example.com / user123 (Bali)');
        $this->command->info('- user.jabar@example.com / user123 (Jawa Barat)');
    }
}
