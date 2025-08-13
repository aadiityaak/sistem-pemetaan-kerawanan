<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the menu items.
     */
    public function index()
    {
        $menuItems = MenuItem::with(['children', 'parent'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/MenuItems/Index', [
            'menuItems' => $menuItems,
        ]);
    }

    /**
     * Show the form for creating a new menu item.
     */
    public function create()
    {
        $parentMenuItems = MenuItem::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/MenuItems/Create', [
            'parentMenuItems' => $parentMenuItems,
        ]);
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'parent_id' => 'nullable|exists:menu_items,id',
            'admin_only' => 'boolean',
            'description' => 'nullable|string',
        ]);

        MenuItem::create($request->all());

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item created successfully.');
    }

    /**
     * Display the specified menu item.
     */
    public function show(MenuItem $menuItem)
    {
        $menuItem->load(['children', 'parent']);

        return Inertia::render('Admin/MenuItems/Show', [
            'menuItem' => $menuItem,
        ]);
    }

    /**
     * Show the form for editing the specified menu item.
     */
    public function edit(MenuItem $menuItem)
    {
        $parentMenuItems = MenuItem::whereNull('parent_id')
            ->where('id', '!=', $menuItem->id)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/MenuItems/Edit', [
            'menuItem' => $menuItem,
            'parentMenuItems' => $parentMenuItems,
        ]);
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'parent_id' => 'nullable|exists:menu_items,id',
            'admin_only' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $menuItem->update($request->all());

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Toggle the active status of a menu item.
     */
    public function toggleStatus(MenuItem $menuItem)
    {
        $menuItem->update(['is_active' => !$menuItem->is_active]);

        return redirect()->back()
            ->with('success', 'Menu item status updated successfully.');
    }
}