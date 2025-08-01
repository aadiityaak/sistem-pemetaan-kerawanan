<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\MonitoringDataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeamananDashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/api/provinsi', function () {
    return \App\Models\Provinsi::select('id', 'nama')->get();
});

Route::get('/api/kabupaten-kota/{provinsi_id}', function ($provinsi_id) {
    return \App\Models\KabupatenKota::where('provinsi_id', $provinsi_id)
        ->select('id', 'nama', 'provinsi_id')
        ->get();
});

Route::get('/api/kecamatan/{kabupaten_kota_id}', function ($kabupaten_kota_id) {
    return \App\Models\Kecamatan::where('kabupaten_kota_id', $kabupaten_kota_id)
        ->select('id', 'nama', 'kabupaten_kota_id')
        ->get();
});

Route::get('/api/categories', function () {
    return \App\Models\Category::active()->ordered()->select('id', 'name', 'slug')->get();
});

Route::get('/api/sub-categories/{category_id}', function ($category_id) {
    return \App\Models\SubCategory::where('category_id', $category_id)
        ->active()
        ->ordered()
        ->select('id', 'name', 'slug', 'category_id')
        ->get();
});

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard sub-menus
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/ideologi', [DashboardController::class, 'ideologi'])->name('dashboard.ideologi');
    Route::get('/dashboard/politik', [DashboardController::class, 'politik'])->name('dashboard.politik');
    Route::get('/dashboard/ekonomi', [DashboardController::class, 'ekonomi'])->name('dashboard.ekonomi');
    Route::get('/dashboard/sosial-budaya', [DashboardController::class, 'sosialBudaya'])->name('dashboard.sosial-budaya');
    Route::get('/dashboard/keamanan', [DashboardController::class, 'keamanan'])->name('dashboard.keamanan');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/provinsi', [ProvinsiController::class, 'index'])->name('provinsi.index');
    Route::get('/provinsi/{id}', [ProvinsiController::class, 'show'])->name('provinsi.show');

    Route::get('/kabupaten-kota', [KabupatenKotaController::class, 'index'])->name('kabupaten_kota.index');
    Route::get('/kabupaten-kota/{id}', [KabupatenKotaController::class, 'show'])->name('kabupaten_kota.show');

    Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');
    Route::get('/kecamatan/{id}', [KecamatanController::class, 'show'])->name('kecamatan.show');

    Route::get('/monitoring-data', [MonitoringDataController::class, 'index'])->name('monitoring_data.index');
    Route::get('/monitoring-data/create', [MonitoringDataController::class, 'create'])->name('monitoring_data.create');
    Route::post('/monitoring-data', [MonitoringDataController::class, 'store'])->name('monitoring_data.store');
    Route::get('/monitoring-data/{id}/edit', [MonitoringDataController::class, 'edit'])->name('monitoring_data.edit');
    Route::put('/monitoring-data/{id}', [MonitoringDataController::class, 'update'])->name('monitoring_data.update');
    Route::delete('/monitoring-data/{id}', [MonitoringDataController::class, 'destroy'])->name('monitoring_data.destroy');

    // Keamanan Dashboard Routes
    Route::get('/keamanan', [KeamananDashboardController::class, 'index'])->name('keamanan.dashboard');
    Route::get('/keamanan/{subCategorySlug}', [KeamananDashboardController::class, 'subCategory'])->name('keamanan.subcategory');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
