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

    $envExpiry = env('EXPIRED_DATE');

    if ($envExpiry) {
      $envExpires = \Carbon\Carbon::parse($envExpiry);

      if (now()->lessThanOrEqualTo($envExpires)) {
        return $next($request);
      }
    }

    $expiresAt = AppSetting::get('license_expires_at');

    if (! $expiresAt) {
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
    if ($request->expectsJson()) {
      return response()->json([
        'message' => 'Lisensi aplikasi telah expired. Silakan perbarui lisensi melalui halaman Settings.',
      ], 403);
    }

    return response('Lisensi aplikasi telah expired. Silakan perbarui lisensi melalui halaman Settings.', 403);
  }
}
