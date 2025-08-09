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
        $selectedMonth = $request->query('month');
        $selectedYear = $request->query('year');
        $selectedCategory = null;
        $selectedSubCategory = null;

        // Query monitoring data
        $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory']);

        // Filter berdasarkan category jika ada
        if ($categorySlug) {
            $selectedCategory = Category::where('slug', $categorySlug)->first();
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
                
                // Filter berdasarkan subcategory jika ada
                if ($subCategorySlug) {
                    $selectedSubCategory = SubCategory::where('slug', $subCategorySlug)
                        ->where('category_id', $selectedCategory->id)
                        ->first();
                    if ($selectedSubCategory) {
                        $query->where('sub_category_id', $selectedSubCategory->id);
                    }
                }
            }
        }

        // Filter berdasarkan bulan dan tahun jika ada
        if ($selectedYear) {
            $query->whereYear('incident_date', $selectedYear);
        }
        
        if ($selectedMonth) {
            $query->whereMonth('incident_date', $selectedMonth);
        }

        $monitoringData = $query->get();

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

              return [
                  'name' => $subCategory->name ?? 'Unknown',
                  'icon' => $subCategory->icon ?? '📊',
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
        $allSubCategoriesData = $selectedCategory 
          ? MonitoringData::with(['subCategory'])
              ->where('category_id', $selectedCategory->id)
              ->get()
              ->groupBy('sub_category_id')
              ->map(function ($data) {
                  $subCategory = $data->first()->subCategory ?? null;
                  return [
                      'name' => $subCategory->name ?? 'Unknown',
                      'icon' => $subCategory->icon ?? '📊',
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
        
        // Ambil subcategories jika ada kategori yang dipilih
        $subCategories = $selectedCategory 
            ? $selectedCategory->subCategories()->active()->ordered()->get()
            : collect();

        return Inertia::render('Dashboard', [
            'monitoringData' => $monitoringData,
            'selectedCategory' => $selectedCategory,
            'selectedSubCategory' => $selectedSubCategory,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'categories' => $categories,
            'subCategories' => $subCategories,
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
