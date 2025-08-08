<?php

use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KamtipmasEventController;
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

    // Categories Routes
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::post('/categories/{category}/toggle-status', [\App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Sub Categories Routes
    Route::resource('sub-categories', \App\Http\Controllers\SubCategoryController::class);
    Route::post('/sub-categories/{subCategory}/toggle-status', [\App\Http\Controllers\SubCategoryController::class, 'toggleStatus'])->name('sub-categories.toggle-status');

    // Kamtipmas Calendar Routes
    Route::get('/kamtipmas-calendar', [KamtipmasEventController::class, 'index'])->name('kamtipmas-calendar.index');
    Route::post('/kamtipmas-events', [KamtipmasEventController::class, 'store'])->name('kamtipmas-events.store');
    Route::get('/kamtipmas-events/{kamtipmasEvent}', [KamtipmasEventController::class, 'show'])->name('kamtipmas-events.show');
    Route::put('/kamtipmas-events/{kamtipmasEvent}', [KamtipmasEventController::class, 'update'])->name('kamtipmas-events.update');
    Route::delete('/kamtipmas-events/{kamtipmasEvent}', [KamtipmasEventController::class, 'destroy'])->name('kamtipmas-events.destroy');
    Route::post('/kamtipmas-events/{kamtipmasEvent}/toggle-status', [KamtipmasEventController::class, 'toggleStatus'])->name('kamtipmas-events.toggle-status');

    // Settings Routes (Fixed settings - only allow viewing and updating values)
    Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
    Route::match(['POST', 'PUT'], '/settings/{key}', [AppSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
