<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subCategories = [
            // Ideologi
            'ideologi' => [
                ['name' => 'Radikalisme', 'slug' => 'radikalisme', 'icon' => '⚡', 'description' => 'Paham radikal yang berpotensi merusak persatuan'],
                ['name' => 'Separatisme', 'slug' => 'separatisme', 'icon' => '💥', 'description' => 'Gerakan pemisahan diri dari NKRI'],
                ['name' => 'Ekstremisme', 'slug' => 'ekstremisme', 'icon' => '🔥', 'description' => 'Paham ekstrem yang merugikan masyarakat'],
                ['name' => 'Komunisme', 'slug' => 'komunisme', 'icon' => '☭', 'description' => 'Paham komunis yang dilarang di Indonesia'],
                ['name' => 'Liberalisme', 'slug' => 'liberalisme', 'icon' => '🌊', 'description' => 'Paham liberal yang berlebihan'],
            ],

            // Politik
            'politik' => [
                ['name' => 'Korupsi', 'slug' => 'korupsi', 'icon' => '💰', 'description' => 'Tindak pidana korupsi di institusi publik'],
                ['name' => 'Pilkada', 'slug' => 'pilkada', 'icon' => '🗳️', 'description' => 'Dinamika pemilihan kepala daerah'],
                ['name' => 'Demonstrasi', 'slug' => 'demonstrasi', 'icon' => '📢', 'description' => 'Aksi demonstrasi dan unjuk rasa'],
                ['name' => 'Money Politics', 'slug' => 'money-politics', 'icon' => '💵', 'description' => 'Politik uang dalam pemilihan'],
                ['name' => 'Konflik Elite', 'slug' => 'konflik-elite', 'icon' => '⚔️', 'description' => 'Konflik antar elite politik'],
            ],

            // Ekonomi
            'ekonomi' => [
                ['name' => 'Inflasi', 'slug' => 'inflasi', 'icon' => '📈', 'description' => 'Kondisi inflasi dan daya beli masyarakat'],
                ['name' => 'Kemiskinan', 'slug' => 'kemiskinan', 'icon' => '🏚️', 'description' => 'Tingkat kemiskinan dan kesejahteraan'],
                ['name' => 'Pengangguran', 'slug' => 'pengangguran', 'icon' => '😞', 'description' => 'Tingkat pengangguran dan lapangan kerja'],
                ['name' => 'UMKM', 'slug' => 'umkm', 'icon' => '🏪', 'description' => 'Kondisi usaha mikro kecil menengah'],
                ['name' => 'Investasi', 'slug' => 'investasi', 'icon' => '💼', 'description' => 'Iklim investasi dan penanaman modal'],
            ],

            // Sosial Budaya
            'sosial-budaya' => [
                ['name' => 'Konflik Etnis', 'slug' => 'konflik-etnis', 'icon' => '🤺', 'description' => 'Konflik antar suku dan etnis'],
                ['name' => 'Toleransi', 'slug' => 'toleransi', 'icon' => '🤝', 'description' => 'Tingkat toleransi antar umat beragama'],
                ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'icon' => '📚', 'description' => 'Kondisi dan kualitas pendidikan'],
                ['name' => 'Kesehatan', 'slug' => 'kesehatan', 'icon' => '🏥', 'description' => 'Kondisi kesehatan masyarakat'],
                ['name' => 'Narkoba', 'slug' => 'narkoba', 'icon' => '💊', 'description' => 'Penyalahgunaan narkotika dan obat terlarang'],
            ],

            // Keamanan
            'keamanan' => [
                ['name' => 'Kriminalitas', 'slug' => 'kriminalitas', 'icon' => '🔫', 'description' => 'Tindak pidana umum dan kejahatan'],
                ['name' => 'Terorisme', 'slug' => 'terorisme', 'icon' => '💣', 'description' => 'Ancaman terorisme dan radikalisme'],
                ['name' => 'Gangguan Kamtibmas', 'slug' => 'gangguan-kamtibmas', 'icon' => '⚠️', 'description' => 'Gangguan keamanan dan ketertiban masyarakat'],
                ['name' => 'Bencana Alam', 'slug' => 'bencana-alam', 'icon' => '🌪️', 'description' => 'Bencana alam dan mitigasi risiko'],
                ['name' => 'Cyber Crime', 'slug' => 'cyber-crime', 'icon' => '💻', 'description' => 'Kejahatan siber dan teknologi'],
            ],
        ];

        foreach ($subCategories as $categorySlug => $subs) {
            $category = Category::where('slug', $categorySlug)->first();

            if (! $category) {
                echo "Category {$categorySlug} tidak ditemukan\n";

                continue;
            }

            foreach ($subs as $index => $sub) {
                SubCategory::updateOrCreate(
                    ['slug' => $sub['slug'], 'category_id' => $category->id], // Kondisi untuk mencari
                    [
                        'name' => $sub['name'],
                        'icon' => $sub['icon'] ?? '📊', // Default icon jika tidak ada
                        'description' => $sub['description'],
                        'sort_order' => $index + 1,
                    ]
                );

                echo "SubCategory {$sub['name']} ({$sub['icon']}) dalam {$category->name} berhasil diperbarui\n";
            }
        }

        echo "SubCategorySeeder selesai dijalankan\n";
    }
}
