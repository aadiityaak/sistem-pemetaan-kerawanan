<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AiService
{
    public function __construct(
        protected SettingsService $settingsService,
        protected GeminiService $geminiService,
        protected OpenAiService $openAiService
    ) {}

    public function getProviderKey(): string
    {
        $provider = $this->settingsService->getSetting('ai_provider', 'gemini');

        if (!in_array($provider, ['gemini', 'openai'], true)) {
            return 'gemini';
        }

        return $provider;
    }

    public function getProviderName(): string
    {
        return $this->getProviderKey() === 'openai'
            ? $this->openAiService->getProviderName()
            : 'Gemini (Google)';
    }

    public function isEnabled(): bool
    {
        return $this->getProviderKey() === 'openai'
            ? $this->openAiService->isEnabled()
            : $this->geminiService->isEnabled();
    }

    public function generateContent(string $prompt, array $context = []): ?string
    {
        $provider = $this->getProviderKey();

        $result = $provider === 'openai'
            ? $this->openAiService->generateContent($prompt, $context)
            : $this->geminiService->generateContent($prompt, $context);

        if ($result === null) {
            Log::warning('AI provider returned null', ['provider' => $provider]);
        }

        return $result;
    }
}

