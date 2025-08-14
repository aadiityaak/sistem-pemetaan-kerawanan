<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menu items
        \App\Models\MenuItem::truncate();

        // Main menu items
        $ipoleksosbudkam = \App\Models\MenuItem::create([
            'title' => 'IPOLEKSOSBUDKAM',
            'icon' => 'LayoutGrid',
            'path' => '/dashboard',
            'is_active' => true,
            'sort_order' => 1,
            'admin_only' => false,
            'description' => 'Dashboard utama sistem monitoring',
        ]);

        // IPOLEKSOSBUDKAM submenus berdasarkan kategori
        \App\Models\MenuItem::create([
            'title' => 'Ideologi',
            'icon' => 'Users',
            'path' => '/dashboard?category=ideologi',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $ipoleksosbudkam->id,
            'admin_only' => false,
            'description' => 'Monitoring aspek ideologi',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Politik',
            'icon' => 'Landmark',
            'path' => '/dashboard?category=politik',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $ipoleksosbudkam->id,
            'admin_only' => false,
            'description' => 'Monitoring aspek politik',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Ekonomi',
            'icon' => 'DollarSign',
            'path' => '/dashboard?category=ekonomi',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $ipoleksosbudkam->id,
            'admin_only' => false,
            'description' => 'Monitoring aspek ekonomi',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Sosial Budaya',
            'icon' => 'Heart',
            'path' => '/dashboard?category=sosial-budaya',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $ipoleksosbudkam->id,
            'admin_only' => false,
            'description' => 'Monitoring aspek sosial budaya',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Keamanan',
            'icon' => 'Shield',
            'path' => '/dashboard?category=keamanan',
            'is_active' => true,
            'sort_order' => 5,
            'parent_id' => $ipoleksosbudkam->id,
            'admin_only' => false,
            'description' => 'Monitoring aspek keamanan',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'ISU NEGATIF ANGGOTA BRIMOB',
            'icon' => 'ShieldAlert',
            'path' => '/dashboard?category=isu-negatif-anggota-brimob',
            'is_active' => true,
            'sort_order' => 2,
            'admin_only' => false,
            'description' => 'Monitoring isu negatif anggota BRIMOB',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'KALENDER KAMTIBMAS',
            'icon' => 'Calendar',
            'path' => '/event?event=kamtibmas',
            'is_active' => true,
            'sort_order' => 3,
            'admin_only' => false,
            'description' => 'Kalender keamanan dan ketertiban masyarakat',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'AGENDA',
            'icon' => 'CalendarDays',
            'path' => '/event?event=agenda',
            'is_active' => true,
            'sort_order' => 4,
            'admin_only' => false,
            'description' => 'Agenda kegiatan',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'AGENDA INTERNAL KORP BRIMOB POLRI',
            'icon' => 'Calendar',
            'path' => '/agenda-internal-korp-brimob',
            'is_active' => true,
            'sort_order' => 5,
            'admin_only' => false,
            'description' => 'Agenda internal korps BRIMOB POLRI',
        ]);

        // INDAS menu with submenu
        $indas = \App\Models\MenuItem::create([
            'title' => 'INDAS',
            'icon' => 'ScrollText',
            'path' => '/indas',
            'is_active' => true,
            'sort_order' => 6,
            'admin_only' => false,
            'description' => 'Sistem Analisis Data Intelijen',
        ]);

        // INDAS submenus
        \App\Models\MenuItem::create([
            'title' => 'Dashboard',
            'icon' => 'LayoutGrid',
            'path' => '/indas',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $indas->id,
            'admin_only' => false,
            'description' => 'Dashboard INDAS',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Input Data',
            'icon' => 'Database',
            'path' => '/indas/data-entry',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $indas->id,
            'admin_only' => false,
            'description' => 'Input data indikator INDAS',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Indicator Types',
            'icon' => 'Settings',
            'path' => '/indas/indicators',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $indas->id,
            'admin_only' => false,
            'description' => 'Kelola jenis indikator INDAS',
        ]);

        // DATA CENTER menu with submenus
        $dataCenter = \App\Models\MenuItem::create([
            'title' => 'DATA CENTER',
            'icon' => 'ShieldAlert',
            'path' => '/monitoring-data',
            'is_active' => true,
            'sort_order' => 7,
            'admin_only' => false,
            'description' => 'Pusat data monitoring dan analisis',
        ]);

        // DATA CENTER submenus
        \App\Models\MenuItem::create([
            'title' => 'Data Monitoring',
            'icon' => 'Database',
            'path' => '/monitoring-data',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $dataCenter->id,
            'admin_only' => false,
            'description' => 'Monitoring data kejadian',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Partai Politik',
            'icon' => 'Flag',
            'path' => '/partai-politik',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $dataCenter->id,
            'admin_only' => false,
            'description' => 'Data partai politik',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Sembako',
            'icon' => 'ShoppingCart',
            'path' => '/sembako',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $dataCenter->id,
            'admin_only' => false,
            'description' => 'Data harga sembako',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Kurs Mata Uang',
            'icon' => 'DollarSign',
            'path' => '/pasar-saham',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $dataCenter->id,
            'admin_only' => false,
            'description' => 'Data kurs mata uang dan pasar saham',
        ]);

        // Admin-only submenus for DATA CENTER
        \App\Models\MenuItem::create([
            'title' => 'Kategori',
            'icon' => 'Tags',
            'path' => '/categories',
            'is_active' => true,
            'sort_order' => 5,
            'parent_id' => $dataCenter->id,
            'admin_only' => true,
            'description' => 'Kelola kategori',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Sub Kategori',
            'icon' => 'FileText',
            'path' => '/sub-categories',
            'is_active' => true,
            'sort_order' => 6,
            'parent_id' => $dataCenter->id,
            'admin_only' => true,
            'description' => 'Kelola sub kategori',
        ]);

        // PENGATURAN menu with submenus
        $pengaturan = \App\Models\MenuItem::create([
            'title' => 'PENGATURAN',
            'icon' => 'Settings',
            'path' => '/settings',
            'is_active' => true,
            'sort_order' => 8,
            'admin_only' => false,
            'description' => 'Pengaturan sistem',
        ]);

        // Admin-only submenus for PENGATURAN
        \App\Models\MenuItem::create([
            'title' => 'Pengaturan Aplikasi',
            'icon' => 'Settings',
            'path' => '/settings',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $pengaturan->id,
            'admin_only' => true,
            'description' => 'Pengaturan aplikasi',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'User Management',
            'icon' => 'Users',
            'path' => '/users',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $pengaturan->id,
            'admin_only' => true,
            'description' => 'Kelola pengguna',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Menu Management',
            'icon' => 'Menu',
            'path' => '/admin/menu-items',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $pengaturan->id,
            'admin_only' => true,
            'description' => 'Kelola menu navigasi',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Provinsi',
            'icon' => 'Map',
            'path' => '/provinsi',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $pengaturan->id,
            'admin_only' => false,
            'description' => 'Data provinsi',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Kabupaten/Kota',
            'icon' => 'Building2',
            'path' => '/kabupaten-kota',
            'is_active' => true,
            'sort_order' => 5,
            'parent_id' => $pengaturan->id,
            'admin_only' => false,
            'description' => 'Data kabupaten/kota',
        ]);

        \App\Models\MenuItem::create([
            'title' => 'Kecamatan',
            'icon' => 'MapPin',
            'path' => '/kecamatan',
            'is_active' => true,
            'sort_order' => 6,
            'parent_id' => $pengaturan->id,
            'admin_only' => false,
            'description' => 'Data kecamatan',
        ]);

        // Admin-only menu
        \App\Models\MenuItem::create([
            'title' => 'PREDIKSI AI',
            'icon' => 'Brain',
            'path' => '/ai-prediction',
            'is_active' => true,
            'sort_order' => 9,
            'admin_only' => true,
            'description' => 'Prediksi menggunakan artificial intelligence',
        ]);

        $this->command->info('Menu items seeded successfully!');
    }
}
