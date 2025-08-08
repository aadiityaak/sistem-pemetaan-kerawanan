<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Ideologi' => [
                'description' => 'Monitoring aspek ideologi dan paham yang berkembang',
                'icon' => '🧠',
                'color' => '#3B82F6',
                'children' => [
                    ['name' => 'Ideologi Kanan', 'icon' => '🟦'],
                    ['name' => 'Ideologi Kiri', 'icon' => '🟥'],
                    ['name' => 'Isu Menonjol', 'icon' => '📌'],
                ],
            ],
            'Politik' => [
                'description' => 'Monitoring dinamika politik dan pemerintahan',
                'icon' => '🏛️',
                'color' => '#8B5CF6',
                'children' => [
                    ['name' => 'Dalam Negeri', 'icon' => '🏠'],
                    ['name' => 'Luar Negeri', 'icon' => '🌍'],
                    ['name' => 'Isu Menonjol', 'icon' => '📌'],
                ],
            ],
            'Ekonomi' => [
                'description' => 'Monitoring kondisi ekonomi dan kesejahteraan',
                'icon' => '💰',
                'color' => '#10B981',
                'children' => [
                    ['name' => 'Export Import', 'icon' => '🚢'],
                    ['name' => 'Harga Sembako', 'icon' => '🛒'],
                    ['name' => 'Index Pendapatan masyarakat', 'icon' => '📈'],
                    ['name' => 'Kesenjangan Sosial', 'icon' => '⚖️'],
                    ['name' => 'Ekonomi Asing', 'icon' => '💱'],
                    ['name' => 'Pro Kontra Proyek Strategis Nasional', 'icon' => '🏗️'],
                    ['name' => 'Korupsi', 'icon' => '🕳️'],
                    ['name' => 'Isu Menonjol', 'icon' => '📌'],
                ],
            ],
            'Sosial Budaya' => [
                'description' => 'Monitoring aspek sosial budaya masyarakat',
                'icon' => '🎭',
                'color' => '#EC4899',
                'children' => [
                    ['name' => 'Ormas', 'icon' => '👥'],
                    ['name' => 'Bencana Alam', 'icon' => '🌪️'],
                    ['name' => 'Unjuk rasa', 'icon' => '📢'],
                    ['name' => 'Konflik sosial', 'icon' => '⚔️'],
                    ['name' => 'PHK', 'icon' => '📉'],
                    ['name' => 'SARA', 'icon' => '🧬'],
                    ['name' => 'Isu Menonjol', 'icon' => '📌'],
                ],
            ],
            'Keamanan' => [
                'description' => 'Monitoring situasi keamanan dan ketertiban',
                'icon' => '🛡️',
                'color' => '#F59E0B',
                'children' => [
                    ['name' => 'Teror', 'icon' => '💣'],
                    ['name' => 'Kemanan Negara', 'icon' => '🚓'],
                    ['name' => 'Isu Menonjol', 'icon' => '📌'],
                ],
            ],
        ];

        $sort_order = 1;
        foreach ($data as $mainCategoryName => $mainCategoryData) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($mainCategoryName)],
                [
                    'name' => $mainCategoryName,
                    'description' => $mainCategoryData['description'],
                    'icon' => $mainCategoryData['icon'],
                    'color' => $mainCategoryData['color'],
                    'sort_order' => $sort_order++,
                ]
            );

            echo "✅ Kategori utama '{$mainCategoryName}' berhasil ditambahkan\n";

            $sub_sort = 1;
            foreach ($mainCategoryData['children'] as $child) {
                SubCategory::updateOrCreate(
                    ['slug' => Str::slug($mainCategoryName . '-' . $child['name'])],
                    [
                        'name' => $child['name'],
                        'sort_order' => $sub_sort++,
                        'category_id' => $parent->id,
                        'icon' => $child['icon'] ?? null,
                    ]
                );
                echo "   ↳ Subkategori '{$child['name']}' ditambahkan (icon: {$child['icon']})\n";
            }
        }

        echo "🎉 CategorySeeder selesai dijalankan\n";
    }
}
