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
                    [
                        'name' => 'Ideologi Kanan', 
                        'icon' => '🟦',
                        'description' => 'Contoh: Front Pembela Islam (FPI), Jamaah Islamiah (JI), Jamaah Ansharut Daulah (JAD), Negara Islam Indonesia (NII), Mujahidin Indonesia Barat (MIB), Mujahidin Indonesia Timur (MIT)'
                    ],
                    [
                        'name' => 'Ideologi Kiri', 
                        'icon' => '🟥',
                        'description' => 'Contoh: Partai Komunis Indonesia (PKI), Gerwani Pemuda Rakyat, Lekra, Serikat Buruh'
                    ],
                    [
                        'name' => 'Isu Menonjol', 
                        'icon' => '📌',
                        'description' => 'Isu-isu ideologi yang sedang menonjol dan mendapat perhatian publik'
                    ],
                ],
            ],
            'Politik' => [
                'description' => 'Monitoring dinamika politik dan pemerintahan',
                'icon' => '🏛️',
                'color' => '#8B5CF6',
                'children' => [
                    [
                        'name' => 'Dalam Negeri', 
                        'icon' => '🏠',
                        'description' => 'Isu politik domestik, termasuk pemilu, pilkada, dan dinamika pemerintahan dalam negeri'
                    ],
                    [
                        'name' => 'Luar Negeri', 
                        'icon' => '🌍',
                        'description' => 'Politik luar negeri, hubungan diplomatik, dan isu internasional yang mempengaruhi Indonesia'
                    ],
                    [
                        'name' => 'Isu Menonjol', 
                        'icon' => '📌',
                        'description' => 'Isu politik yang sedang mendapat sorotan dan perhatian khusus'
                    ],
                ],
            ],
            'Ekonomi' => [
                'description' => 'Monitoring kondisi ekonomi dan kesejahteraan',
                'icon' => '💰',
                'color' => '#10B981',
                'children' => [
                    [
                        'name' => 'Export Import', 
                        'icon' => '🚢',
                        'description' => 'Pajak, Arus Barang & Jasa Antar Negara, Perdagangan, Dampak Ekonomi'
                    ],
                    [
                        'name' => 'Harga Sembako', 
                        'icon' => '🛒',
                        'description' => 'Harga Beras, Sagu dan Jagung, Gula Pasir, Sayur-sayuran dan Buah-buahan, Daging Sapi dan Ayam, Minyak Goreng dan Margarin, Susu, Telur dll'
                    ],
                    [
                        'name' => 'Index Pendapatan masyarakat', 
                        'icon' => '📈',
                        'description' => 'UMR/UMK, Harga Saham, Daya Beli Masyarakat'
                    ],
                    [
                        'name' => 'Kesenjangan Sosial', 
                        'icon' => '⚖️',
                        'description' => 'Kesenjangan Ekonomi, Kesenjangan Pendidikan, Akses Kesehatan, Kesenjangan Geografis, Kesenjangan Akses Layanan Publik'
                    ],
                    [
                        'name' => 'Ekonomi Asing', 
                        'icon' => '💱',
                        'description' => 'Inflasi, Deflasi, Kebijakan Pajak'
                    ],
                    [
                        'name' => 'Pro Kontra Proyek Strategis Nasional', 
                        'icon' => '🏗️',
                        'description' => 'Gejolak Masyarakat terkait proyek-proyek strategis nasional'
                    ],
                    [
                        'name' => 'Korupsi', 
                        'icon' => '🕳️',
                        'description' => 'Isu Korupsi, OTT (Operasi tangkap tangan)'
                    ],
                    [
                        'name' => 'Isu Menonjol', 
                        'icon' => '📌',
                        'description' => 'Isu ekonomi yang sedang mendapat perhatian khusus'
                    ],
                ],
            ],
            'Sosial Budaya' => [
                'description' => 'Monitoring aspek sosial budaya masyarakat',
                'icon' => '🎭',
                'color' => '#EC4899',
                'children' => [
                    [
                        'name' => 'Ormas', 
                        'icon' => '👥',
                        'description' => 'Organisasi masyarakat: Umum, Keagamaan, AMP (Asosiasi Papua Merdeka)'
                    ],
                    [
                        'name' => 'Bencana Alam', 
                        'icon' => '🌪️',
                        'description' => 'Tanah Longsor, Banjir, Gunung Meletus'
                    ],
                    [
                        'name' => 'Unjuk rasa', 
                        'icon' => '📢',
                        'description' => 'Demonstrasi dan aksi protes masyarakat'
                    ],
                    [
                        'name' => 'Konflik sosial', 
                        'icon' => '⚔️',
                        'description' => 'Konflik: Pertambangan, Perkebunan, Antar kelompok & agama, Antar Negara'
                    ],
                    [
                        'name' => 'PHK', 
                        'icon' => '📉',
                        'description' => 'Isu Pemutusan Hubungan Kerja dan dampaknya'
                    ],
                    [
                        'name' => 'SARA', 
                        'icon' => '🧬',
                        'description' => 'Isu Suku, Ras, Agama yang berpotensi menimbulkan konflik'
                    ],
                    [
                        'name' => 'Isu Menonjol', 
                        'icon' => '📌',
                        'description' => 'Isu sosial budaya yang sedang mendapat perhatian khusus'
                    ],
                ],
            ],
            'Keamanan' => [
                'description' => 'Monitoring situasi keamanan dan ketertiban',
                'icon' => '🛡️',
                'color' => '#F59E0B',
                'children' => [
                    [
                        'name' => 'Teror', 
                        'icon' => '💣',
                        'description' => 'Teroris, Ancaman Bom, Ledakan BOM'
                    ],
                    [
                        'name' => 'Keamanan Negara', 
                        'icon' => '🚓',
                        'description' => 'KKB, RMS (Republik Maluku Selatan), Isu Aceh Merdeka, Bentrok TNI POLRI'
                    ],
                    [
                        'name' => 'Isu Menonjol', 
                        'icon' => '📌',
                        'description' => 'Isu keamanan yang sedang mendapat perhatian khusus'
                    ],
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
                        'description' => $child['description'] ?? null,
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
