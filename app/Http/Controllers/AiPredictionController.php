<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AiPredictionHistory;
use App\Models\MonitoringData;
use App\Services\AiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AiPredictionController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display the AI prediction page
     */
    public function index()
    {
        $categories = Category::active()->ordered()->with('subCategories')->get();
        $history = collect();
        if (Schema::hasTable('ai_prediction_histories')) {
            try {
                $userId = auth()->id();
                $history = AiPredictionHistory::query()
                    ->where('user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(20)
                    ->get([
                        'id',
                        'ai_provider_key',
                        'ai_provider_name',
                        'category_id',
                        'category_name',
                        'sub_category_id',
                        'sub_category_name',
                        'time_period',
                        'data_period',
                        'total_analyzed',
                        'created_at',
                    ]);
            } catch (\Exception $e) {
                Log::warning('Failed to load AI prediction history', ['message' => $e->getMessage()]);
            }
        }

        return Inertia::render('AiPrediction/Index', [
            'categories' => $categories,
            'aiEnabled' => $this->aiService->isEnabled(),
            'aiProvider' => [
                'key' => $this->aiService->getProviderKey(),
                'name' => $this->aiService->getProviderName(),
            ],
            'analysisHistory' => $history,
        ]);
    }

    /**
     * Analyze crime data for a specific category using AI
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'time_period' => 'required|numeric|in:0.03,0.25,1,3,6,12',
        ]);

        if (!$this->aiService->isEnabled()) {
            $providerName = $this->aiService->getProviderName();
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => "{$providerName} tidak aktif. Silakan aktifkan dan konfigurasi di pengaturan.",
                    'ai' => config('app.debug') ? $this->aiService->getDebugInfo() : [
                        'provider' => [
                            'key' => $this->aiService->getProviderKey(),
                            'name' => $providerName,
                        ],
                    ],
                ], 400);
            }

            return back()->withErrors([
                'error' => "{$providerName} tidak aktif. Silakan aktifkan dan konfigurasi di pengaturan."
            ]);
        }

        $categoryId = $request->category_id;
        $subCategoryId = $request->sub_category_id;
        $timePeriod = (float) $request->time_period;
        $category = Category::find($categoryId);

        // Calculate the starting date based on the time period
        if ($timePeriod < 1) {
            // If less than a month, use days (0.03 months is ~1 day, 0.25 months is ~7 days)
            $days = round($timePeriod * 30);
            $startDate = now()->subDays($days);
        } else {
            $startDate = now()->subMonths($timePeriod);
        }

        // Get crime data for the selected category based on time period
        $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])
            ->where('category_id', $categoryId)
            ->where('created_at', '>=', $startDate);

        // Filter by sub-category if provided
        if ($subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
        }

        $crimeData = $query->orderBy('incident_date', 'desc')
            ->take(100)
            ->get();

        if ($crimeData->isEmpty()) {
            $periodText = $this->getTimePeriodText($timePeriod);
            $errorMessage = $subCategoryId
                ? "Tidak ada data kriminalitas untuk sub-kategori ini dalam {$periodText}."
                : "Tidak ada data kriminalitas untuk kategori ini dalam {$periodText}.";

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $errorMessage,
                ], 404);
            }

            return back()->withErrors([
                'error' => $errorMessage
            ]);
        }

        // Prepare data for AI analysis
        $dataForAi = $crimeData->map(function ($data) {
            return [
                'category' => $data->category?->name ?? 'Tidak diketahui',
                'sub_category' => $data->subCategory?->name ?? 'Tidak diketahui',
                'location' => ($data->provinsi?->nama ?? 'Tidak diketahui') . ', ' . 
                             ($data->kabupatenKota?->nama ?? 'Tidak diketahui') . ', ' . 
                             ($data->kecamatan?->nama ?? 'Tidak diketahui'),
                'date' => $data->incident_date->format('Y-m-d'),
                'severity' => $data->severity_level,
                'description' => $data->description,
                'status' => $data->status,
                'affected_count' => $data->jumlah_terdampak ?? 0,
            ];
        })->toArray();

        // Get statistics for additional context
        $statistics = [
            'total_cases' => $crimeData->count(),
            'severity_distribution' => $crimeData->groupBy('severity_level')->map->count(),
            'monthly_trend' => $crimeData->groupBy(function ($item) {
                return $item->incident_date->format('Y-m');
            })->map->count()->sortKeys(),
            'location_distribution' => $crimeData->groupBy('kabupaten_kota_id')
                ->map(function ($items) {
                    return [
                        'count' => $items->count(),
                        'location' => $items->first()->kabupatenKota?->nama ?? 'Lokasi tidak diketahui',
                    ];
                })
                ->sortByDesc('count')
                ->take(10),
        ];

        // Create comprehensive prompt for AI analysis
        $prompt = $this->createAnalysisPrompt($category->name, $dataForAi, $statistics, $timePeriod);

        $providerKey = $this->aiService->getProviderKey();
        $providerName = $this->aiService->getProviderName();
        $timePeriodKey = (string) $request->time_period;
        $ids = $crimeData->pluck('id')->implode(',');
        $maxUpdatedAt = $crimeData->max('updated_at');
        $dataFingerprint = sha1($ids . '|' . ($maxUpdatedAt ? $maxUpdatedAt->timestamp : '') . '|' . $crimeData->count());

        if (Schema::hasTable('ai_prediction_histories')) {
            try {
                $existing = AiPredictionHistory::query()
                    ->where('user_id', auth()->id())
                    ->where('ai_provider_key', $providerKey)
                    ->where('category_id', $categoryId)
                    ->where('sub_category_id', $subCategoryId)
                    ->where('time_period', $timePeriodKey)
                    ->where('data_fingerprint', $dataFingerprint)
                    ->first();

                if ($existing) {
                    $result = [
                        'success' => true,
                        'analysis' => $existing->analysis,
                        'statistics' => $existing->statistics ?? $statistics,
                        'data_period' => $existing->data_period,
                        'total_analyzed' => $existing->total_analyzed,
                        'category' => $existing->category_name,
                        'sub_category' => $existing->sub_category_name,
                        'ai_provider' => [
                            'key' => $existing->ai_provider_key,
                            'name' => $existing->ai_provider_name,
                        ],
                        'history_id' => $existing->id,
                        'cached' => true,
                    ];

                    if ($request->wantsJson()) {
                        return response()->json($result);
                    }

                    return back()->with('analysisResult', $result);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to query AI prediction history cache', ['message' => $e->getMessage()]);
            }
        }

        try {
            $aiAnalysis = $this->aiService->generateContent($prompt);

            if (!$aiAnalysis) {
                Log::warning('AI analysis returned empty result', [
                    'provider' => $this->aiService->getProviderKey(),
                    'category_id' => $categoryId,
                    'sub_category_id' => $subCategoryId,
                    'time_period' => $timePeriod,
                ]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'Gagal mendapatkan analisis dari AI. Silakan coba lagi.',
                        'ai' => config('app.debug') ? $this->aiService->getDebugInfo() : [
                            'provider' => [
                                'key' => $this->aiService->getProviderKey(),
                                'name' => $this->aiService->getProviderName(),
                            ],
                        ],
                    ], 500);
                }

                return back()->withErrors([
                    'error' => 'Gagal mendapatkan analisis dari AI. Silakan coba lagi.'
                ]);
            }

            $subCategory = $subCategoryId ? \App\Models\SubCategory::find($subCategoryId) : null;

            $historyId = null;
            if (Schema::hasTable('ai_prediction_histories')) {
                try {
                    $history = AiPredictionHistory::create([
                        'user_id' => auth()->id(),
                        'ai_provider_key' => $providerKey,
                        'ai_provider_name' => $providerName,
                        'category_id' => $categoryId,
                        'category_name' => $category->name,
                        'sub_category_id' => $subCategoryId,
                        'sub_category_name' => $subCategory?->name,
                        'time_period' => $timePeriodKey,
                        'data_period' => $this->getTimePeriodText($timePeriod),
                        'start_date' => $startDate->toDateString(),
                        'end_date' => now()->toDateString(),
                        'total_analyzed' => $crimeData->count(),
                        'data_fingerprint' => $dataFingerprint,
                        'analysis' => $aiAnalysis,
                        'statistics' => $statistics,
                    ]);
                    $historyId = $history->id;
                } catch (\Exception $e) {
                    Log::warning('Failed to store AI prediction history', ['message' => $e->getMessage()]);
                }
            }

            $result = [
                'success' => true,
                'analysis' => $aiAnalysis,
                'statistics' => $statistics,
                'data_period' => $this->getTimePeriodText($timePeriod),
                'total_analyzed' => $crimeData->count(),
                'category' => $category->name,
                'sub_category' => $subCategory ? $subCategory->name : null,
                'ai_provider' => [
                    'key' => $this->aiService->getProviderKey(),
                    'name' => $this->aiService->getProviderName(),
                ],
                'history_id' => $historyId,
                'cached' => false,
            ];

            // Return JSON for AJAX requests
            if ($request->wantsJson()) {
                return response()->json($result);
            }

            // Return Inertia response for form submissions
            return back()->with('analysisResult', $result);
        } catch (\Exception $e) {
            Log::error('AI analysis exception', [
                'provider' => $this->aiService->getProviderKey(),
                'message' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Terjadi kesalahan saat menganalisis data: ' . $e->getMessage(),
                    'ai' => config('app.debug') ? $this->aiService->getDebugInfo() : [
                        'provider' => [
                            'key' => $this->aiService->getProviderKey(),
                            'name' => $this->aiService->getProviderName(),
                        ],
                    ],
                ], 500);
            }

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menganalisis data: ' . $e->getMessage()
            ]);
        }
    }

    public function historyShow(Request $request, int $id)
    {
        if (!Schema::hasTable('ai_prediction_histories')) {
            return response()->json([
                'error' => 'Riwayat analisa belum tersedia. Jalankan migrasi database terlebih dahulu.',
            ], 400);
        }

        $history = AiPredictionHistory::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'analysis' => $history->analysis,
            'statistics' => $history->statistics,
            'data_period' => $history->data_period,
            'total_analyzed' => $history->total_analyzed,
            'category' => $history->category_name,
            'sub_category' => $history->sub_category_name,
            'ai_provider' => [
                'key' => $history->ai_provider_key,
                'name' => $history->ai_provider_name,
            ],
            'history_id' => $history->id,
            'cached' => true,
        ]);
    }

    /**
     * Create comprehensive prompt for AI analysis
     */
    private function createAnalysisPrompt(string $categoryName, array $crimeData, array $statistics, $timePeriod): string
    {
        $periodText = $this->getTimePeriodText($timePeriod);
        $prompt = "Sebagai seorang ahli analisis kriminalitas, analisis data kejahatan kategori '{$categoryName}' berikut:\n\n";

        $prompt .= "STATISTIK UMUM:\n";
        $prompt .= "- Total kasus: " . $statistics['total_cases'] . "\n";
        $prompt .= "- Periode: {$periodText}\n";
        $prompt .= "- Distribusi Tingkat Resiko: " . json_encode($statistics['severity_distribution']) . "\n";
        $prompt .= "- Trend bulanan: " . json_encode($statistics['monthly_trend']) . "\n\n";

        $prompt .= "LOKASI DENGAN KASUS TERBANYAK:\n";
        foreach ($statistics['location_distribution']->take(5) as $location) {
            $prompt .= "- {$location['location']}: {$location['count']} kasus\n";
        }
        $prompt .= "\n";

        $prompt .= "SAMPLE DATA DETAIL:\n";
        foreach (array_slice($crimeData, 0, 15) as $data) {
            $prompt .= "- Lokasi: {$data['location']}\n";
            $prompt .= "  Tanggal: {$data['date']}\n";
            $prompt .= "  Tingkat: {$data['severity']}\n";
            $prompt .= "  Status: {$data['status']}\n";
            $prompt .= "  Komentar: {$data['affected_count']} orang\n";
            if (!empty($data['description'])) {
                $prompt .= "  Deskripsi: " . substr($data['description'], 0, 100) . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "TUGAS ANALISIS:\n";
        $prompt .= "Berikan analisis komprehensif dalam bahasa Indonesia yang mencakup:\n\n";
        $prompt .= "1. **POLA DAN TREND**\n";
        $prompt .= "   - Identifikasi pola waktu (hari, minggu, bulan)\n";
        $prompt .= "   - Trend peningkatan atau penurunan\n";
        $prompt .= "   - Faktor musiman yang mempengaruhi\n\n";

        $prompt .= "2. **ANALISIS GEOGRAFIS**\n";
        $prompt .= "   - Wilayah dengan risiko tinggi\n";
        $prompt .= "   - Pola penyebaran geografis\n";
        $prompt .= "   - Faktor lokasi yang berkontribusi\n\n";

        $prompt .= "3. **PREDIKSI DAN PROYEKSI**\n";
        $prompt .= "   - Prediksi trend " . ($timePeriod >= 1 ? "{$timePeriod} bulan" : round($timePeriod * 30) . " hari") . " ke depan berdasarkan data {$periodText}\n";
        $prompt .= "   - Wilayah yang berpotensi mengalami peningkatan\n";
        $prompt .= "   - Estimasi dampak berdasarkan data historis\n";
        $prompt .= "   - Proyeksi pola musiman dan siklus tahunan\n\n";

        $prompt .= "4. **REKOMENDASI STRATEGIS**\n";
        $prompt .= "   - Prioritas penanganan berdasarkan tingkat risiko\n";
        $prompt .= "   - Strategi pencegahan yang dapat diterapkan\n";
        $prompt .= "   - Alokasi sumber daya yang optimal\n\n";

        $prompt .= "5. **FAKTOR RISIKO**\n";
        $prompt .= "   - Identifikasi faktor pemicu utama\n";
        $prompt .= "   - Kondisi yang meningkatkan kemungkinan kejadian\n";
        $prompt .= "   - Indikator peringatan dini\n\n";

        $prompt .= "Format jawaban dengan struktur yang jelas menggunakan markdown dan berikan insight yang actionable untuk pengambilan keputusan.";

        return $prompt;
    }

    /**
     * Get time period text for display
     */
    private function getTimePeriodText($months): string
    {
        $months = (float) $months;

        if ($months == 0.03) return '1 hari terakhir';
        if ($months == 0.25) return '1 minggu terakhir';

        return match ((int)$months) {
            1 => '1 bulan terakhir',
            3 => '3 bulan terakhir',
            6 => '6 bulan terakhir',
            12 => '1 tahun terakhir',
            default => "{$months} bulan terakhir"
        };
    }
}
