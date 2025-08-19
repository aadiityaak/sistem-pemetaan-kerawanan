<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProvinceFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if ($user && $user->role === 'admin') {
            // Only 'admin' role gets province filter - super_admin and admin_vip see all data
            $request->merge([
                'province_filter' => $user->provinsi_id
            ]);
        }

        return $next($request);
    }
}
