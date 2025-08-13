<?php

namespace App\Http\Middleware;

use App\Models\MenuItem;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleMenuRedirects
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get current path
        $currentPath = '/' . trim($request->path(), '/');
        
        // Check if there's a menu item with a redirect path for this route
        $menuItem = MenuItem::where('is_active', true)
            ->whereNotNull('path')
            ->where('path', '!=', '')
            ->where('path', '!=', $currentPath)
            ->get()
            ->first(function ($item) use ($currentPath) {
                // Check if the current route matches any menu item that needs redirect
                return $this->routeMatches($currentPath, $item);
            });
        
        if ($menuItem && $menuItem->path && $menuItem->path !== $currentPath) {
            return redirect($menuItem->path, 302);
        }

        return $next($request);
    }
    
    /**
     * Check if the route matches a menu item that should be redirected
     */
    private function routeMatches(string $currentPath, MenuItem $menuItem): bool
    {
        // Define routes that should trigger redirects based on menu title
        $redirectRules = [
            'IPOLEKSOSBUDKAM' => ['/dashboard'],
            'ISU NEGATIF ANGGOTA BRIMOB' => ['/dashboard'],
            'KALENDER KAMTIBMAS' => ['/event'],
            'AGENDA' => ['/event'],
            'AGENDA INTERNAL KORP BRIMOB POLRI' => ['/agenda-internal-korp-brimob'],
            'INDAS' => ['/indas'],
            'PREDIKSI AI' => ['/ai-prediction'],
        ];
        
        if (isset($redirectRules[$menuItem->title])) {
            return in_array($currentPath, $redirectRules[$menuItem->title]);
        }
        
        return false;
    }
}