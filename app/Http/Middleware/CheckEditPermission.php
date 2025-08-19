<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEditPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (!$user->isActive()) {
            auth()->logout();
            return redirect('/login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        // Admin VIP cannot edit anything
        if ($user->isAdminVip()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit data.');
        }

        // Check if user can edit (only super_admin and admin can edit)
        if (!$user->canEdit()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit data.');
        }

        return $next($request);
    }
}
