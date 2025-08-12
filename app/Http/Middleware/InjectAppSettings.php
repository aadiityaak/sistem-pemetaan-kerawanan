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
            'app_name' => 'Peta Kriminal Indonesia',
            'app_description' => 'Sistem Informasi Pemetaan Data Kriminal Indonesia',
            'footer_text' => 'Peta Kriminal Indonesia © 2024',
            'app_favicon' => '/favicon.ico',
            'app_logo' => '',
            'login_logo' => '',
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
