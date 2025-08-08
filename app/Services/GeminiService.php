<?php

namespace App\Services;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Check if Gemini AI is enabled and configured
     */
    public function isEnabled(): bool
    {
        return $this->settingsService->getSetting('gemini_enabled', 'false') === 'true' &&
               !empty($this->getApiKey()) &&
               !empty($this->getEndpoint());
    }

    /**
     * Get Gemini API key
     */
    public function getApiKey(): string
    {
        return $this->settingsService->getSetting('gemini_api_key', '');
    }

    /**
     * Get Gemini API endpoint
     */
    public function getEndpoint(): string
    {
        return $this->settingsService->getSetting('gemini_api_endpoint', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent');
    }

    /**
     * Generate content using Gemini API
     */
    public function generateContent(string $prompt, array $context = []): ?string
    {
        if (!$this->isEnabled()) {
            Log::warning('Gemini AI is not enabled or not properly configured');
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getEndpoint() . '?key=' . $this->getApiKey(), [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 1,
                        'topP' => 1,
                        'maxOutputTokens' => 2048,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            Log::error('Gemini API error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini API exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analyze crime data and generate insights
     */
    public function analyzeCrimeData(array $crimeData): ?string
    {
        if (empty($crimeData)) {
            return null;
        }

        $prompt = "Analisis data kriminalitas berikut dan berikan insight yang berguna:\n\n";
        
        foreach ($crimeData as $data) {
            $prompt .= "- Kategori: {$data['category']}\n";
            $prompt .= "  Lokasi: {$data['location']}\n";
            $prompt .= "  Tanggal: {$data['date']}\n";
            $prompt .= "  Tingkat: {$data['severity']}\n";
            $prompt .= "  Deskripsi: {$data['description']}\n\n";
        }

        $prompt .= "Berikan analisis dalam bahasa Indonesia mengenai:\n";
        $prompt .= "1. Pola atau trend yang terlihat\n";
        $prompt .= "2. Area atau waktu dengan risiko tinggi\n";
        $prompt .= "3. Rekomendasi pencegahan\n";
        $prompt .= "4. Prioritas penanganan\n";

        return $this->generateContent($prompt);
    }

    /**
     * Generate crime report summary
     */
    public function generateReportSummary(array $reportData): ?string
    {
        $prompt = "Buatkan ringkasan laporan kriminalitas berdasarkan data berikut:\n\n";
        $prompt .= json_encode($reportData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nBuatkan ringkasan dalam bahasa Indonesia yang mencakup statistik utama dan temuan penting.";

        return $this->generateContent($prompt);
    }
}