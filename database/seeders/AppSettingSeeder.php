<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $settings = [
      [
        'key' => 'app_name',
        'value' => 'Peta Kriminal Indonesia',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Nama Aplikasi',
        'description' => 'Nama aplikasi yang akan ditampilkan di header dan title'
      ],
      [
        'key' => 'app_description',
        'value' => 'Sistem Informasi Pemetaan Data Kriminal Indonesia',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Deskripsi Aplikasi',
        'description' => 'Deskripsi singkat tentang aplikasi'
      ],
      [
        'key' => 'app_favicon',
        'value' => '/favicon.ico',
        'type' => 'image',
        'group' => 'appearance',
        'label' => 'Favicon',
        'description' => 'Icon yang ditampilkan di tab browser'
      ],
      [
        'key' => 'footer_text',
        'value' => 'Peta Kriminal Indonesia © 2024',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Teks Footer',
        'description' => 'Teks yang ditampilkan di footer aplikasi'
      ]
    ];

    foreach ($settings as $setting) {
      AppSetting::updateOrCreate(
        ['key' => $setting['key']],
        $setting
      );
    }
  }
}
