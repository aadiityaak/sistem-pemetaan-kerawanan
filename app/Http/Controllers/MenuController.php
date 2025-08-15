<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get menu items for sidebar display
     */
    public function getMenuItems(Request $request)
    {
        $user = $request->user();
        
        try {
            $menuItems = MenuItem::getMenuTree($user);
            return response()->json($menuItems);
        } catch (\Exception $e) {
            \Log::error('Error getting menu items for API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([], 500);
        }
    }
}