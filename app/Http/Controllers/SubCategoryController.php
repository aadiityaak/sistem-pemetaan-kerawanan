<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubCategory::query()->with('category')->withCount('monitoringData');

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%')
                ->orWhereHas('category', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%');
                });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $subCategories = $query->orderBy('category_id')->orderBy('sort_order')->paginate(15);

        $categories = Category::active()->orderBy('sort_order')->get();

        return Inertia::render('SubCategories/Index', [
            'subCategories' => $subCategories,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->orderBy('sort_order')->get();

        return Inertia::render('SubCategories/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check for duplicate name within the same category
        $exists = SubCategory::where('category_id', $validated['category_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'Sub kategori dengan nama ini sudah ada dalam kategori yang dipilih.',
            ]);
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        // Set default sort_order if not provided
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = SubCategory::where('category_id', $validated['category_id'])
                ->max('sort_order') + 1;
        }

        SubCategory::create($validated);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subCategory)
    {
        $subCategory->load('category');

        return Inertia::render('SubCategories/Show', [
            'subCategory' => $subCategory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategory $subCategory)
    {
        $categories = Category::active()->orderBy('sort_order')->get();

        return Inertia::render('SubCategories/Edit', [
            'subCategory' => $subCategory,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check for duplicate name within the same category (excluding current record)
        $exists = SubCategory::where('category_id', $validated['category_id'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $subCategory->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'Sub kategori dengan nama ini sudah ada dalam kategori yang dipilih.',
            ]);
        }

        // Update slug if name changed
        if ($validated['name'] !== $subCategory->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $subCategory->update($validated);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        // Check if sub category has monitoring data
        if ($subCategory->monitoringData()->count() > 0) {
            return redirect()->route('sub-categories.index')
                ->with('error', 'Sub kategori tidak dapat dihapus karena masih digunakan dalam data monitoring.');
        }

        $subCategory->delete();

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub kategori berhasil dihapus.');
    }

    /**
     * Toggle sub category status
     */
    public function toggleStatus(SubCategory $subCategory)
    {
        $subCategory->update(['is_active' => ! $subCategory->is_active]);

        $status = $subCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('sub-categories.index')
            ->with('success', "Sub kategori berhasil {$status}.");
    }
}
