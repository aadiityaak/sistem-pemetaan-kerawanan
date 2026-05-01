<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class InjectAppSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get app settings with defaults and inject to all Inertia responses
        $defaultSettings = [
            'app_name' => 'Pemetaan Kerawanan',
            'app_description' => 'Sistem Informasi Pemetaan Kerawanan Indonesia',
            'footer_text' => 'Pemetaan Kerawanan © 2024',
            'app_favicon' => '/favicon.ico',
            'app_logo' => '',
            'login_logo' => '',
            'monitoring_video_enabled' => false,
        ];

        $settings = [];
        foreach ($defaultSettings as $key => $default) {
            $settings[$key] = AppSetting::get($key, $default);
        }

        Inertia::share([
            'appSettings' => $settings,
        ]);

        return $next($request);
    }
}
