<?php

namespace Database\Seeders;

use App\Models\IndasIndicatorType;
use App\Models\IndasMonthlyData;
use App\Models\KabupatenKota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndasIndicatorTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data safely (handle foreign key constraints)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        IndasMonthlyData::truncate();
        IndasIndicatorType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $indicators = [
            // Economic Indicators
            [
                'name' => 'Jumlah Toko',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.25,
                'description' => 'Total jumlah toko dan usaha retail yang terdaftar',
                'is_active' => true,
            ],
            [
                'name' => 'Jumlah Pasar',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.20,
                'description' => 'Total jumlah pasar tradisional dan modern',
                'is_active' => true,
            ],
            [
                'name' => 'Jumlah Bank',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.15,
                'description' => 'Total jumlah bank dan lembaga keuangan',
                'is_active' => true,
            ],
            [
                'name' => 'UMKM Aktif',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'weight_factor' => 0.25,
                'description' => 'Jumlah Usaha Mikro Kecil Menengah yang aktif beroperasi',
                'is_active' => true,
            ],
            [
                'name' => 'Investasi Daerah',
                'category' => 'ekonomi',
                'unit' => 'miliar',
                'weight_factor' => 0.15,
                'description' => 'Total investasi yang masuk ke daerah (dalam miliar rupiah)',
                'is_active' => true,
            ],

            // Tourism Indicators
            [
                'name' => 'Objek Wisata',
                'category' => 'pariwisata',
                'unit' => 'unit',
                'weight_factor' => 0.30,
                'description' => 'Total jumlah destinasi dan objek wisata',
                'is_active' => true,
            ],
            [
                'name' => 'Jumlah Hotel',
                'category' => 'pariwisata',
                'unit' => 'unit',
                'weight_factor' => 0.25,
                'description' => 'Total jumlah hotel dan fasilitas akomodasi',
                'is_active' => true,
            ],
            [
                'name' => 'Kunjungan Wisatawan',
                'category' => 'pariwisata',
                'unit' => 'ribu orang',
                'weight_factor' => 0.35,
                'description' => 'Jumlah kunjungan wisatawan per bulan (dalam ribuan)',
                'is_active' => true,
            ],
            [
                'name' => 'Pendapatan Sektor Pariwisata',
                'category' => 'pariwisata',
                'unit' => 'miliar',
                'weight_factor' => 0.10,
                'description' => 'Pendapatan dari sektor pariwisata (dalam miliar rupiah)',
                'is_active' => true,
            ],

            // Social Indicators
            [
                'name' => 'Fasilitas Kesehatan',
                'category' => 'sosial',
                'unit' => 'unit',
                'weight_factor' => 0.25,
                'description' => 'Jumlah rumah sakit, puskesmas, dan klinik kesehatan',
                'is_active' => true,
            ],
            [
                'name' => 'Fasilitas Pendidikan',
                'category' => 'sosial',
                'unit' => 'unit',
                'weight_factor' => 0.25,
                'description' => 'Jumlah sekolah, universitas, dan lembaga pendidikan',
                'is_active' => true,
            ],
            [
                'name' => 'Tingkat Pengangguran',
                'category' => 'sosial',
                'unit' => 'persen',
                'weight_factor' => 0.20,
                'description' => 'Persentase tingkat pengangguran di daerah',
                'is_active' => true,
            ],
            [
                'name' => 'Program Bantuan Sosial',
                'category' => 'sosial',
                'unit' => 'program',
                'weight_factor' => 0.15,
                'description' => 'Jumlah program bantuan sosial yang berjalan',
                'is_active' => true,
            ],
            [
                'name' => 'Infrastruktur Transportasi',
                'category' => 'sosial',
                'unit' => 'km',
                'weight_factor' => 0.15,
                'description' => 'Panjang jalan dan infrastruktur transportasi (dalam kilometer)',
                'is_active' => true,
            ],
        ];

        $createdIndicators = [];
        foreach ($indicators as $indicator) {
            $createdIndicators[] = IndasIndicatorType::create($indicator);
        }

        // Generate sample monthly data for the last 6 months
        $this->generateSampleMonthlyData($createdIndicators);
    }

    private function generateSampleMonthlyData($indicators)
    {
        // Get some sample kabupaten/kota (limit to first 10 for demo)
        $kabupatenKotaList = KabupatenKota::take(10)->get();
        
        if ($kabupatenKotaList->isEmpty()) {
            $this->command->info('No kabupaten/kota found. Skipping sample data generation.');
            return;
        }

        // Generate data for last 6 months
        $currentDate = now();
        $months = [];
        
        for ($i = 0; $i < 6; $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $months[] = [
                'month' => $date->month,
                'year' => $date->year
            ];
        }

        foreach ($kabupatenKotaList as $kabupatenKota) {
            foreach ($months as $period) {
                foreach ($indicators as $indicator) {
                    $value = $this->generateSampleValue($indicator);
                    
                    IndasMonthlyData::create([
                        'kabupaten_kota_id' => $kabupatenKota->id,
                        'indicator_type_id' => $indicator->id,
                        'value' => $value,
                        'month' => $period['month'],
                        'year' => $period['year'],
                        'user_id' => 1, // Assume first user exists
                        'notes' => 'Data sampel untuk testing dan demo sistem INDAS'
                    ]);
                }
            }
        }

        $this->command->info('Generated sample monthly data for ' . count($kabupatenKotaList) . ' regions over 6 months');
    }

    private function generateSampleValue($indicator)
    {
        // Generate realistic sample values based on indicator type
        switch ($indicator->name) {
            case 'Jumlah Toko':
                return rand(50, 500);
            case 'Jumlah Pasar':
                return rand(2, 15);
            case 'Jumlah Bank':
                return rand(3, 25);
            case 'UMKM Aktif':
                return rand(100, 1000);
            case 'Investasi Daerah':
                return rand(5, 100); // dalam miliar
            case 'Objek Wisata':
                return rand(5, 50);
            case 'Jumlah Hotel':
                return rand(3, 30);
            case 'Kunjungan Wisatawan':
                return rand(10, 200); // dalam ribuan
            case 'Pendapatan Sektor Pariwisata':
                return rand(2, 50); // dalam miliar
            case 'Fasilitas Kesehatan':
                return rand(10, 100);
            case 'Fasilitas Pendidikan':
                return rand(20, 200);
            case 'Tingkat Pengangguran':
                return rand(3, 15); // persentase
            case 'Program Bantuan Sosial':
                return rand(5, 25);
            case 'Infrastruktur Transportasi':
                return rand(100, 2000); // dalam km
            default:
                return rand(10, 100);
        }
    }
}
