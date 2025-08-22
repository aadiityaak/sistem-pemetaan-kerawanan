<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class KetahanePanganController extends Controller
{
    /**
     * Display the main food security dashboard
     */
    public function index()
    {
        return Inertia::render('KetahanPangan/Index', [
            'komoditas' => $this->getKomoditasOptions(),
        ]);
    }

    /**
     * Proxy API call to external food price API (to avoid CORS issues)
     */
    public function getHargaPeta(Request $request)
    {
        // Simply proxy the request to external API
        try {
            // Build query parameters manually to avoid encoding issues
            $params = [
                'level_harga_id' => $request->get('level_harga_id', 3),
                'komoditas_id' => $request->get('komoditas_id', 35),
                'period_date' => $request->get('period_date', now()->format('d/m/Y').' - '.now()->format('d/m/Y')),
                'multi_status_map[0]' => $request->get('multi_status_map.0', ''),
                'multi_province_id[0]' => $request->get('multi_province_id.0', ''),
            ];

            $url = 'https://api-panelhargav2.badanpangan.go.id/api/front/harga-peta-provinsi?' . http_build_query($params);
            
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => 'External API error',
                    'status' => $response->status(),
                    'message' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'API request failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commodity options based on the provided HTML select
     */
    private function getKomoditasOptions(): array
    {
        return [
            ['value' => '27', 'label' => 'Beras Premium'],
            ['value' => '28', 'label' => 'Beras Medium'],
            ['value' => '109', 'label' => 'Beras SPHP'],
            ['value' => '102', 'label' => 'Jagung Tk Peternak'],
            ['value' => '29', 'label' => 'Kedelai Biji Kering (Impor)'],
            ['value' => '30', 'label' => 'Bawang Merah'],
            ['value' => '31', 'label' => 'Bawang Putih Bonggol'],
            ['value' => '32', 'label' => 'Cabai Merah Keriting'],
            ['value' => '126', 'label' => 'Cabai Merah Besar'],
            ['value' => '34', 'label' => 'Daging Sapi Murni'],
            ['value' => '33', 'label' => 'Cabai Rawit Merah'],
            ['value' => '35', 'label' => 'Daging Ayam Ras'],
            ['value' => '36', 'label' => 'Telur Ayam Ras'],
            ['value' => '37', 'label' => 'Gula Konsumsi'],
            ['value' => '38', 'label' => 'Minyak Goreng Kemasan'],
            ['value' => '101', 'label' => 'Minyak Goreng Curah'],
            ['value' => '40', 'label' => 'Tepung Terigu (Curah)'],
            ['value' => '127', 'label' => 'Minyakita'],
            ['value' => '108', 'label' => 'Tepung Terigu Kemasan'],
            ['value' => '104', 'label' => 'Ikan Kembung'],
            ['value' => '105', 'label' => 'Ikan Tongkol'],
            ['value' => '106', 'label' => 'Ikan Bandeng'],
            ['value' => '107', 'label' => 'Garam Konsumsi'],
            ['value' => '149', 'label' => 'Daging Kerbau Beku (Impor Luar Negeri)'],
            ['value' => '152', 'label' => 'Daging Kerbau Segar (Lokal)'],
        ];
    }
}
