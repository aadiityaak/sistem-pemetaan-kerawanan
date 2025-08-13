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
        $menuItems = MenuItem::getMenuTree($user);
        
        return response()->json($menuItems);
    }
}