<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    public function isEnabled(): bool
    {
        return $this->settingsService->getSetting('openai_enabled', 'false') === 'true' &&
            !empty($this->getApiKey());
    }

    public function getProviderName(): string
    {
        return 'ChatGPT (OpenAI)';
    }

    public function getApiKey(): string
    {
        return $this->settingsService->getSetting('openai_api_key', '');
    }

    public function getBaseUrl(): string
    {
        return rtrim($this->settingsService->getSetting('openai_api_base_url', 'https://api.openai.com'), '/');
    }

    public function getModel(): string
    {
        return $this->settingsService->getSetting('openai_model', 'gpt-4o-mini');
    }

    public function generateContent(string $prompt, array $context = []): ?string
    {
        if (!$this->isEnabled()) {
            Log::warning('OpenAI is not enabled or not properly configured');
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getApiKey(),
                ])
                ->post($this->getBaseUrl() . '/v1/chat/completions', [
                    'model' => $this->getModel(),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah analis data kriminalitas. Jawab dalam bahasa Indonesia, ringkas, jelas, dan berbasis data yang diberikan.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['choices'][0]['message']['content'])) {
                    return $data['choices'][0]['message']['content'];
                }
            }

            Log::error('OpenAI API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI API exception: ' . $e->getMessage());
            return null;
        }
    }
}

