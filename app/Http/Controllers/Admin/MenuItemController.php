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

    /**
     * Reorder menu items based on drag and drop (order only, no hierarchy change).
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->items as $itemData) {
            MenuItem::where('id', $itemData['id'])->update([
                'sort_order' => $itemData['sort_order']
            ]);
        }

        return redirect()->back()
            ->with('success', 'Menu order updated successfully.');
    }

    /**
     * Indent menu item to the right (make it sub-menu of previous sibling).
     */
    public function indent(MenuItem $menuItem)
    {
        // Find previous sibling at the same level
        $previousSibling = MenuItem::where('parent_id', $menuItem->parent_id)
            ->where('sort_order', '<', $menuItem->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousSibling) {
            // Make this menu a child of the previous sibling
            $menuItem->update([
                'parent_id' => $previousSibling->id,
                'sort_order' => $this->getNextSortOrder($previousSibling->id)
            ]);
            
            return redirect()->back()
                ->with('success', "Menu '{$menuItem->title}' berhasil dijadikan sub-menu dari '{$previousSibling->title}'.");
        }

        return redirect()->back()
            ->with('error', 'Tidak dapat mengindent menu ini. Tidak ada menu sebelumnya untuk dijadikan parent.');
    }

    /**
     * Outdent menu item to the left (reduce hierarchy level).
     */
    public function outdent(MenuItem $menuItem)
    {
        // Can only outdent if it has a parent
        if (!$menuItem->parent_id) {
            return redirect()->back()
                ->with('error', 'Menu ini sudah berada di level tertinggi.');
        }

        $parent = $menuItem->parent;
        
        // Find the appropriate sort order to maintain logical positioning
        // Place it right after the parent at the same level as parent
        $newParentId = $parent->parent_id;
        
        // Get the parent's sort order and increment by 1
        $newSortOrder = $parent->sort_order + 1;
        
        // Shift other items at the same level that come after this position
        MenuItem::where('parent_id', $newParentId)
            ->where('sort_order', '>=', $newSortOrder)
            ->increment('sort_order');
        
        // Move to same level as parent, positioned right after parent
        $menuItem->update([
            'parent_id' => $newParentId,
            'sort_order' => $newSortOrder
        ]);

        return redirect()->back()
            ->with('success', "Menu '{$menuItem->title}' berhasil dipindahkan ke level yang lebih tinggi.");
    }

    /**
     * Reset menu items to default configuration.
     */
    public function reset()
    {
        try {
            // Run the seeder to reset menu to default state
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\MenuItemSeeder',
                '--force' => true
            ]);

            return redirect()->back()
                ->with('success', 'Menu berhasil direset ke pengaturan default!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal reset menu: ' . $e->getMessage());
        }
    }

    /**
     * Get next sort order for a given parent.
     */
    private function getNextSortOrder($parentId = null)
    {
        $maxOrder = MenuItem::where('parent_id', $parentId)->max('sort_order');
        return ($maxOrder ?? 0) + 1;
    }
}