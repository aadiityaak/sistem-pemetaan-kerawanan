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

    $envKey = config('app.license_key') ?: env('KEY_API');
    $expiresAt = AppSetting::get('license_expires_at');
    $storedHash = AppSetting::get('license_key_hash');

    if (! $envKey || ! $expiresAt || ! $storedHash) {
      \Illuminate\Support\Facades\Log::warning('License check failed: missing data', [
        'has_env_key' => ! empty($envKey),
        'has_expires_at' => ! empty($expiresAt),
        'has_stored_hash' => ! empty($storedHash),
      ]);

      return $this->expiredResponse($request);
    }

    $currentHash = hash('sha256', $envKey);

    if ($currentHash !== $storedHash) {
      \Illuminate\Support\Facades\Log::warning('License check failed: hash mismatch', [
        'current_hash' => $currentHash,
        'stored_hash' => $storedHash,
      ]);

      return $this->expiredResponse($request);
    }

    try {
      $expires = \Carbon\Carbon::parse($expiresAt);

      if (now()->greaterThan($expires)) {
        \Illuminate\Support\Facades\Log::warning('License check failed: expired', [
          'expires_at' => $expiresAt,
          'now' => now()->toDateTimeString(),
        ]);

        return $this->expiredResponse($request);
      }
    } catch (\Exception $e) {
      \Illuminate\Support\Facades\Log::error('License check failed: parse error', [
        'expires_at' => $expiresAt,
        'error' => $e->getMessage(),
      ]);

      return $this->expiredResponse($request);
    }

    return $next($request);
  }

  private function expiredResponse(Request $request): Response
  {
    $envExpiry = config('app.license_expired_date');
    $message = 'Lisensi aplikasi telah expired. Silakan perbarui lisensi melalui halaman Settings.';

    if ($envExpiry) {
      $message = 'Lisensi aplikasi telah expired. Masa berlaku lisensi berakhir pada ' . $envExpiry . '. Silakan perbarui lisensi melalui halaman Settings.';
    }

    if ($request->expectsJson()) {
      return response()->json([
        'message' => $message,
      ], 403);
    }

    return response($message, 403);
  }
}
