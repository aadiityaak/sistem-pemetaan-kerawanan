<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\GeminiService::class, function ($app) {
            return new \App\Services\GeminiService($app->make(\App\Services\SettingsService::class));
        });

        $this->app->singleton(\App\Services\OpenAiService::class, function ($app) {
            return new \App\Services\OpenAiService($app->make(\App\Services\SettingsService::class));
        });

        $this->app->singleton(\App\Services\AiService::class, function ($app) {
            return new \App\Services\AiService(
                $app->make(\App\Services\SettingsService::class),
                $app->make(\App\Services\GeminiService::class),
                $app->make(\App\Services\OpenAiService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL key length error
        Schema::defaultStringLength(191);
    }
}
