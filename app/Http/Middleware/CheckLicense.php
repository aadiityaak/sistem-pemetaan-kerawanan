<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        if (
            $request->is('login') ||
            $request->is('register') ||
            $request->is('forgot-password') ||
            $request->is('forgot-password/*') ||
            $request->is('reset-password/*') ||
            $request->is('settings') ||
            $request->is('settings/*')
        ) {
            return $next($request);
        }

        $envKey = env('KEY_API');
        $expiresAt = AppSetting::get('license_expires_at');
        $storedHash = AppSetting::get('license_key_hash');

        if (! $envKey || ! $expiresAt || ! $storedHash) {
            return $this->expiredResponse($request);
        }

        $currentHash = hash('sha256', $envKey);

        if ($currentHash !== $storedHash) {
            return $this->expiredResponse($request);
        }

        $expires = \Carbon\Carbon::parse($expiresAt);

        if (now()->greaterThan($expires)) {
            return $this->expiredResponse($request);
        }

        return $next($request);
    }

    private function expiredResponse(Request $request): Response
    {
        $envExpiry = env('EXPIRED_DATE');
        $message = 'Lisensi aplikasi telah expired. Silakan perbarui lisensi melalui halaman Settings.';

        if ($envExpiry) {
            $message = 'Lisensi aplikasi telah expired. Masa berlaku lisensi berakhir pada '.$envExpiry.'. Silakan perbarui lisensi melalui halaman Settings.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        return response($message, 403);
    }
}
