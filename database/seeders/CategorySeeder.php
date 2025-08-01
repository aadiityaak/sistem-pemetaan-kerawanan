<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categories = [
      [
        'name' => 'Ideologi',
        'slug' => 'ideologi',
        'description' => 'Monitoring aspek ideologi dan paham yang berkembang',
        'icon' => 'Users',
        'color' => '#3B82F6', // Blue
        'sort_order' => 1,
      ],
      [
        'name' => 'Politik',
        'slug' => 'politik',
        'description' => 'Monitoring dinamika politik dan pemerintahan',
        'icon' => 'Landmark',
        'color' => '#8B5CF6', // Purple
        'sort_order' => 2,
      ],
      [
        'name' => 'Ekonomi',
        'slug' => 'ekonomi',
        'description' => 'Monitoring kondisi ekonomi dan kesejahteraan',
        'icon' => 'DollarSign',
        'color' => '#10B981', // Green
        'sort_order' => 3,
      ],
      [
        'name' => 'Sosial Budaya',
        'slug' => 'sosial-budaya',
        'description' => 'Monitoring aspek sosial budaya masyarakat',
        'icon' => 'Heart',
        'color' => '#EC4899', // Pink
        'sort_order' => 4,
      ],
      [
        'name' => 'Keamanan',
        'slug' => 'keamanan',
        'description' => 'Monitoring situasi keamanan dan ketertiban',
        'icon' => 'Shield',
        'color' => '#F59E0B', // Orange
        'sort_order' => 5,
      ],
    ];

    foreach ($categories as $category) {
      Category::create($category);
      echo "Category {$category['name']} berhasil ditambahkan\n";
    }

    echo "CategorySeeder selesai dijalankan\n";
  }
}
