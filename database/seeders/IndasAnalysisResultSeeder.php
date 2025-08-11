<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IndasAnalysisResult;
use App\Models\KabupatenKota;
use Illuminate\Support\Facades\DB;

class IndasAnalysisResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample kabupaten/kota for different provinces
        $sampleRegions = KabupatenKota::with('provinsi')
            ->whereIn('nama', [
                'KOTA JAKARTA PUSAT',
                'KOTA SURABAYA', 
                'KOTA BANDUNG',
                'KOTA MEDAN',
                'KOTA MAKASSAR',
                'KOTA DENPASAR',
                'KOTA PALEMBANG',
                'KOTA BALIKPAPAN',
                'KOTA MANADO',
                'KOTA JAYAPURA'
            ])
            ->get();

        if ($sampleRegions->isEmpty()) {
            // Fallback: get first 10 kabupaten/kota if specific ones not found
            $sampleRegions = KabupatenKota::with('provinsi')->limit(10)->get();
        }

        // Generate data for last 6 months
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        $analysisData = [];
        
        foreach ($sampleRegions as $region) {
            $previousScores = null;
            
            // Generate 6 months of data
            for ($i = 5; $i >= 0; $i--) {
                $month = $currentMonth - $i;
                $year = $currentYear;
                
                if ($month <= 0) {
                    $month += 12;
                    $year -= 1;
                }
                
                // Generate realistic scores with some variation
                $economicScore = $this->generateScore($region->nama, 'economic', $month, $previousScores['economic'] ?? null);
                $tourismScore = $this->generateScore($region->nama, 'tourism', $month, $previousScores['tourism'] ?? null);
                $socialScore = $this->generateScore($region->nama, 'social', $month, $previousScores['social'] ?? null);
                $totalScore = round(($economicScore + $tourismScore + $socialScore) / 3, 2);
                
                // Calculate trends (percentage change from previous month)
                $economicTrend = $previousScores ? $this->calculateTrend($economicScore, $previousScores['economic']) : null;
                $tourismTrend = $previousScores ? $this->calculateTrend($tourismScore, $previousScores['tourism']) : null;
                $socialTrend = $previousScores ? $this->calculateTrend($socialScore, $previousScores['social']) : null;
                $totalTrend = $previousScores ? $this->calculateTrend($totalScore, $previousScores['total']) : null;
                
                $analysisData[] = [
                    'kabupaten_kota_id' => $region->id,
                    'month' => $month,
                    'year' => $year,
                    'economic_score' => $economicScore,
                    'tourism_score' => $tourismScore,
                    'social_score' => $socialScore,
                    'total_score' => $totalScore,
                    'economic_trend' => $economicTrend,
                    'tourism_trend' => $tourismTrend,
                    'social_trend' => $socialTrend,
                    'total_trend' => $totalTrend,
                    'calculation_details' => json_encode([
                        'region_name' => $region->nama,
                        'province' => $region->provinsi->nama,
                        'calculation_date' => now()->toDateTimeString(),
                        'data_points' => [
                            'economic_indicators' => rand(15, 25),
                            'tourism_indicators' => rand(10, 15),
                            'social_indicators' => rand(20, 30)
                        ]
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $previousScores = [
                    'economic' => $economicScore,
                    'tourism' => $tourismScore,
                    'social' => $socialScore,
                    'total' => $totalScore
                ];
            }
        }
        
        // Insert in chunks for better performance
        foreach (array_chunk($analysisData, 50) as $chunk) {
            IndasAnalysisResult::insert($chunk);
        }
        
        $this->command->info('Created ' . count($analysisData) . ' INDAS analysis result records for ' . $sampleRegions->count() . ' regions.');
    }
    
    /**
     * Generate a realistic score based on region type and month
     */
    private function generateScore(string $regionName, string $category, int $month, ?float $previousScore = null): float
    {
        // Base scores by region type
        $baseScores = [
            'economic' => [
                'KOTA JAKARTA PUSAT' => 85,
                'KOTA SURABAYA' => 78,
                'KOTA BANDUNG' => 75,
                'KOTA MEDAN' => 72,
                'default' => 65
            ],
            'tourism' => [
                'KOTA DENPASAR' => 88,
                'KOTA JAKARTA PUSAT' => 70,
                'KOTA BANDUNG' => 68,
                'KOTA MANADO' => 75,
                'default' => 60
            ],
            'social' => [
                'KOTA JAKARTA PUSAT' => 80,
                'KOTA SURABAYA' => 76,
                'KOTA BANDUNG' => 78,
                'default' => 70
            ]
        ];
        
        $baseScore = $baseScores[$category][$regionName] ?? $baseScores[$category]['default'];
        
        // Add seasonal variation (tourism higher in certain months)
        $seasonalAdjustment = 0;
        if ($category === 'tourism') {
            // Higher tourism scores in vacation months
            if (in_array($month, [6, 7, 8, 12])) {
                $seasonalAdjustment = rand(3, 8);
            }
        }
        
        // Add some randomness but keep it realistic
        $randomVariation = rand(-5, 5);
        
        // If there's a previous score, make gradual changes (prevent dramatic swings)
        if ($previousScore !== null) {
            $maxChange = 8; // Maximum change from previous month
            $targetScore = $baseScore + $seasonalAdjustment + $randomVariation;
            $change = $targetScore - $previousScore;
            
            if (abs($change) > $maxChange) {
                $change = $change > 0 ? $maxChange : -$maxChange;
            }
            
            $finalScore = $previousScore + $change;
        } else {
            $finalScore = $baseScore + $seasonalAdjustment + $randomVariation;
        }
        
        // Ensure score is within valid range (0-100)
        return max(0, min(100, round($finalScore, 2)));
    }
    
    /**
     * Calculate percentage trend between current and previous score
     */
    private function calculateTrend(float $currentScore, float $previousScore): float
    {
        if ($previousScore == 0) return 0;
        
        return round((($currentScore - $previousScore) / $previousScore) * 100, 2);
    }
}
