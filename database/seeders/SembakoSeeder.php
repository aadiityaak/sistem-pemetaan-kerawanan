<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sembako;
use App\Models\KabupatenKota;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SembakoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample kabupaten/kota from different provinces
        $sampleRegions = KabupatenKota::with('provinsi')
            ->whereIn('nama', [
                'KOTA JAKARTA PUSAT',
                'KOTA SURABAYA', 
                'KOTA BANDUNG',
                'KOTA MEDAN',
                'KOTA MAKASSAR',
                'KOTA DENPASAR',
                'KOTA PALEMBANG',
                'KOTA SEMARANG',
                'KOTA YOGYAKARTA',
                'KOTA MALANG',
                'KOTA BALIKPAPAN',
                'KOTA MANADO',
                'KOTA PONTIANAK',
                'KOTA BANJARMASIN',
                'KOTA PEKANBARU'
            ])
            ->get();

        if ($sampleRegions->isEmpty()) {
            // Fallback: get first 15 kabupaten/kota if specific ones not found
            $sampleRegions = KabupatenKota::with('provinsi')->limit(15)->get();
        }

        // Define Indonesian food commodities with realistic price ranges
        $commodities = [
            // Beras & Padi-padian
            ['nama' => 'Beras Premium', 'satuan' => 'kg', 'base_price' => 15000, 'variance' => 3000],
            ['nama' => 'Beras Medium', 'satuan' => 'kg', 'base_price' => 12000, 'variance' => 2000],
            ['nama' => 'Beras IR64', 'satuan' => 'kg', 'base_price' => 10000, 'variance' => 1500],
            ['nama' => 'Tepung Terigu', 'satuan' => 'kg', 'base_price' => 8000, 'variance' => 1000],
            ['nama' => 'Tepung Beras', 'satuan' => 'kg', 'base_price' => 12000, 'variance' => 1500],

            // Protein Hewani
            ['nama' => 'Daging Sapi', 'satuan' => 'kg', 'base_price' => 130000, 'variance' => 20000],
            ['nama' => 'Daging Ayam', 'satuan' => 'kg', 'base_price' => 35000, 'variance' => 5000],
            ['nama' => 'Telur Ayam', 'satuan' => 'kg', 'base_price' => 28000, 'variance' => 3000],
            ['nama' => 'Ikan Bandeng', 'satuan' => 'kg', 'base_price' => 25000, 'variance' => 5000],
            ['nama' => 'Ikan Lele', 'satuan' => 'kg', 'base_price' => 18000, 'variance' => 3000],
            ['nama' => 'Ikan Tongkol', 'satuan' => 'kg', 'base_price' => 20000, 'variance' => 4000],

            // Protein Nabati
            ['nama' => 'Kedelai', 'satuan' => 'kg', 'base_price' => 10000, 'variance' => 2000],
            ['nama' => 'Tahu', 'satuan' => 'kg', 'base_price' => 8000, 'variance' => 1000],
            ['nama' => 'Tempe', 'satuan' => 'kg', 'base_price' => 9000, 'variance' => 1000],

            // Sayuran
            ['nama' => 'Bawang Merah', 'satuan' => 'kg', 'base_price' => 35000, 'variance' => 10000],
            ['nama' => 'Bawang Putih', 'satuan' => 'kg', 'base_price' => 25000, 'variance' => 5000],
            ['nama' => 'Cabai Merah', 'satuan' => 'kg', 'base_price' => 50000, 'variance' => 15000],
            ['nama' => 'Cabai Rawit', 'satuan' => 'kg', 'base_price' => 80000, 'variance' => 20000],
            ['nama' => 'Tomat', 'satuan' => 'kg', 'base_price' => 8000, 'variance' => 2000],
            ['nama' => 'Kangkung', 'satuan' => 'kg', 'base_price' => 3000, 'variance' => 500],
            ['nama' => 'Bayam', 'satuan' => 'kg', 'base_price' => 4000, 'variance' => 500],
            ['nama' => 'Wortel', 'satuan' => 'kg', 'base_price' => 6000, 'variance' => 1000],

            // Buah-buahan
            ['nama' => 'Jeruk', 'satuan' => 'kg', 'base_price' => 12000, 'variance' => 3000],
            ['nama' => 'Pisang', 'satuan' => 'kg', 'base_price' => 8000, 'variance' => 2000],
            ['nama' => 'Apel', 'satuan' => 'kg', 'base_price' => 25000, 'variance' => 5000],
            ['nama' => 'Mangga', 'satuan' => 'kg', 'base_price' => 15000, 'variance' => 5000],

            // Bumbu & Rempah
            ['nama' => 'Gula Pasir', 'satuan' => 'kg', 'base_price' => 14000, 'variance' => 2000],
            ['nama' => 'Garam', 'satuan' => 'kg', 'base_price' => 5000, 'variance' => 1000],
            ['nama' => 'Minyak Goreng', 'satuan' => 'liter', 'base_price' => 16000, 'variance' => 2000],
            ['nama' => 'Santan Kelapa', 'satuan' => 'liter', 'base_price' => 8000, 'variance' => 1000],

            // Produk Olahan
            ['nama' => 'Mie Instan', 'satuan' => 'pcs', 'base_price' => 3500, 'variance' => 500],
            ['nama' => 'Susu Kental Manis', 'satuan' => 'kaleng', 'base_price' => 12000, 'variance' => 2000],
            ['nama' => 'Kornet Sapi', 'satuan' => 'kaleng', 'base_price' => 18000, 'variance' => 3000],
        ];

        // Generate data for last 3 months
        $sembakoData = [];
        $currentDate = Carbon::now();
        
        foreach ($sampleRegions as $region) {
            // Generate data for each commodity
            foreach ($commodities as $commodity) {
                // Generate 3 months of data (with some gaps for realism)
                for ($i = 2; $i >= 0; $i--) {
                    $recordDate = $currentDate->copy()->subMonths($i)->subDays(rand(0, 15));
                    
                    // Skip some records randomly to create realistic gaps
                    if (rand(1, 10) <= 2) continue; // 20% chance to skip
                    
                    $price = $this->calculatePrice($commodity, $region->nama, $recordDate);
                    
                    $sembakoData[] = [
                        'nama_komoditas' => $commodity['nama'],
                        'satuan' => $commodity['satuan'],
                        'harga' => $price,
                        'kabupaten_kota_id' => $region->id,
                        'tanggal_pencatatan' => $recordDate->format('Y-m-d'),
                        'keterangan' => $this->generateKeterangan($commodity['nama'], $price, $commodity['base_price']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Insert in chunks for better performance
        foreach (array_chunk($sembakoData, 50) as $chunk) {
            Sembako::insert($chunk);
        }
        
        $this->command->info('Created ' . count($sembakoData) . ' Sembako commodity price records for ' . $sampleRegions->count() . ' regions.');
    }
    
    /**
     * Calculate realistic price based on region, commodity, and date
     */
    private function calculatePrice(array $commodity, string $regionName, Carbon $date): int
    {
        $basePrice = $commodity['base_price'];
        $variance = $commodity['variance'];
        
        // Regional price adjustments
        $regionalMultiplier = $this->getRegionalPriceMultiplier($regionName);
        
        // Seasonal adjustments for certain commodities
        $seasonalAdjustment = $this->getSeasonalAdjustment($commodity['nama'], $date);
        
        // Market fluctuation (random but realistic)
        $marketFluctuation = rand(-$variance, $variance);
        
        // Calculate final price
        $finalPrice = ($basePrice * $regionalMultiplier) + $seasonalAdjustment + $marketFluctuation;
        
        // Ensure minimum price and round to nearest 100 for realism
        return max(500, round($finalPrice / 100) * 100);
    }
    
    /**
     * Get regional price multiplier based on economic conditions
     */
    private function getRegionalPriceMultiplier(string $regionName): float
    {
        $priceMultipliers = [
            'KOTA JAKARTA PUSAT' => 1.3,  // Higher cost of living
            'KOTA DENPASAR' => 1.25,      // Tourism area
            'KOTA SURABAYA' => 1.15,      // Major city
            'KOTA BANDUNG' => 1.1,        // Major city
            'KOTA MEDAN' => 1.05,         // Regional center
            'KOTA MAKASSAR' => 1.05,      // Regional center
            'KOTA SEMARANG' => 1.0,       // Average
            'KOTA YOGYAKARTA' => 1.0,     // Average
            'KOTA MALANG' => 0.95,        // Lower cost
            'KOTA PALEMBANG' => 0.95,     // Lower cost
        ];
        
        return $priceMultipliers[$regionName] ?? 1.0; // Default multiplier
    }
    
    /**
     * Get seasonal price adjustment for certain commodities
     */
    private function getSeasonalAdjustment(string $commodityName, Carbon $date): int
    {
        $month = $date->month;
        
        // Seasonal patterns for Indonesian commodities
        $seasonalPatterns = [
            'Cabai Merah' => [
                1 => 2000, 2 => 3000, 3 => 1000,   // Dry season - higher prices
                4 => -1000, 5 => -2000, 6 => -1000, // Harvest season - lower prices
                7 => 1000, 8 => 2000, 9 => 3000,   // Dry season - higher prices
                10 => -1000, 11 => -2000, 12 => 1000 // Rainy season variation
            ],
            'Cabai Rawit' => [
                1 => 3000, 2 => 4000, 3 => 2000,
                4 => -2000, 5 => -3000, 6 => -1000,
                7 => 2000, 8 => 3000, 9 => 4000,
                10 => -1000, 11 => -2000, 12 => 1000
            ],
            'Bawang Merah' => [
                1 => 1000, 2 => 2000, 3 => 3000,   // Before harvest
                4 => -3000, 5 => -4000, 6 => -2000, // Harvest season
                7 => 1000, 8 => 2000, 9 => 3000,
                10 => -1000, 11 => -2000, 12 => 0
            ],
            'Tomat' => [
                1 => 500, 2 => 1000, 3 => 500,
                4 => -1000, 5 => -1500, 6 => -500,
                7 => 500, 8 => 1000, 9 => 1500,
                10 => -500, 11 => -1000, 12 => 0
            ],
        ];
        
        return $seasonalPatterns[$commodityName][$month] ?? 0;
    }
    
    /**
     * Generate realistic notes based on price conditions
     */
    private function generateKeterangan(string $commodityName, int $actualPrice, int $basePrice): ?string
    {
        $priceRatio = $actualPrice / $basePrice;
        
        $keteranganOptions = [];
        
        if ($priceRatio > 1.3) {
            $keteranganOptions = [
                'Harga meningkat tajam karena kelangkaan pasokan',
                'Dampak cuaca ekstrem pada produksi',
                'Tingginya permintaan pasar',
                'Gangguan distribusi dari daerah produsen',
                'Kenaikan harga BBM mempengaruhi biaya transportasi'
            ];
        } elseif ($priceRatio > 1.1) {
            $keteranganOptions = [
                'Harga sedikit naik mengikuti musim',
                'Permintaan pasar cukup tinggi',
                'Biaya distribusi meningkat',
                'Kualitas produk baik, harga stabil naik'
            ];
        } elseif ($priceRatio < 0.8) {
            $keteranganOptions = [
                'Panen raya, pasokan melimpah',
                'Harga turun karena oversupply',
                'Musim panen tiba, harga turun',
                'Kompetisi antar pedagang menurunkan harga'
            ];
        } elseif ($priceRatio < 0.9) {
            $keteranganOptions = [
                'Harga stabil dengan sedikit penurunan',
                'Pasokan cukup dari daerah produsen',
                'Kondisi pasar normal'
            ];
        } else {
            // Normal price range - sometimes no notes
            if (rand(1, 3) === 1) {
                $keteranganOptions = [
                    'Harga stabil sesuai kondisi pasar',
                    'Kualitas baik, harga normal',
                    'Pasokan dan permintaan seimbang'
                ];
            }
        }
        
        return !empty($keteranganOptions) 
            ? $keteranganOptions[array_rand($keteranganOptions)]
            : null;
    }
}