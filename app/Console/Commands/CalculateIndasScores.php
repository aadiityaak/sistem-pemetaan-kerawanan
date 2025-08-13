<?php

namespace App\Console\Commands;

use App\Models\KabupatenKota;
use App\Models\IndasMonthlyData;
use App\Models\IndasAnalysisResult;
use Illuminate\Console\Command;

class CalculateIndasScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indas:calculate {--month=} {--year=} {--all-months}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate INDAS analysis scores for regions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentMonth = $this->option('month') ?? now()->month;
        $currentYear = $this->option('year') ?? now()->year;
        
        if ($this->option('all-months')) {
            // Calculate for all months that have data
            $periods = IndasMonthlyData::select('month', 'year')
                ->distinct()
                ->orderBy('year')
                ->orderBy('month')
                ->get();
            
            $this->info("Calculating for all periods with data...");
            
            foreach ($periods as $period) {
                $this->calculateForPeriod($period->month, $period->year);
            }
        } else {
            $this->calculateForPeriod($currentMonth, $currentYear);
        }
        
        $this->info('INDAS calculation completed successfully!');
    }
    
    private function calculateForPeriod($month, $year)
    {
        $this->info("Calculating for period: {$month}/{$year}");
        
        $kabupatenKotaList = KabupatenKota::take(10)->get();
        $calculated = 0;
        
        foreach ($kabupatenKotaList as $kk) {
            $monthlyData = IndasMonthlyData::with('indicatorType')
                ->where('kabupaten_kota_id', $kk->id)
                ->where('month', $month)
                ->where('year', $year)
                ->get()
                ->groupBy('indicatorType.category');
            
            if ($monthlyData->isEmpty()) {
                continue; // Skip if no data for this region/period
            }
            
            $scores = [
                'economic_score' => 0,
                'tourism_score' => 0,
                'social_score' => 0,
            ];
            
            foreach (['ekonomi', 'pariwisata', 'sosial'] as $category) {
                $categoryData = $monthlyData->get($category, collect());
                
                if (!$categoryData->isEmpty()) {
                    // Jumlah total nilai langsung tanpa bobot
                    $totalValue = 0;
                    
                    foreach ($categoryData as $data) {
                        $totalValue += $data->value;
                    }
                    
                    $scoreField = match($category) {
                        'ekonomi' => 'economic_score',
                        'pariwisata' => 'tourism_score',
                        'sosial' => 'social_score',
                    };
                    
                    // Menggunakan total nilai langsung sebagai skor
                    $scores[$scoreField] = $totalValue;
                }
            }
            
            // Calculate total score as sum of all category scores
            $totalScore = $scores['economic_score'] + $scores['tourism_score'] + $scores['social_score'];
            
            $scores['total_score'] = $totalScore;
            
            // Calculate trends if previous month data exists
            $prevMonth = $month - 1;
            $prevYear = $year;
            
            if ($prevMonth < 1) {
                $prevMonth = 12;
                $prevYear = $year - 1;
            }
            
            $previousResult = IndasAnalysisResult::where('kabupaten_kota_id', $kk->id)
                ->where('month', $prevMonth)
                ->where('year', $prevYear)
                ->first();
            
            $trends = [
                'economic_trend' => null,
                'tourism_trend' => null,
                'social_trend' => null,
                'total_trend' => null,
            ];
            
            if ($previousResult) {
                foreach (['economic', 'tourism', 'social', 'total'] as $type) {
                    $currentScore = $scores["{$type}_score"];
                    $previousScore = $previousResult->{"{$type}_score"};
                    
                    if ($previousScore > 0) {
                        $trends["{$type}_trend"] = (($currentScore - $previousScore) / $previousScore) * 100;
                    }
                }
            }
            
            IndasAnalysisResult::updateOrCreate([
                'kabupaten_kota_id' => $kk->id,
                'month' => $month,
                'year' => $year,
            ], array_merge($scores, $trends));
            
            $calculated++;
        }
        
        $this->info("  → Calculated scores for {$calculated} regions");
    }
}
