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
    if ($request->routeIs('settings.*') || $request->routeIs('login') || $request->routeIs('password.*') || $request->routeIs('register')) {
      return $next($request);
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
