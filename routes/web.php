<?php

use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndasController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KamtibmasEventController;
use App\Http\Controllers\MonitoringDataController;
use App\Http\Controllers\ProvinsiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/api/categories-with-subcategories', function () {
    return \App\Models\Category::active()
        ->ordered()
        ->with(['subCategories' => function ($query) {
            $query->active()->ordered()->select('id', 'category_id', 'name', 'slug');
        }])
        ->select('id', 'name', 'slug')
        ->get();
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

// Dashboard dengan parameter category (contoh: /dashboard?category=keamanan)
Route::middleware(['auth', 'verified'])->group(function () {
    // Route untuk backward compatibility - redirect ke dashboard dengan parameter
    Route::get('/dashboard/ideologi', function () {
        return redirect('/dashboard?category=ideologi');
    });
    Route::get('/dashboard/politik', function () {
        return redirect('/dashboard?category=politik');
    });
    Route::get('/dashboard/ekonomi', function () {
        return redirect('/dashboard?category=ekonomi');
    });
    Route::get('/dashboard/sosial-budaya', function () {
        return redirect('/dashboard?category=sosial-budaya');
    });
    Route::get('/dashboard/keamanan', function () {
        return redirect('/dashboard?category=keamanan');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/provinsi', [ProvinsiController::class, 'index'])->name('provinsi.index');
    Route::get('/provinsi/{id}', [ProvinsiController::class, 'show'])->name('provinsi.show');

    Route::get('/kabupaten-kota', [KabupatenKotaController::class, 'index'])->name('kabupaten_kota.index');
    Route::get('/kabupaten-kota/{id}', [KabupatenKotaController::class, 'show'])->name('kabupaten_kota.show');

    Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');
    Route::get('/kecamatan/{id}', [KecamatanController::class, 'show'])->name('kecamatan.show');

    // Monitoring Data Routes
    Route::get('/monitoring-data', [MonitoringDataController::class, 'index'])->name('monitoring-data.index');
    Route::get('/monitoring-data/create', [MonitoringDataController::class, 'create'])->name('monitoring-data.create');
    Route::post('/monitoring-data', [MonitoringDataController::class, 'store'])->name('monitoring-data.store');
    Route::get('/monitoring-data/{id}', [MonitoringDataController::class, 'show'])->name('monitoring-data.show');
    Route::get('/monitoring-data/{id}/edit', [MonitoringDataController::class, 'edit'])->name('monitoring-data.edit');
    Route::put('/monitoring-data/{id}', [MonitoringDataController::class, 'update'])->name('monitoring-data.update');
    Route::delete('/monitoring-data/{id}', [MonitoringDataController::class, 'destroy'])->name('monitoring-data.destroy');
    Route::delete('/monitoring-data/{id}/gallery', [MonitoringDataController::class, 'deleteGalleryImage'])->name('monitoring-data.delete-gallery');

    // AI Prediction Routes
    Route::get('/ai-prediction', [\App\Http\Controllers\AiPredictionController::class, 'index'])->name('ai-prediction.index');
    Route::post('/ai-prediction/analyze', [\App\Http\Controllers\AiPredictionController::class, 'analyze'])->name('ai-prediction.analyze');

    // INDAS Routes
    Route::get('/indas', [IndasController::class, 'index'])->name('indas.index');
    Route::get('/indas/trends', [IndasController::class, 'trends'])->name('indas.trends');
    Route::get('/indas/recommendations', [IndasController::class, 'recommendations'])->name('indas.recommendations');

    // Categories Routes
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::post('/categories/{category}/toggle-status', [\App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::delete('/categories/{category}/delete-image', [\App\Http\Controllers\CategoryController::class, 'deleteImage'])->name('categories.delete-image');

    // Sub Categories Routes
    Route::resource('sub-categories', \App\Http\Controllers\SubCategoryController::class);
    Route::post('/sub-categories/{subCategory}/toggle-status', [\App\Http\Controllers\SubCategoryController::class, 'toggleStatus'])->name('sub-categories.toggle-status');
    Route::delete('/sub-categories/{subCategory}/delete-image', [\App\Http\Controllers\SubCategoryController::class, 'deleteImage'])->name('sub-categories.delete-image');

    // Event Calendar Routes (Universal for Kamtibmas, Agenda, etc.)
    Route::get('/event', [KamtibmasEventController::class, 'index'])->name('event.index');
    Route::post('/event', [KamtibmasEventController::class, 'store'])->name('event.store');
    Route::get('/event/{event}', [KamtibmasEventController::class, 'show'])->name('event.show');
    Route::put('/event/{event}', [KamtibmasEventController::class, 'update'])->name('event.update');
    Route::delete('/event/{event}', [KamtibmasEventController::class, 'destroy'])->name('event.destroy');
    Route::post('/event/{event}/toggle-status', [KamtibmasEventController::class, 'toggleStatus'])->name('event.toggle-status');
    
    // Legacy routes for backward compatibility
    Route::get('/kamtibmas-calendar', function() {
        return redirect('/event?event=kamtibmas');
    });
    Route::post('/kamtibmas-events', [KamtibmasEventController::class, 'store'])->name('kamtibmas-events.store');
    Route::get('/kamtibmas-events/{event}', [KamtibmasEventController::class, 'show'])->name('kamtibmas-events.show');
    Route::put('/kamtibmas-events/{event}', [KamtibmasEventController::class, 'update'])->name('kamtibmas-events.update');
    Route::delete('/kamtibmas-events/{event}', [KamtibmasEventController::class, 'destroy'])->name('kamtibmas-events.destroy');
    Route::post('/kamtibmas-events/{event}/toggle-status', [KamtibmasEventController::class, 'toggleStatus'])->name('kamtibmas-events.toggle-status');


    // Settings Routes (Fixed settings - only allow viewing and updating values)
    Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
    Route::match(['POST', 'PUT'], '/settings/{key}', [AppSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
