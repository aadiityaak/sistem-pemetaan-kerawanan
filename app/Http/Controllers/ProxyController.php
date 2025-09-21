<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function pusiknas(Request $request): Response
    {
        try {
            // Cache key untuk konten Pusiknas
            $cacheKey = 'pusiknas_content';

            // Cek apakah ada konten yang di-cache (1 hari = 1440 menit)
            $cachedContent = Cache::get($cacheKey);

            if ($cachedContent) {
                // Jika ada cache, langsung return dari cache
                return response($cachedContent)
                    ->header('Content-Type', 'text/html; charset=utf-8')
                    ->header('X-Frame-Options', 'ALLOWALL')
                    ->header('Content-Security-Policy', 'frame-ancestors *;')
                    ->header('X-Cache-Status', 'HIT'); // Header untuk debugging
            }

            // Jika tidak ada cache, ambil dari website asli
            $targetUrl = 'https://pusiknas.polri.go.id/peta_kriminalitas';

            // Buat request ke website target dengan headers yang sesuai
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ])->timeout(30)->get($targetUrl);

            if ($response->successful()) {
                $content = $response->body();

                // Modifikasi content untuk mengubah relative URLs menjadi absolute
                $content = str_replace(
                    [
                        'src="/',
                        'href="/',
                        'src="./',
                        'href="./',
                        'url(/',
                        "src='/",
                        "href='/",
                        'action="/',
                    ],
                    [
                        'src="https://pusiknas.polri.go.id/',
                        'href="https://pusiknas.polri.go.id/',
                        'src="https://pusiknas.polri.go.id/',
                        'href="https://pusiknas.polri.go.id/',
                        'url(https://pusiknas.polri.go.id/',
                        "src='https://pusiknas.polri.go.id/",
                        "href='https://pusiknas.polri.go.id/",
                        'action="https://pusiknas.polri.go.id/',
                    ],
                    $content
                );

                // Hapus X-Frame-Options headers yang bisa konflik
                $content = preg_replace('/<meta[^>]*http-equiv=["\']X-Frame-Options["\'][^>]*>/i', '', $content);

                // Inject CSS untuk memastikan styling tetap bekerja
                $additionalCSS = '
                <style>
                    body { margin: 0; padding: 0; }
                    .iframe-container { width: 100%; height: 100vh; overflow: auto; }
                </style>';

                $content = str_replace('</head>', $additionalCSS . '</head>', $content);

                // Simpan ke cache untuk 1 hari (1440 menit)
                Cache::put($cacheKey, $content, now()->addDay());

                return response($content)
                    ->header('Content-Type', 'text/html; charset=utf-8')
                    ->header('X-Frame-Options', 'ALLOWALL') // Override X-Frame-Options
                    ->header('Content-Security-Policy', 'frame-ancestors *;') // Allow framing
                    ->header('X-Cache-Status', 'MISS'); // Header untuk debugging
            }

            return response('Gagal mengambil data dari Pusiknas', 500);

        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }
    }
}
