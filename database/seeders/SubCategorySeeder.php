<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\SubCategory;

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
        ['name' => 'Radikalisme', 'slug' => 'radikalisme', 'description' => 'Paham radikal yang berpotensi merusak persatuan'],
        ['name' => 'Separatisme', 'slug' => 'separatisme', 'description' => 'Gerakan pemisahan diri dari NKRI'],
        ['name' => 'Ekstremisme', 'slug' => 'ekstremisme', 'description' => 'Paham ekstrem yang merugikan masyarakat'],
        ['name' => 'Komunisme', 'slug' => 'komunisme', 'description' => 'Paham komunis yang dilarang di Indonesia'],
        ['name' => 'Liberalisme', 'slug' => 'liberalisme', 'description' => 'Paham liberal yang berlebihan'],
      ],

      // Politik
      'politik' => [
        ['name' => 'Korupsi', 'slug' => 'korupsi', 'description' => 'Tindak pidana korupsi di institusi publik'],
        ['name' => 'Pilkada', 'slug' => 'pilkada', 'description' => 'Dinamika pemilihan kepala daerah'],
        ['name' => 'Demonstrasi', 'slug' => 'demonstrasi', 'description' => 'Aksi demonstrasi dan unjuk rasa'],
        ['name' => 'Money Politics', 'slug' => 'money-politics', 'description' => 'Politik uang dalam pemilihan'],
        ['name' => 'Konflik Elite', 'slug' => 'konflik-elite', 'description' => 'Konflik antar elite politik'],
      ],

      // Ekonomi
      'ekonomi' => [
        ['name' => 'Inflasi', 'slug' => 'inflasi', 'description' => 'Kondisi inflasi dan daya beli masyarakat'],
        ['name' => 'Kemiskinan', 'slug' => 'kemiskinan', 'description' => 'Tingkat kemiskinan dan kesejahteraan'],
        ['name' => 'Pengangguran', 'slug' => 'pengangguran', 'description' => 'Tingkat pengangguran dan lapangan kerja'],
        ['name' => 'UMKM', 'slug' => 'umkm', 'description' => 'Kondisi usaha mikro kecil menengah'],
        ['name' => 'Investasi', 'slug' => 'investasi', 'description' => 'Iklim investasi dan penanaman modal'],
      ],

      // Sosial Budaya
      'sosial-budaya' => [
        ['name' => 'Konflik Etnis', 'slug' => 'konflik-etnis', 'description' => 'Konflik antar suku dan etnis'],
        ['name' => 'Toleransi', 'slug' => 'toleransi', 'description' => 'Tingkat toleransi antar umat beragama'],
        ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'description' => 'Kondisi dan kualitas pendidikan'],
        ['name' => 'Kesehatan', 'slug' => 'kesehatan', 'description' => 'Kondisi kesehatan masyarakat'],
        ['name' => 'Narkoba', 'slug' => 'narkoba', 'description' => 'Penyalahgunaan narkotika dan obat terlarang'],
      ],

      // Keamanan
      'keamanan' => [
        ['name' => 'Kriminalitas', 'slug' => 'kriminalitas', 'description' => 'Tindak pidana umum dan kejahatan'],
        ['name' => 'Terorisme', 'slug' => 'terorisme', 'description' => 'Ancaman terorisme dan radikalisme'],
        ['name' => 'Gangguan Kamtibmas', 'slug' => 'gangguan-kamtibmas', 'description' => 'Gangguan keamanan dan ketertiban masyarakat'],
        ['name' => 'Bencana Alam', 'slug' => 'bencana-alam', 'description' => 'Bencana alam dan mitigasi risiko'],
        ['name' => 'Cyber Crime', 'slug' => 'cyber-crime', 'description' => 'Kejahatan siber dan teknologi'],
      ],
    ];

    foreach ($subCategories as $categorySlug => $subs) {
      $category = Category::where('slug', $categorySlug)->first();

      if (!$category) {
        echo "Category {$categorySlug} tidak ditemukan\n";
        continue;
      }

      foreach ($subs as $index => $sub) {
        SubCategory::create([
          'category_id' => $category->id,
          'name' => $sub['name'],
          'slug' => $sub['slug'],
          'description' => $sub['description'],
          'sort_order' => $index + 1,
        ]);

        echo "SubCategory {$sub['name']} dalam {$category->name} berhasil ditambahkan\n";
      }
    }

    echo "SubCategorySeeder selesai dijalankan\n";
  }
}
