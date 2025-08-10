<?php

namespace Database\Seeders;

use App\Models\IndasIndicatorType;
use Illuminate\Database\Seeder;

class IndasIndicatorTypeSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            // Economic Indicators
            [
                'name' => 'Jumlah Toko',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.4,
                'description' => 'Total number of registered shops and retail establishments',
                'is_active' => true,
            ],
            [
                'name' => 'Jumlah Pasar',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.3,
                'description' => 'Total number of traditional and modern markets',
                'is_active' => true,
            ],
            [
                'name' => 'Jumlah Bank',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.3,
                'description' => 'Total number of banks and financial institutions',
                'is_active' => true,
            ],

            // Tourism Indicators
            [
                'name' => 'Objek Wisata',
                'category' => 'pariwisata',
                'unit' => 'unit',
                'weight_factor' => 0.6,
                'description' => 'Total number of tourism destinations and attractions',
                'is_active' => true,
            ],
            [
                'name' => 'Jumlah Hotel',
                'category' => 'pariwisata',
                'unit' => 'unit',
                'weight_factor' => 0.4,
                'description' => 'Total number of hotels and accommodation facilities',
                'is_active' => true,
            ],

            // Social Indicators
            [
                'name' => 'Fasilitas Umum',
                'category' => 'sosial',
                'unit' => 'unit',
                'weight_factor' => 0.5,
                'description' => 'Number of public facilities (hospitals, schools, etc.)',
                'is_active' => true,
            ],
        ];

        foreach ($indicators as $indicator) {
            IndasIndicatorType::create($indicator);
        }
    }
}
