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
        // Get app settings and inject to all Inertia responses
        $settings = AppSetting::pluck('value', 'key')->toArray();

        Inertia::share([
            'appSettings' => $settings
        ]);

        return $next($request);
    }
}
