<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter category dan subcategory jika ada
        $categorySlug = $request->query('category');
        $subCategorySlug = $request->query('subcategory');
        
        // Set default date range to last 6 months if not provided
        $defaultStartDate = now()->subMonths(6)->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');
        
        $startDate = $request->query('start_date', $defaultStartDate);
        $endDate = $request->query('end_date', $defaultEndDate);
        $selectedCategory = null;
        $selectedSubCategory = null;

        // Query monitoring data
        $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory']);

        // Apply province filter for non-admin users
        if ($request->has('province_filter')) {
            $query->where('provinsi_id', $request->input('province_filter'));
        }

        // Filter berdasarkan category jika ada
        if ($categorySlug) {
            $selectedCategory = Category::where('slug', $categorySlug)->first();
            if ($selectedCategory) {
                // Append image URL to selected category
                $selectedCategory->append(['image_url']);
                
                $query->where('category_id', $selectedCategory->id);
                
                // Filter berdasarkan subcategory jika ada
                if ($subCategorySlug) {
                    $selectedSubCategory = SubCategory::where('slug', $subCategorySlug)
                        ->where('category_id', $selectedCategory->id)
                        ->first();
                    if ($selectedSubCategory) {
                        // Append image URL to selected subcategory
                        $selectedSubCategory->append(['image_url']);
                        $query->where('sub_category_id', $selectedSubCategory->id);
                    }
                }
            }
        }

        // Filter berdasarkan tanggal jika ada
        if ($startDate) {
            $query->whereDate('incident_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('incident_date', '<=', $endDate);
        }

        $monitoringData = $query->get();
        
        // Append image URLs to subcategories
        $monitoringData->each(function ($data) {
            if ($data->subCategory) {
                $data->subCategory->append(['image_url']);
            }
        });

        // Hitung statistik
        $totalData = $monitoringData->count();
        $totalProvinsi = $monitoringData->groupBy('provinsi_id')->count();
        $totalKabupatenKota = $monitoringData->groupBy('kabupaten_kota_id')->count();
        $totalKecamatan = $monitoringData->groupBy('kecamatan_id')->count();
        $totalTerdampak = $monitoringData->sum('jumlah_terdampak');

        // Jika ada filter subcategory, hitung 1, jika ada filter kategori hitung sub categories, jika tidak hitung total categories
        if ($selectedSubCategory) {
            $totalSubCategories = 1; // Only one subcategory selected
        } elseif ($selectedCategory) {
            $totalSubCategories = $monitoringData->groupBy('sub_category_id')->count();
        } else {
            $totalSubCategories = Category::count();
        }

        // Hitung berdasarkan sub kategori (jika ada filter kategori) atau kategori (jika tidak ada filter)
        $dataBySubCategory = $selectedCategory
          ? $monitoringData->groupBy('sub_category_id')->map(function ($data) {
              $subCategory = $data->first()->subCategory ?? null;
              if ($subCategory) {
                  $subCategory->append(['image_url']);
              }

              return [
                  'name' => $subCategory->name ?? 'Unknown',
                  'icon' => $subCategory->icon ?? '📊',
                  'image_url' => $subCategory->image_url ?? null,
                  'count' => $data->count(),
              ];
          })
          : $monitoringData->groupBy('category_id')->map(function ($data) {
              $category = $data->first()->category ?? null;

              return [
                  'name' => $category->name ?? 'Unknown',
                  'icon' => '📊', // Default icon for categories
                  'count' => $data->count(),
              ];
          });

        // Hitung berdasarkan semua sub kategori dalam kategori (untuk analytics card)
        // Ini akan selalu menampilkan semua subcategories berdasarkan kategori yang dipilih
        $allSubCategoriesQuery = $selectedCategory 
          ? MonitoringData::with(['subCategory'])
              ->where('category_id', $selectedCategory->id)
          : MonitoringData::with(['subCategory']);
          
        // Apply province filter for non-admin users in subcategory analytics
        if ($request->has('province_filter')) {
            $allSubCategoriesQuery->where('provinsi_id', $request->input('province_filter'));
        }
        
        $allSubCategoriesData = $selectedCategory 
          ? $allSubCategoriesQuery->get()
              ->groupBy('sub_category_id')
              ->map(function ($data) {
                  $subCategory = $data->first()->subCategory ?? null;
                  if ($subCategory) {
                      $subCategory->append(['image_url']);
                  }
                  return [
                      'name' => $subCategory->name ?? 'Unknown',
                      'icon' => $subCategory->icon ?? '📊',
                      'image_url' => $subCategory->image_url ?? null,
                      'count' => $data->count(),
                  ];
              })
          : $dataBySubCategory; // Fallback to categories if no category selected

        // Hitung berdasarkan provinsi
        $dataByProvinsi = $monitoringData->groupBy(function ($data) {
            return $data->provinsi->nama ?? 'Unknown';
        })->map(function ($data) {
            return $data->count();
        });

        // Hitung berdasarkan severity level
        $dataBySeverity = $monitoringData->groupBy('severity_level')->map(function ($data) {
            return $data->count();
        });

        // Hitung berdasarkan status
        $dataByStatus = $monitoringData->groupBy('status')->map(function ($data) {
            return $data->count();
        });

        // Ambil recent activities (5 terbaru)
        $recentActivities = $monitoringData->sortByDesc('created_at')->take(5)->values();

        // Ambil semua kategori untuk menu
        $categories = Category::all();
        $categories->each(function ($category) {
            $category->append(['image_url']);
        });
        
        // Ambil subcategories jika ada kategori yang dipilih
        $subCategories = $selectedCategory 
            ? $selectedCategory->subCategories()->active()->ordered()->get()
            : collect();
            
        // Append image URLs to subcategories
        $subCategories->each(function ($subCategory) {
            $subCategory->append(['image_url']);
        });

        // Get user's province information for map centering
        $userProvinsi = null;
        if ($request->has('province_filter')) {
            $userProvinsi = Provinsi::select('id', 'nama', 'latitude', 'longitude')
                ->find($request->input('province_filter'));
        }

        return Inertia::render('Dashboard', [
            'monitoringData' => $monitoringData,
            'selectedCategory' => $selectedCategory,
            'selectedSubCategory' => $selectedSubCategory,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'userProvinsi' => $userProvinsi,
            'statistics' => [
                'totalData' => $totalData,
                'totalProvinsi' => $totalProvinsi,
                'totalKabupatenKota' => $totalKabupatenKota,
                'totalKecamatan' => $totalKecamatan,
                'totalTerdampak' => $totalTerdampak,
                'totalSubCategories' => $totalSubCategories,
                'dataBySubCategory' => $dataBySubCategory,
                'allSubCategoriesData' => $allSubCategoriesData,
                'dataByProvinsi' => $dataByProvinsi,
                'dataBySeverity' => $dataBySeverity,
                'dataByStatus' => $dataByStatus,
            ],
            'recentActivities' => $recentActivities,
        ]);
    }
}
