<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (! $user->isActive()) {
            auth()->logout();

            return redirect('/login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        // Check if user has any of the required roles
        if (! in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak. Izin tidak mencukupi.');
        }

        return $next($request);
    }
}
