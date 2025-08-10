<?php

namespace App\Http\Controllers;

use App\Models\IndasRegion;
use App\Models\IndasIndicatorType;
use App\Models\IndasMonthlyData;
use App\Models\IndasAnalysisResult;
use App\Models\KabupatenKota;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndasController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get current month/year or use provided values
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Base query for analysis results
        $query = IndasAnalysisResult::with(['kabupatenKota.provinsi'])
            ->forPeriod($month, $year)
            ->latest();
        
        // Apply province filter for non-admin users
        if (!$user->isAdmin() && $user->provinsi_id) {
            $query->whereHas('kabupatenKota', function ($q) use ($user) {
                $q->where('provinsi_id', $user->provinsi_id);
            });
        }
        
        $analysisResults = $query->get();
        
        // Get summary statistics
        $stats = [
            'total_regions' => $analysisResults->count(),
            'avg_economic_score' => $analysisResults->avg('economic_score') ?? 0,
            'avg_tourism_score' => $analysisResults->avg('tourism_score') ?? 0,
            'avg_social_score' => $analysisResults->avg('social_score') ?? 0,
            'avg_total_score' => $analysisResults->avg('total_score') ?? 0,
        ];
        
        return Inertia::render('Indas/Index', [
            'analysisResults' => $analysisResults,
            'currentMonth' => $month,
            'currentYear' => $year,
            'stats' => $stats,
        ]);
    }

    public function regions(Request $request)
    {
        $user = $request->user();
        
        $query = KabupatenKota::with(['provinsi']);
        
        // Apply province filter for non-admin users
        if (!$user->isAdmin() && $user->provinsi_id) {
            $query->where('provinsi_id', $user->provinsi_id);
        }
        
        // Add search functionality
        if ($request->has('search')) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }
        
        return Inertia::render('Indas/Regions', [
            'kabupatenKota' => $query->get(),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function indicators()
    {
        return Inertia::render('Indas/Indicators', [
            'indicators' => IndasIndicatorType::active()->get()->groupBy('category'),
        ]);
    }

    public function storeIndicator(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:ekonomi,pariwisata,sosial',
            'unit' => 'required|string|max:50',
            'weight_factor' => 'required|numeric|min:0|max:1',
            'description' => 'nullable|string',
        ]);
        
        IndasIndicatorType::create($request->validated());
        
        return redirect()->back()->with('success', 'Indicator added successfully');
    }

    public function dataEntry(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Get available kabupaten/kota directly
        $kabupatenKotaQuery = KabupatenKota::with(['provinsi']);
        
        if (!$user->isAdmin() && $user->provinsi_id) {
            $kabupatenKotaQuery->where('provinsi_id', $user->provinsi_id);
        }
        
        $kabupatenKota = $kabupatenKotaQuery->get();
        $indicators = IndasIndicatorType::active()->get()->groupBy('category');
        
        // Get existing data for the period
        $existingData = IndasMonthlyData::with(['indicatorType'])
            ->forPeriod($month, $year)
            ->when(!$user->isAdmin() && $user->provinsi_id, function ($query) use ($user) {
                $query->whereHas('kabupatenKota', function ($q) use ($user) {
                    $q->where('provinsi_id', $user->provinsi_id);
                });
            })
            ->get()
            ->groupBy(['kabupaten_kota_id', 'indicator_type_id']);
        
        return Inertia::render('Indas/DataEntry', [
            'kabupatenKota' => $kabupatenKota,
            'indicators' => $indicators,
            'currentMonth' => $month,
            'currentYear' => $year,
            'existingData' => $existingData,
        ]);
    }

    public function storeData(Request $request)
    {
        $request->validate([
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'indicator_type_id' => 'required|exists:indas_indicator_types,id',
            'value' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'notes' => 'nullable|string',
        ]);
        
        $user = $request->user();
        
        // Check access to kabupaten/kota
        if (!$user->isAdmin() && $user->provinsi_id) {
            $kabupatenKota = KabupatenKota::find($request->kabupaten_kota_id);
            if (!$kabupatenKota || $kabupatenKota->provinsi_id !== $user->provinsi_id) {
                abort(403, 'Unauthorized access to this region');
            }
        }
        
        // Update or create monthly data
        IndasMonthlyData::updateOrCreate(
            [
                'kabupaten_kota_id' => $request->kabupaten_kota_id,
                'indicator_type_id' => $request->indicator_type_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'value' => $request->value,
                'user_id' => $user->id,
                'notes' => $request->notes,
            ]
        );
        
        // Trigger recalculation for this kabupaten/kota and period
        $this->calculateScores($request->kabupaten_kota_id, $request->month, $request->year);
        
        return redirect()->back()->with('success', 'Data saved successfully');
    }

    public function calculateAll(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $user = $request->user();
        
        $kabupatenKotaQuery = KabupatenKota::query();
        
        if (!$user->isAdmin() && $user->provinsi_id) {
            $kabupatenKotaQuery->where('provinsi_id', $user->provinsi_id);
        }
        
        $kabupatenKotaList = $kabupatenKotaQuery->get();
        $calculatedCount = 0;
        
        foreach ($kabupatenKotaList as $kabupatenKota) {
            if ($this->calculateScores($kabupatenKota->id, $month, $year)) {
                $calculatedCount++;
            }
        }
        
        return redirect()->back()->with('success', "Calculated scores for {$calculatedCount} regions");
    }

    private function calculateScores($kabupatenKotaId, $month, $year)
    {
        // Get all data for this kabupaten/kota and period
        $monthlyData = IndasMonthlyData::with('indicatorType')
            ->where('kabupaten_kota_id', $kabupatenKotaId)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->groupBy('indicatorType.category');
        
        $scores = [
            'economic_score' => 0,
            'tourism_score' => 0,
            'social_score' => 0,
        ];
        
        $calculationDetails = [];
        
        // Calculate category scores using agreed formula
        foreach (['ekonomi', 'pariwisata', 'sosial'] as $category) {
            $categoryData = $monthlyData->get($category, collect());
            
            if ($categoryData->isEmpty()) {
                $calculationDetails[$category] = ['message' => 'No data available'];
                continue;
            }
            
            $weightedSum = 0;
            $totalWeight = 0;
            $details = [];
            
            foreach ($categoryData as $data) {
                $weight = $data->indicatorType->weight_factor;
                $value = $data->value;
                $weightedSum += $value * $weight;
                $totalWeight += $weight;
                
                $details[] = [
                    'indicator' => $data->indicatorType->name,
                    'value' => $value,
                    'weight' => $weight,
                    'weighted_value' => $value * $weight,
                ];
            }
            
            $categoryScore = $totalWeight > 0 ? ($weightedSum / $totalWeight) : 0;
            
            // Map category to score field
            $scoreField = match($category) {
                'ekonomi' => 'economic_score',
                'pariwisata' => 'tourism_score',
                'sosial' => 'social_score',
            };
            
            $scores[$scoreField] = $categoryScore;
            $calculationDetails[$category] = [
                'weighted_sum' => $weightedSum,
                'total_weight' => $totalWeight,
                'score' => $categoryScore,
                'details' => $details,
            ];
        }
        
        // Calculate total score with agreed weights
        // Economic: 40%, Tourism: 35%, Social: 25%
        $totalScore = ($scores['economic_score'] * 0.4) + 
                     ($scores['tourism_score'] * 0.35) + 
                     ($scores['social_score'] * 0.25);
        
        $scores['total_score'] = $totalScore;
        $calculationDetails['total'] = [
            'economic_weight' => 0.4,
            'tourism_weight' => 0.35,
            'social_weight' => 0.25,
            'total_score' => $totalScore,
        ];
        
        // Get previous month for trend calculation
        $prevMonth = $month - 1;
        $prevYear = $year;
        
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        }
        
        $previousResult = IndasAnalysisResult::where('kabupaten_kota_id', $kabupatenKotaId)
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
        
        // Save analysis result
        IndasAnalysisResult::updateOrCreate(
            [
                'kabupaten_kota_id' => $kabupatenKotaId,
                'month' => $month,
                'year' => $year,
            ],
            array_merge($scores, $trends, [
                'calculation_details' => $calculationDetails,
            ])
        );
        
        return true;
    }
}