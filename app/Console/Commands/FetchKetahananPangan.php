<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\FoodPriceSnapshot;

class FetchKetahananPangan extends Command
{
    protected $signature = 'fetch:ketahanan-pangan';

    protected $description = 'Fetch daily food price data and store snapshot';

    public function handle(): int
    {
        $this->fetchHargaPetaProvinsi();
        $this->fetchHargaInformasi();
        return self::SUCCESS;
    }

    private function fetchHargaPetaProvinsi(): void
    {
        $params = [
            'level_harga_id' => 3,
            'komoditas_id' => 35,
            'period_date' => now()->format('d/m/Y') . ' - ' . now()->format('d/m/Y'),
            'multi_status_map[0]' => '',
            'multi_province_id[0]' => '',
        ];

        $url = 'https://api-panelhargav2.badanpangan.go.id/api/front/harga-peta-provinsi?' . http_build_query($params);

        $response = Http::withHeaders([
            'Origin' => 'https://panelharga.badanpangan.go.id',
            'Referer' => 'https://panelharga.badanpangan.go.id/beranda',
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'application/json',
        ])->timeout(60)->get($url);

        if ($response->successful()) {
            $endpoint = 'harga_peta_provinsi';
            $dateKey = now()->toDateString();
            $paramsHash = md5(json_encode($params));
            FoodPriceSnapshot::updateOrCreate(
                ['endpoint' => $endpoint, 'date_key' => $dateKey, 'params_hash' => $paramsHash],
                ['params' => $params, 'payload' => $response->json(), 'fetched_at' => now()]
            );
        }
    }

    private function fetchHargaInformasi(): void
    {
        $params = [
            'province_id' => '',
            'city_id' => '',
            'level_harga_id' => 3,
        ];

        $url = 'https://api-panelhargav2.badanpangan.go.id/api/front/harga-pangan-informasi?' . http_build_query($params);

        $response = Http::withHeaders([
            'Origin' => 'https://panelharga.badanpangan.go.id',
            'Referer' => 'https://panelharga.badanpangan.go.id/beranda',
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'application/json',
        ])->timeout(60)->get($url);

        if ($response->successful()) {
            $endpoint = 'harga_pangan_informasi';
            $dateKey = now()->toDateString();
            $paramsHash = md5(json_encode($params));
            FoodPriceSnapshot::updateOrCreate(
                ['endpoint' => $endpoint, 'date_key' => $dateKey, 'params_hash' => $paramsHash],
                ['params' => $params, 'payload' => $response->json(), 'fetched_at' => now()]
            );
        }
    }
}

