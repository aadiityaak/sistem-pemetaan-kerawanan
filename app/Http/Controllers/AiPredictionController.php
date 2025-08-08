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
        $categories = Category::active()->ordered()->get();
        
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
        ]);

        if (!$this->geminiService->isEnabled()) {
            return response()->json([
                'error' => 'Gemini AI tidak aktif. Silakan aktifkan di pengaturan.',
            ], 400);
        }

        $categoryId = $request->category_id;
        $category = Category::find($categoryId);

        // Get crime data for the selected category (last 3 months)
        $crimeData = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])
            ->where('category_id', $categoryId)
            ->where('created_at', '>=', now()->subMonths(3))
            ->orderBy('incident_date', 'desc')
            ->take(50)
            ->get();

        if ($crimeData->isEmpty()) {
            return response()->json([
                'error' => 'Tidak ada data kriminalitas untuk kategori ini dalam 3 bulan terakhir.',
            ], 404);
        }

        // Prepare data for AI analysis
        $dataForAi = $crimeData->map(function ($data) {
            return [
                'category' => $data->category->name,
                'sub_category' => $data->subCategory->name,
                'location' => $data->provinsi->nama . ', ' . $data->kabupatenKota->nama . ', ' . $data->kecamatan->nama,
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
                        'location' => $items->first()->kabupatenKota->nama,
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
                return response()->json([
                    'error' => 'Gagal mendapatkan analisis dari AI. Silakan coba lagi.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'analysis' => $aiAnalysis,
                'statistics' => $statistics,
                'data_period' => '3 bulan terakhir',
                'total_analyzed' => $crimeData->count(),
                'category' => $category->name,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat menganalisis data: ' . $e->getMessage(),
            ], 500);
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
        $prompt .= "- Periode: 3 bulan terakhir\n";
        $prompt .= "- Distribusi tingkat keparahan: " . json_encode($statistics['severity_distribution']) . "\n";
        $prompt .= "- Trend bulanan: " . json_encode($statistics['monthly_trend']) . "\n\n";

        $prompt .= "LOKASI DENGAN KASUS TERBANYAK:\n";
        foreach ($statistics['location_distribution']->take(5) as $location) {
            $prompt .= "- {$location['location']}: {$location['count']} kasus\n";
        }
        $prompt .= "\n";

        $prompt .= "SAMPLE DATA DETAIL:\n";
        foreach (array_slice($crimeData, 0, 10) as $data) {
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
        $prompt .= "   - Prediksi trend 3 bulan ke depan\n";
        $prompt .= "   - Wilayah yang berpotensi mengalami peningkatan\n";
        $prompt .= "   - Estimasi dampak berdasarkan data historis\n\n";
        
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