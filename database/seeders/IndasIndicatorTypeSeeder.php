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
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            IndasMonthlyData::truncate();
            IndasIndicatorType::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // For SQLite and other databases, use delete instead of truncate
            IndasMonthlyData::query()->delete();
            IndasIndicatorType::query()->delete();
        }
        
        $indicators = [
            // Economic Indicators
            [
                'name' => 'Sekolah',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'description' => 'Jumlah sekolah (SD, SMP, SMA, SMK)',
                'is_active' => true,
            ],
            [
                'name' => 'Toko',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'description' => 'Jumlah toko dan usaha retail',
                'is_active' => true,
            ],
            [
                'name' => 'UMR',
                'category' => 'ekonomi',
                'unit' => 'rupiah',
                'description' => 'Upah Minimum Regional dalam rupiah',
                'is_active' => true,
            ],
            [
                'name' => 'Miskin',
                'category' => 'ekonomi',
                'unit' => 'orang',
                'description' => 'Jumlah penduduk miskin',
                'is_active' => true,
            ],
            [
                'name' => 'UMKM Aktif',
                'category' => 'ekonomi',
                'unit' => 'unit',
                'description' => 'Jumlah Usaha Mikro Kecil Menengah yang aktif',
                'is_active' => true,
            ],

            // Tourism Indicators
            [
                'name' => 'Objek Wisata',
                'category' => 'pariwisata',
                'unit' => 'unit',
                'description' => 'Jumlah destinasi dan objek wisata',
                'is_active' => true,
            ],
            [
                'name' => 'Hotel',
                'category' => 'pariwisata',
                'unit' => 'unit',
                'description' => 'Jumlah hotel dan fasilitas akomodasi',
                'is_active' => true,
            ],
            [
                'name' => 'Kunjungan Wisatawan',
                'category' => 'pariwisata',
                'unit' => 'orang',
                'description' => 'Jumlah kunjungan wisatawan per bulan',
                'is_active' => true,
            ],
            [
                'name' => 'Pendapatan Pariwisata',
                'category' => 'pariwisata',
                'unit' => 'rupiah',
                'description' => 'Pendapatan dari sektor pariwisata',
                'is_active' => true,
            ],

            // Social Indicators
            [
                'name' => 'Rumah Sakit',
                'category' => 'sosial',
                'unit' => 'unit',
                'description' => 'Jumlah rumah sakit dan puskesmas',
                'is_active' => true,
            ],
            [
                'name' => 'Pengangguran',
                'category' => 'sosial',
                'unit' => 'orang',
                'description' => 'Jumlah pengangguran',
                'is_active' => true,
            ],
            [
                'name' => 'Program Bantuan Sosial',
                'category' => 'sosial',
                'unit' => 'program',
                'description' => 'Jumlah program bantuan sosial aktif',
                'is_active' => true,
            ],
            [
                'name' => 'Jalan',
                'category' => 'sosial',
                'unit' => 'km',
                'description' => 'Panjang jalan dalam kondisi baik',
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
            case 'Sekolah':
                return rand(50, 300); // jumlah sekolah
            case 'Toko':
                return rand(100, 1500); // jumlah toko
            case 'UMR':
                return rand(3000000, 5000000); // UMR dalam rupiah (3-5 juta)
            case 'Miskin':
                return rand(1000, 15000); // jumlah orang miskin
            case 'UMKM Aktif':
                return rand(200, 2000); // jumlah UMKM
            case 'Objek Wisata':
                return rand(10, 80); // jumlah objek wisata
            case 'Hotel':
                return rand(5, 50); // jumlah hotel
            case 'Kunjungan Wisatawan':
                return rand(5000, 100000); // jumlah wisatawan per bulan
            case 'Pendapatan Pariwisata':
                return rand(500000000, 10000000000); // pendapatan dalam rupiah (500 juta - 10 miliar)
            case 'Rumah Sakit':
                return rand(5, 25); // jumlah rumah sakit
            case 'Pengangguran':
                return rand(500, 8000); // jumlah pengangguran
            case 'Program Bantuan Sosial':
                return rand(3, 20); // jumlah program
            case 'Jalan':
                return rand(50, 500); // panjang jalan dalam km
            default:
                return rand(10, 100);
        }
    }
}
