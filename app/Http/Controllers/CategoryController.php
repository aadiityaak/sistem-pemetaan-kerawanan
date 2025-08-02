<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Category::query()->withCount(['subCategories', 'monitoringData']);

    // Search functionality
    if ($request->filled('search')) {
      $query->where('name', 'like', '%' . $request->search . '%')
        ->orWhere('description', 'like', '%' . $request->search . '%');
    }

    // Filter by status
    if ($request->filled('status')) {
      $query->where('is_active', $request->status === 'active');
    }

    $categories = $query->orderBy('sort_order')->paginate(10);

    return Inertia::render('Categories/Index', [
      'categories' => $categories,
      'filters' => $request->only(['search', 'status']),
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return Inertia::render('Categories/Create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:categories,name',
      'description' => 'nullable|string|max:1000',
      'icon' => 'nullable|string|max:10',
      'color' => 'required|string|max:7',
      'is_active' => 'boolean',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    // Generate slug
    $validated['slug'] = Str::slug($validated['name']);

    // Set default sort_order if not provided
    if (!isset($validated['sort_order'])) {
      $validated['sort_order'] = Category::max('sort_order') + 1;
    }

    Category::create($validated);

    return redirect()->route('categories.index')
      ->with('success', 'Kategori berhasil ditambahkan.');
  }

  /**
   * Display the specified resource.
   */
  public function show(Category $category)
  {
    $category->load(['subCategories' => function ($query) {
      $query->orderBy('sort_order');
    }]);

    return Inertia::render('Categories/Show', [
      'category' => $category,
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Category $category)
  {
    return Inertia::render('Categories/Edit', [
      'category' => $category,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Category $category)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
      'description' => 'nullable|string|max:1000',
      'icon' => 'nullable|string|max:10',
      'color' => 'required|string|max:7',
      'is_active' => 'boolean',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    // Update slug if name changed
    if ($validated['name'] !== $category->name) {
      $validated['slug'] = Str::slug($validated['name']);
    }

    $category->update($validated);

    return redirect()->route('categories.index')
      ->with('success', 'Kategori berhasil diperbarui.');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Category $category)
  {
    // Check if category has sub categories
    if ($category->subCategories()->count() > 0) {
      return redirect()->route('categories.index')
        ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki sub kategori.');
    }

    // Check if category has monitoring data
    if ($category->monitoringData()->count() > 0) {
      return redirect()->route('categories.index')
        ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan dalam data monitoring.');
    }

    $category->delete();

    return redirect()->route('categories.index')
      ->with('success', 'Kategori berhasil dihapus.');
  }

  /**
   * Toggle category status
   */
  public function toggleStatus(Category $category)
  {
    $category->update(['is_active' => !$category->is_active]);

    $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

    return redirect()->route('categories.index')
      ->with('success', "Kategori berhasil {$status}.");
  }
}
