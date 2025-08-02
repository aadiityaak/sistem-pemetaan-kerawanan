<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter category jika ada
        $categorySlug = $request->query('category');
        $selectedCategory = null;

        // Query monitoring data
        $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory']);

        // Filter berdasarkan category jika ada
        if ($categorySlug) {
            $selectedCategory = Category::where('slug', $categorySlug)->first();
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        $monitoringData = $query->get();

        // Hitung statistik
        $totalData = $monitoringData->count();
        $totalProvinsi = $monitoringData->groupBy('provinsi_id')->count();
        $totalKabupatenKota = $monitoringData->groupBy('kabupaten_kota_id')->count();
        $totalKecamatan = $monitoringData->groupBy('kecamatan_id')->count();
        $totalTerdampak = $monitoringData->sum('jumlah_terdampak');

        // Jika ada filter kategori, hitung sub categories, jika tidak hitung total categories
        $totalSubCategories = $selectedCategory
          ? $monitoringData->groupBy('sub_category_id')->count()
          : Category::count();

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

        return Inertia::render('Dashboard', [
            'monitoringData' => $monitoringData,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'statistics' => [
                'totalData' => $totalData,
                'totalProvinsi' => $totalProvinsi,
                'totalKabupatenKota' => $totalKabupatenKota,
                'totalKecamatan' => $totalKecamatan,
                'totalTerdampak' => $totalTerdampak,
                'totalSubCategories' => $totalSubCategories,
                'dataBySubCategory' => $dataBySubCategory,
                'dataByProvinsi' => $dataByProvinsi,
                'dataBySeverity' => $dataBySeverity,
                'dataByStatus' => $dataByStatus,
            ],
            'recentActivities' => $recentActivities,
        ]);
    }
}
