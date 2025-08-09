<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndasController extends Controller
{
    /**
     * Display INDAS main page with situation summary
     */
    public function index(Request $request)
    {
        // Periode analisis (default: 30 hari terakhir)
        $period = $request->query('period', 30);
        $startDate = Carbon::now()->subDays($period);

        // Ambil data monitoring dalam periode
        $monitoringData = MonitoringData::with(['provinsi', 'kabupatenKota', 'category', 'subCategory'])
            ->where('incident_date', '>=', $startDate)
            ->orderBy('incident_date', 'desc')
            ->get();

        // Hitung statistik keamanan
        $securityStats = $this->calculateSecurityStats($monitoringData);
        
        // Analisis tren
        $trendAnalysis = $this->analyzeTrends($monitoringData, $startDate);
        
        // Ancaman prioritas
        $priorityThreats = $this->identifyPriorityThreats($monitoringData);
        
        // Kondisi regional
        $regionalStatus = $this->assessRegionalStatus($monitoringData);
        
        // Rekomendasi keamanan
        $recommendations = $this->generateRecommendations($monitoringData);

        return Inertia::render('Indas/Index', [
            'period' => $period,
            'monitoringData' => $monitoringData,
            'securityStats' => $securityStats,
            'trendAnalysis' => $trendAnalysis,
            'priorityThreats' => $priorityThreats,
            'regionalStatus' => $regionalStatus,
            'recommendations' => $recommendations,
            'lastUpdated' => Carbon::now()->format('d M Y H:i'),
        ]);
    }

    /**
     * Display trend analysis page
     */
    public function trends(Request $request)
    {
        $period = $request->query('period', 90); // 3 bulan default untuk analisis tren
        $startDate = Carbon::now()->subDays($period);

        $monitoringData = MonitoringData::with(['provinsi', 'category', 'subCategory'])
            ->where('incident_date', '>=', $startDate)
            ->get();

        // Analisis tren per kategori
        $categoryTrends = $this->analyzeCategoryTrends($monitoringData, $startDate, $period);
        
        // Analisis tren geografis
        $geographicTrends = $this->analyzeGeographicTrends($monitoringData, $startDate);
        
        // Prediksi risiko
        $riskPrediction = $this->predictRisks($monitoringData);

        return Inertia::render('Indas/Trends', [
            'period' => $period,
            'categoryTrends' => $categoryTrends,
            'geographicTrends' => $geographicTrends,
            'riskPrediction' => $riskPrediction,
            'lastUpdated' => Carbon::now()->format('d M Y H:i'),
        ]);
    }

    /**
     * Display recommendations page
     */
    public function recommendations(Request $request)
    {
        $period = $request->query('period', 30);
        $startDate = Carbon::now()->subDays($period);

        $monitoringData = MonitoringData::with(['provinsi', 'category', 'subCategory'])
            ->where('incident_date', '>=', $startDate)
            ->get();

        // Rekomendasi strategis
        $strategicRecommendations = $this->generateStrategicRecommendations($monitoringData);
        
        // Rekomendasi taktis
        $tacticalRecommendations = $this->generateTacticalRecommendations($monitoringData);
        
        // Rekomendasi regional
        $regionalRecommendations = $this->generateRegionalRecommendations($monitoringData);

        return Inertia::render('Indas/Recommendations', [
            'period' => $period,
            'strategicRecommendations' => $strategicRecommendations,
            'tacticalRecommendations' => $tacticalRecommendations,
            'regionalRecommendations' => $regionalRecommendations,
            'lastUpdated' => Carbon::now()->format('d M Y H:i'),
        ]);
    }

    /**
     * Calculate security statistics
     */
    private function calculateSecurityStats($monitoringData)
    {
        $total = $monitoringData->count();
        $critical = $monitoringData->where('severity_level', 'critical')->count();
        $high = $monitoringData->where('severity_level', 'high')->count();
        $medium = $monitoringData->where('severity_level', 'medium')->count();
        $low = $monitoringData->where('severity_level', 'low')->count();

        $active = $monitoringData->where('status', 'active')->count();
        $resolved = $monitoringData->where('status', 'resolved')->count();
        $monitoring = $monitoringData->where('status', 'monitoring')->count();

        return [
            'total_incidents' => $total,
            'critical_level' => $critical,
            'high_level' => $high,
            'medium_level' => $medium,
            'low_level' => $low,
            'active_cases' => $active,
            'resolved_cases' => $resolved,
            'monitoring_cases' => $monitoring,
            'security_index' => $this->calculateSecurityIndex($critical, $high, $medium, $low),
            'affected_population' => $monitoringData->sum('jumlah_terdampak'),
            'provinces_affected' => $monitoringData->groupBy('provinsi_id')->count(),
        ];
    }

    /**
     * Calculate security index (0-100, higher is better)
     */
    private function calculateSecurityIndex($critical, $high, $medium, $low)
    {
        $total = $critical + $high + $medium + $low;
        
        if ($total === 0) return 100;
        
        // Weighted scoring: critical=-4, high=-2, medium=-1, low=0
        $score = ($critical * -4) + ($high * -2) + ($medium * -1) + ($low * 0);
        $maxNegativeScore = $total * -4; // Worst case scenario
        
        // Normalize to 0-100 scale
        $index = 100 + (($score / $maxNegativeScore) * 100);
        
        return max(0, min(100, round($index)));
    }

    /**
     * Analyze trends over time
     */
    private function analyzeTrends($monitoringData, $startDate)
    {
        $days = Carbon::now()->diffInDays($startDate);
        $midPoint = Carbon::now()->subDays($days / 2);
        
        $recentData = $monitoringData->where('incident_date', '>=', $midPoint);
        $olderData = $monitoringData->where('incident_date', '<', $midPoint);
        
        $recentCount = $recentData->count();
        $olderCount = $olderData->count();
        
        $trend = $olderCount > 0 ? (($recentCount - $olderCount) / $olderCount) * 100 : 0;
        
        return [
            'overall_trend' => $trend > 5 ? 'increasing' : ($trend < -5 ? 'decreasing' : 'stable'),
            'trend_percentage' => round(abs($trend), 1),
            'recent_incidents' => $recentCount,
            'previous_incidents' => $olderCount,
            'daily_average' => round($recentCount / ($days / 2), 1),
        ];
    }

    /**
     * Identify priority threats
     */
    private function identifyPriorityThreats($monitoringData)
    {
        return $monitoringData
            ->whereIn('severity_level', ['critical', 'high'])
            ->where('status', 'active')
            ->sortByDesc(function ($item) {
                return $item->severity_level === 'critical' ? 2 : 1;
            })
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Assess regional security status
     */
    private function assessRegionalStatus($monitoringData)
    {
        $regionalData = $monitoringData->groupBy('provinsi.nama')->map(function ($data, $province) {
            $critical = $data->where('severity_level', 'critical')->count();
            $high = $data->where('severity_level', 'high')->count();
            $total = $data->count();
            
            $riskLevel = 'low';
            if ($critical > 0 || ($high / max($total, 1)) > 0.5) {
                $riskLevel = 'high';
            } elseif ($high > 0 || ($total > 5)) {
                $riskLevel = 'medium';
            }
            
            return [
                'province' => $province,
                'total_incidents' => $total,
                'critical_incidents' => $critical,
                'high_incidents' => $high,
                'risk_level' => $riskLevel,
                'affected_population' => $data->sum('jumlah_terdampak'),
            ];
        })->sortByDesc('total_incidents')->values();

        return $regionalData;
    }

    /**
     * Generate security recommendations
     */
    private function generateRecommendations($monitoringData)
    {
        $recommendations = [];
        
        // Check for critical incidents
        $critical = $monitoringData->where('severity_level', 'critical');
        if ($critical->count() > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'Respons Darurat',
                'title' => 'Penanganan Insiden Kritis',
                'description' => "Terdapat {$critical->count()} insiden kritis yang memerlukan respons segera.",
                'actions' => [
                    'Aktifkan pusat komando darurat',
                    'Koordinasi dengan instansi terkait',
                    'Monitor perkembangan 24/7'
                ]
            ];
        }
        
        // Check for increasing trends
        $trendAnalysis = $this->analyzeTrends($monitoringData, Carbon::now()->subDays(30));
        if ($trendAnalysis['overall_trend'] === 'increasing') {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'Pencegahan',
                'title' => 'Antisipasi Peningkatan Insiden',
                'description' => "Tren insiden meningkat {$trendAnalysis['trend_percentage']}% dalam periode ini.",
                'actions' => [
                    'Tingkatkan patroli di area rawan',
                    'Perkuat sistem early warning',
                    'Koordinasi antar unit keamanan'
                ]
            ];
        }
        
        // Check high-risk provinces
        $highRiskProvinces = $this->assessRegionalStatus($monitoringData)
            ->where('risk_level', 'high')->take(3);
        
        if ($highRiskProvinces->count() > 0) {
            $provinces = $highRiskProvinces->pluck('province')->implode(', ');
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'Regional',
                'title' => 'Perhatian Khusus Wilayah Berisiko Tinggi',
                'description' => "Provinsi berisiko tinggi: {$provinces}",
                'actions' => [
                    'Alokasi sumber daya tambahan',
                    'Intensifkan koordinasi regional',
                    'Evaluasi strategi keamanan lokal'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Analyze category trends (for trends page)
     */
    private function analyzeCategoryTrends($monitoringData, $startDate, $period)
    {
        // Implementation for detailed category trend analysis
        return $monitoringData->groupBy('category.name')->map(function ($data, $categoryName) {
            return [
                'category' => $categoryName,
                'total' => $data->count(),
                'trend' => 'stable', // Simplified for now
                'weekly_data' => [] // Weekly breakdown data
            ];
        });
    }

    /**
     * Analyze geographic trends (for trends page) 
     */
    private function analyzeGeographicTrends($monitoringData, $startDate)
    {
        // Implementation for geographic trend analysis
        return [];
    }

    /**
     * Predict risks based on current data (for trends page)
     */
    private function predictRisks($monitoringData)
    {
        // Implementation for risk prediction
        return [];
    }

    /**
     * Generate strategic recommendations (for recommendations page)
     */
    private function generateStrategicRecommendations($monitoringData)
    {
        // Implementation for strategic recommendations
        return [];
    }

    /**
     * Generate tactical recommendations (for recommendations page)
     */
    private function generateTacticalRecommendations($monitoringData)
    {
        // Implementation for tactical recommendations
        return [];
    }

    /**
     * Generate regional recommendations (for recommendations page)
     */
    private function generateRegionalRecommendations($monitoringData)
    {
        // Implementation for regional recommendations
        return [];
    }
}