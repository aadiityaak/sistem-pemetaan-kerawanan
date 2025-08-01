<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KeamananDashboardController extends Controller
{
    public function index(): Response
    {
        // Get Keamanan category
        $keamananCategory = Category::where('slug', 'keamanan')->first();
        
        if (!$keamananCategory) {
            abort(404, 'Kategori Keamanan tidak ditemukan');
        }

        // Get all monitoring data for Keamanan category
        $monitoringData = MonitoringData::with([
            'provinsi', 
            'kabupatenKota', 
            'kecamatan',
            'category',
            'subCategory'
        ])
        ->where('category_id', $keamananCategory->id)
        ->orderBy('created_at', 'desc')
        ->get();

        // Get sub-categories for Keamanan
        $subCategories = SubCategory::where('category_id', $keamananCategory->id)
            ->orderBy('sort_order')
            ->get();

        // Calculate statistics
        $statistics = [
            'totalData' => $monitoringData->count(),
            'totalProvinsi' => Provinsi::has('monitoringData', '>', 0)->count(),
            'totalKabupatenKota' => KabupatenKota::has('monitoringData', '>', 0)->count(),
            'totalKecamatan' => Kecamatan::has('monitoringData', '>', 0)->count(),
            'totalSubCategories' => $subCategories->count(),
            'dataBySubCategory' => $monitoringData->groupBy('sub_category.name')
                ->map->count()
                ->toArray(),
            'dataByProvinsi' => $monitoringData->groupBy('provinsi.nama')
                ->map->count()
                ->toArray(),
            'dataBySeverity' => $monitoringData->groupBy('severity_level')
                ->map->count()
                ->toArray(),
            'dataByStatus' => $monitoringData->groupBy('status')
                ->map->count()
                ->toArray(),
        ];

        // Recent activities (last 10)
        $recentActivities = MonitoringData::with([
            'provinsi', 
            'kabupatenKota', 
            'kecamatan',
            'category',
            'subCategory'
        ])
        ->where('category_id', $keamananCategory->id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        return Inertia::render('Keamanan/Dashboard', [
            'keamananCategory' => $keamananCategory,
            'monitoringData' => $monitoringData,
            'subCategories' => $subCategories,
            'statistics' => $statistics,
            'recentActivities' => $recentActivities,
        ]);
    }

    public function subCategory(string $subCategorySlug): Response
    {
        // Get Keamanan category
        $keamananCategory = Category::where('slug', 'keamanan')->first();
        
        if (!$keamananCategory) {
            abort(404, 'Kategori Keamanan tidak ditemukan');
        }

        // Get specific sub-category
        $subCategory = SubCategory::where('slug', $subCategorySlug)
            ->where('category_id', $keamananCategory->id)
            ->first();
            
        if (!$subCategory) {
            abort(404, 'Sub-kategori tidak ditemukan');
        }

        // Get monitoring data for this sub-category
        $monitoringData = MonitoringData::with([
            'provinsi', 
            'kabupatenKota', 
            'kecamatan',
            'category',
            'subCategory'
        ])
        ->where('sub_category_id', $subCategory->id)
        ->orderBy('created_at', 'desc')
        ->get();

        // Calculate statistics for this sub-category
        $statistics = [
            'totalData' => $monitoringData->count(),
            'totalProvinsi' => $monitoringData->pluck('provinsi_id')->unique()->count(),
            'totalKabupatenKota' => $monitoringData->pluck('kabupaten_kota_id')->unique()->count(),
            'totalKecamatan' => $monitoringData->pluck('kecamatan_id')->unique()->count(),
            'dataByProvinsi' => $monitoringData->groupBy('provinsi.nama')
                ->map->count()
                ->toArray(),
            'dataBySeverity' => $monitoringData->groupBy('severity_level')
                ->map->count()
                ->toArray(),
            'dataByStatus' => $monitoringData->groupBy('status')
                ->map->count()
                ->toArray(),
        ];

        return Inertia::render('Keamanan/SubCategoryDashboard', [
            'keamananCategory' => $keamananCategory,
            'subCategory' => $subCategory,
            'monitoringData' => $monitoringData,
            'statistics' => $statistics,
        ]);
    }
}
