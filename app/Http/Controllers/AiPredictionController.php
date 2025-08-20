<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonitoringData;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AiPredictionController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Display the AI prediction page
     */
    public function index()
    {
        $categories = Category::active()->ordered()->with('subCategories')->get();

        return Inertia::render('AiPrediction/Index', [
            'categories' => $categories,
            'geminiEnabled' => $this->geminiService->isEnabled(),
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
        ]);

        if (!$this->geminiService->isEnabled()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Gemini AI tidak aktif. Silakan aktifkan di pengaturan.',
                ], 400);
            }

            return back()->withErrors([
                'error' => 'Gemini AI tidak aktif. Silakan aktifkan di pengaturan.'
            ]);
        }

        $categoryId = $request->category_id;
        $subCategoryId = $request->sub_category_id;
        $category = Category::find($categoryId);

        // Get crime data for the selected category (last 6 months)
        $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])
            ->where('category_id', $categoryId)
            ->where('created_at', '>=', now()->subMonths(6));

        // Filter by sub-category if provided
        if ($subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
        }

        $crimeData = $query->orderBy('incident_date', 'desc')
            ->take(100)
            ->get();

        if ($crimeData->isEmpty()) {
            $errorMessage = $subCategoryId
                ? 'Tidak ada data kriminalitas untuk sub-kategori ini dalam 6 bulan terakhir.'
                : 'Tidak ada data kriminalitas untuk kategori ini dalam 6 bulan terakhir.';

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
        $prompt = $this->createAnalysisPrompt($category->name, $dataForAi, $statistics);

        try {
            $aiAnalysis = $this->geminiService->generateContent($prompt);

            if (!$aiAnalysis) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'Gagal mendapatkan analisis dari AI. Silakan coba lagi.',
                    ], 500);
                }

                return back()->withErrors([
                    'error' => 'Gagal mendapatkan analisis dari AI. Silakan coba lagi.'
                ]);
            }

            $subCategory = $subCategoryId ? \App\Models\SubCategory::find($subCategoryId) : null;

            $result = [
                'success' => true,
                'analysis' => $aiAnalysis,
                'statistics' => $statistics,
                'data_period' => '6 bulan terakhir',
                'total_analyzed' => $crimeData->count(),
                'category' => $category->name,
                'sub_category' => $subCategory ? $subCategory->name : null,
            ];

            // Return JSON for AJAX requests
            if ($request->wantsJson()) {
                return response()->json($result);
            }

            // Return Inertia response for form submissions
            return back()->with('analysisResult', $result);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Terjadi kesalahan saat menganalisis data: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menganalisis data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create comprehensive prompt for AI analysis
     */
    private function createAnalysisPrompt(string $categoryName, array $crimeData, array $statistics): string
    {
        $prompt = "Sebagai seorang ahli analisis kriminalitas, analisis data kejahatan kategori '{$categoryName}' berikut:\n\n";

        $prompt .= "STATISTIK UMUM:\n";
        $prompt .= "- Total kasus: " . $statistics['total_cases'] . "\n";
        $prompt .= "- Periode: 6 bulan terakhir\n";
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
            $prompt .= "  Terdampak: {$data['affected_count']} orang\n";
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
        $prompt .= "   - Prediksi trend 6 bulan ke depan berdasarkan data 6 bulan terakhir\n";
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
}
