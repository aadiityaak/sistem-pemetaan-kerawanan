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
        // Create super admin user
        User::updateOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create admin vip user (can view all but not edit)
        User::updateOrCreate([
            'email' => 'adminvip@example.com',
        ], [
            'name' => 'Admin VIP',
            'email' => 'adminvip@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin_vip',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super Admin user created/updated: superadmin@example.com / password');
        $this->command->info('Admin VIP user created/updated: adminvip@example.com / password');

        // Create admin users for testing with different provinces
        $provinsiJakarta = \App\Models\Provinsi::where('nama', 'DKI Jakarta')->first();
        $provinciBali = \App\Models\Provinsi::where('nama', 'Bali')->first();
        $provinsiJabar = \App\Models\Provinsi::where('nama', 'Jawa Barat')->first();

        User::updateOrCreate([
            'email' => 'admin.jakarta@example.com',
        ], [
            'name' => 'Admin Jakarta',
            'email' => 'admin.jakarta@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'provinsi_id' => $provinsiJakarta?->id,
        ]);

        User::updateOrCreate([
            'email' => 'admin.bali@example.com',
        ], [
            'name' => 'Admin Bali',
            'email' => 'admin.bali@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'provinsi_id' => $provinciBali?->id,
        ]);

        User::updateOrCreate([
            'email' => 'admin.jabar@example.com',
        ], [
            'name' => 'Admin Jawa Barat',
            'email' => 'admin.jabar@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'provinsi_id' => $provinsiJabar?->id,
        ]);

        $this->command->info('Regional admin users created/updated:');
        $this->command->info('- admin.jakarta@example.com / admin123 (Jakarta)');
        $this->command->info('- admin.bali@example.com / admin123 (Bali)');
        $this->command->info('- admin.jabar@example.com / admin123 (Jawa Barat)');
    }
}
