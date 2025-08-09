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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (!$user->isActive()) {
            auth()->logout();
            return redirect('/login')->with('error', 'Your account has been deactivated.');
        }

        if ($user->role !== $role) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
