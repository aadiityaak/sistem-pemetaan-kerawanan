<?php

namespace App\Http\Controllers;

use App\Models\IndasRegion;
use App\Models\IndasIndicatorType;
use App\Models\IndasMonthlyData;
use App\Models\IndasAnalysisResult;
use App\Models\KabupatenKota;
use App\Models\MonitoringData;
use App\Models\Category;
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
        
        // Get regional information for each kabupaten/kota
        $regionalInfo = $this->getRegionalInfo($user, $analysisResults->pluck('kabupaten_kota_id')->toArray());
        
        // Get summary statistics
        $stats = [
            'total_regions' => $analysisResults->count(),
            'avg_economic_score' => $analysisResults->avg('economic_score') ?? 0,
            'avg_tourism_score' => $analysisResults->avg('tourism_score') ?? 0,
            'avg_social_score' => $analysisResults->avg('social_score') ?? 0,
            'avg_total_score' => $analysisResults->avg('total_score') ?? 0,
        ];
        
        // Get unjuk rasa (protest) data
        $unjukRasaStats = $this->getUnjukRasaStats($user);
        
        return Inertia::render('Indas/Index', [
            'analysisResults' => $analysisResults,
            'regionalInfo' => $regionalInfo,
            'currentMonth' => $month,
            'currentYear' => $year,
            'stats' => $stats,
            'unjukRasaStats' => $unjukRasaStats,
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
        
        return redirect()->back()->with('success', 'Indikator berhasil ditambahkan');
    }

    public function deleteIndicator(Request $request, $id)
    {
        $indicator = IndasIndicatorType::findOrFail($id);
        
        // Check if indicator has associated data
        $hasData = IndasMonthlyData::where('indicator_type_id', $id)->exists();
        
        if ($hasData) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus indikator yang memiliki data terkait. Hapus data terlebih dahulu.');
        }
        
        $indicator->delete();
        
        return redirect()->back()->with('success', 'Indikator berhasil dihapus');
    }

    public function dataEntry(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $selectedKabupatenKotaId = $request->input('kabupaten_kota_id');
        
        // Get available kabupaten/kota for dropdown
        $kabupatenKotaQuery = KabupatenKota::with(['provinsi'])->orderBy('nama');
        
        if (!$user->isAdmin() && $user->provinsi_id) {
            $kabupatenKotaQuery->where('provinsi_id', $user->provinsi_id);
        }
        
        $availableKabupatenKota = $kabupatenKotaQuery->get();
        $indicators = IndasIndicatorType::active()->get()->groupBy('category');
        
        // Get selected kabupaten/kota data
        $selectedKabupatenKota = null;
        $existingData = [];
        
        if ($selectedKabupatenKotaId) {
            $selectedKabupatenKota = KabupatenKota::with(['provinsi'])
                ->where('id', $selectedKabupatenKotaId)
                ->when(!$user->isAdmin() && $user->provinsi_id, function ($query) use ($user) {
                    $query->where('provinsi_id', $user->provinsi_id);
                })
                ->first();
            
            if ($selectedKabupatenKota) {
                // Get existing data for the selected kabupaten/kota and period
                $existingData = IndasMonthlyData::with(['indicatorType'])
                    ->where('kabupaten_kota_id', $selectedKabupatenKotaId)
                    ->forPeriod($month, $year)
                    ->get()
                    ->groupBy(['kabupaten_kota_id', 'indicator_type_id']);
            }
        }
        
        return Inertia::render('Indas/DataEntry', [
            'availableKabupatenKota' => $availableKabupatenKota,
            'selectedKabupatenKota' => $selectedKabupatenKota,
            'indicators' => $indicators,
            'currentMonth' => $month,
            'currentYear' => $year,
            'selectedKabupatenKotaId' => $selectedKabupatenKotaId,
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
                abort(403, 'Akses tidak diizinkan ke wilayah ini');
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
        
        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }

    public function calculateAll(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $specificKabupatenKotaId = $request->input('kabupaten_kota_id');
        $user = $request->user();
        
        $kabupatenKotaQuery = KabupatenKota::query();
        
        if (!$user->isAdmin() && $user->provinsi_id) {
            $kabupatenKotaQuery->where('provinsi_id', $user->provinsi_id);
        }
        
        // If specific kabupaten/kota is requested, calculate for that only
        if ($specificKabupatenKotaId) {
            $kabupatenKotaQuery->where('id', $specificKabupatenKotaId);
        }
        
        $kabupatenKotaList = $kabupatenKotaQuery->get();
        $calculatedCount = 0;
        
        foreach ($kabupatenKotaList as $kabupatenKota) {
            if ($this->calculateScores($kabupatenKota->id, $month, $year)) {
                $calculatedCount++;
            }
        }
        
        $message = $specificKabupatenKotaId 
            ? "Calculated scores for {$kabupatenKotaList->first()?->nama}" 
            : "Calculated scores for {$calculatedCount} regions";
            
        return redirect()->back()->with('success', $message);
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

    /**
     * Get comprehensive regional information for kabupaten/kota
     */
    private function getRegionalInfo($user, $kabupatenKotaIds)
    {
        if (empty($kabupatenKotaIds)) {
            return [];
        }

        // Get security category ID for demo points
        $securityCategory = Category::where('slug', 'keamanan')->first();
        
        $regionalInfo = [];
        
        foreach ($kabupatenKotaIds as $kabupatenKotaId) {
            $kabupatenKota = KabupatenKota::with('provinsi')->find($kabupatenKotaId);
            
            if (!$kabupatenKota) continue;
            
            // Check access permissions
            if (!$user->isAdmin() && $user->provinsi_id && $kabupatenKota->provinsi_id !== $user->provinsi_id) {
                continue;
            }
            
            // Get demo points from monitoring_data with security category
            $demoPoints = [];
            if ($securityCategory) {
                $demoPoints = MonitoringData::where('kabupaten_kota_id', $kabupatenKotaId)
                    ->where('category_id', $securityCategory->id)
                    ->where('status', 'active')
                    ->select('title', 'description', 'latitude', 'longitude', 'incident_date', 'severity_level')
                    ->orderBy('incident_date', 'desc')
                    ->limit(10) // Limit to recent 10 demo points
                    ->get();
            }
            
            // Get latest INDAS data for this region (all categories)
            $latestIndasData = IndasMonthlyData::with('indicatorType')
                ->where('kabupaten_kota_id', $kabupatenKotaId)
                ->latest('created_at')
                ->get()
                ->groupBy('indicatorType.category');
            
            // Organize regional information
            $regionalInfo[$kabupatenKotaId] = [
                'kabupaten_kota' => $kabupatenKota,
                'karakter_masyarakat' => $this->getCharacterOfSociety($latestIndasData),
                'objek_vital_nasional' => $this->getNationalVitalObjects($latestIndasData),
                'titik_demo' => $demoPoints,
                'pariwisata' => $this->getTourismInfo($latestIndasData),
                'sekolah' => $this->getSchoolInfo($latestIndasData),
                'rumah_sakit' => $this->getHospitalInfo($latestIndasData),
                'infrastruktur_transportasi' => $this->getTransportationInfo($latestIndasData),
                'summary_stats' => [
                    'total_demo_points' => $demoPoints->count(),
                    'critical_demo_points' => $demoPoints->where('severity_level', 'critical')->count(),
                    'total_tourist_attractions' => $latestIndasData->get('pariwisata', collect())->where('indicatorType.name', 'Objek Wisata')->sum('value') ?? 0,
                    'total_hotels' => $latestIndasData->get('pariwisata', collect())->where('indicatorType.name', 'Jumlah Hotel')->sum('value') ?? 0,
                    'total_public_facilities' => $latestIndasData->get('sosial', collect())->where('indicatorType.name', 'Fasilitas Umum')->sum('value') ?? 0,
                ]
            ];
        }
        
        return $regionalInfo;
    }

    /**
     * Extract character of society information from INDAS data
     */
    private function getCharacterOfSociety($indasData)
    {
        $socialData = $indasData->get('sosial', collect());
        
        return [
            'public_facilities' => $socialData->where('indicatorType.name', 'Fasilitas Umum')->first()?->value ?? 0,
            'social_indicators' => $socialData->map(function ($item) {
                return [
                    'name' => $item->indicatorType->name,
                    'value' => $item->value,
                    'unit' => $item->indicatorType->unit,
                ];
            })->toArray(),
        ];
    }

    /**
     * Extract national vital objects information
     */
    private function getNationalVitalObjects($indasData)
    {
        // This could be expanded based on specific indicators for vital objects
        $economicData = $indasData->get('ekonomi', collect());
        
        return [
            'banks' => $economicData->where('indicatorType.name', 'Jumlah Bank')->first()?->value ?? 0,
            'markets' => $economicData->where('indicatorType.name', 'Jumlah Pasar')->first()?->value ?? 0,
            'shops' => $economicData->where('indicatorType.name', 'Jumlah Toko')->first()?->value ?? 0,
        ];
    }

    /**
     * Extract tourism information
     */
    private function getTourismInfo($indasData)
    {
        $tourismData = $indasData->get('pariwisata', collect());
        
        return [
            'tourist_attractions' => $tourismData->where('indicatorType.name', 'Objek Wisata')->first()?->value ?? 0,
            'hotels' => $tourismData->where('indicatorType.name', 'Jumlah Hotel')->first()?->value ?? 0,
            'tourism_indicators' => $tourismData->map(function ($item) {
                return [
                    'name' => $item->indicatorType->name,
                    'value' => $item->value,
                    'unit' => $item->indicatorType->unit,
                ];
            })->toArray(),
        ];
    }

    /**
     * Extract school information (part of public facilities)
     */
    private function getSchoolInfo($indasData)
    {
        $socialData = $indasData->get('sosial', collect());
        $publicFacilities = $socialData->where('indicatorType.name', 'Fasilitas Umum')->first()?->value ?? 0;
        
        // Estimate schools as 40% of public facilities (this can be made more accurate with specific indicators)
        return [
            'estimated_schools' => round($publicFacilities * 0.4),
            'note' => 'Estimated from public facilities data. Add specific school indicator for accurate count.',
        ];
    }

    /**
     * Extract hospital information (part of public facilities)
     */
    private function getHospitalInfo($indasData)
    {
        $socialData = $indasData->get('sosial', collect());
        $publicFacilities = $socialData->where('indicatorType.name', 'Fasilitas Umum')->first()?->value ?? 0;
        
        // Estimate hospitals as 10% of public facilities (this can be made more accurate with specific indicators)
        return [
            'estimated_hospitals' => round($publicFacilities * 0.1),
            'note' => 'Estimated from public facilities data. Add specific hospital indicator for accurate count.',
        ];
    }

    /**
     * Extract transportation infrastructure information
     */
    private function getTransportationInfo($indasData)
    {
        $socialData = $indasData->get('sosial', collect());
        $publicFacilities = $socialData->where('indicatorType.name', 'Fasilitas Umum')->first()?->value ?? 0;
        
        // Estimate transportation facilities as 30% of public facilities
        return [
            'estimated_transport_facilities' => round($publicFacilities * 0.3),
            'note' => 'Estimated from public facilities data. Add specific transportation indicators for accurate count.',
        ];
    }

    /**
     * Get unjuk rasa (protest) statistics
     */
    private function getUnjukRasaStats($user)
    {
        // Get the "Sosial Budaya" category and "Unjuk rasa" subcategory
        $sosialBudayaCategory = Category::where('slug', 'sosial-budaya')->first();
        $unjukRasaSubCategory = null;
        
        if ($sosialBudayaCategory) {
            $unjukRasaSubCategory = $sosialBudayaCategory->subCategories()
                ->where('slug', 'sosial-budaya-unjuk-rasa')
                ->first();
        }
        
        if (!$unjukRasaSubCategory) {
            return [
                'total_count' => 0,
                'active_count' => 0,
                'this_month_count' => 0,
                'high_severity_count' => 0,
                'recent_incidents' => [],
                'by_province' => [],
                'subcategory_info' => null,
            ];
        }
        
        // Base query for unjuk rasa data
        $query = MonitoringData::where('category_id', $sosialBudayaCategory->id)
            ->where('sub_category_id', $unjukRasaSubCategory->id);
        
        // Apply province filter for non-admin users
        if (!$user->isAdmin() && $user->provinsi_id) {
            $query->where('provinsi_id', $user->provinsi_id);
        }
        
        $unjukRasaData = $query->with(['provinsi', 'kabupatenKota', 'kecamatan'])
            ->orderBy('incident_date', 'desc')
            ->get();
        
        // Calculate statistics
        $totalCount = $unjukRasaData->count();
        $activeCount = $unjukRasaData->where('status', 'active')->count();
        $thisMonthCount = $unjukRasaData->where('incident_date', '>=', now()->startOfMonth())->count();
        $highSeverityCount = $unjukRasaData->whereIn('severity_level', ['high', 'critical'])->count();
        
        // Get recent incidents (last 5)
        $recentIncidents = $unjukRasaData->take(5)->map(function ($incident) {
            return [
                'id' => $incident->id,
                'title' => $incident->title,
                'description' => $incident->description,
                'location' => $incident->kabupatenKota->nama . ', ' . $incident->provinsi->nama,
                'incident_date' => $incident->incident_date,
                'severity_level' => $incident->severity_level,
                'status' => $incident->status,
            ];
        });
        
        // Group by province
        $byProvince = $unjukRasaData->groupBy(function ($item) {
            return $item->provinsi->nama;
        })->map(function ($provinceData, $provinceName) {
            return [
                'province' => $provinceName,
                'count' => $provinceData->count(),
                'active_count' => $provinceData->where('status', 'active')->count(),
            ];
        })->values();
        
        // Add subcategory info with image
        $unjukRasaSubCategory->append(['image_url']);
        
        return [
            'total_count' => $totalCount,
            'active_count' => $activeCount,
            'this_month_count' => $thisMonthCount,
            'high_severity_count' => $highSeverityCount,
            'recent_incidents' => $recentIncidents,
            'by_province' => $byProvince,
            'subcategory_info' => $unjukRasaSubCategory,
        ];
    }
}