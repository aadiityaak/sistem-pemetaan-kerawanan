<?php

use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\IndasController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MonitoringDataController;
use App\Http\Controllers\PartaiPolitikController;
use App\Http\Controllers\PasarSahamController;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\SembakoController;
use App\Http\Controllers\VideoUploadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
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
    $categories = \App\Models\Category::active()->ordered()->select('id', 'name', 'slug', 'icon', 'image_path')->get();
    $categories->each(function ($category) {
        $category->append(['image_url']);
    });
    return $categories;
});

Route::get('/api/categories-with-subcategories', function () {
    $categories = \App\Models\Category::active()
        ->ordered()
        ->with(['subCategories' => function ($query) {
            $query->active()->ordered()->select('id', 'category_id', 'name', 'slug', 'icon', 'image_path');
        }])
        ->select('id', 'name', 'slug', 'icon', 'image_path')
        ->get();
    
    // Append image URLs to categories and subcategories
    $categories->each(function ($category) {
        $category->append(['image_url']);
        $category->subCategories->each(function ($subCategory) {
            $subCategory->append(['image_url']);
        });
    });
    
    return $categories;
});

Route::get('/api/sub-categories/{category_id}', function ($category_id) {
    $subCategories = \App\Models\SubCategory::where('category_id', $category_id)
        ->active()
        ->ordered()
        ->select('id', 'name', 'slug', 'category_id', 'icon', 'image_path')
        ->get();
    $subCategories->each(function ($subCategory) {
        $subCategory->append(['image_url']);
    });
    return $subCategories;
});

Route::get('/api/menu-items', [MenuController::class, 'getMenuItems'])->name('api.menu-items');

// Video upload API routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::post('/api/upload-video-chunk', [VideoUploadController::class, 'uploadChunk'])->name('api.upload-video-chunk');
    Route::post('/api/delete-video', [VideoUploadController::class, 'deleteVideo'])->name('api.delete-video');
});

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'province.filter'])->name('dashboard');

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
    Route::get('/provinsi/{id}/edit', [ProvinsiController::class, 'edit'])->name('provinsi.edit');
    Route::put('/provinsi/{id}', [ProvinsiController::class, 'update'])->name('provinsi.update');

    Route::get('/kabupaten-kota', [KabupatenKotaController::class, 'index'])->name('kabupaten_kota.index');
    Route::get('/kabupaten-kota/{id}', [KabupatenKotaController::class, 'show'])->name('kabupaten_kota.show');

    Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');
    Route::get('/kecamatan/{id}', [KecamatanController::class, 'show'])->name('kecamatan.show');

    // Monitoring Data Routes - Apply province filter for non-admin users
    Route::middleware(['province.filter'])->group(function () {
        Route::get('/monitoring-data', [MonitoringDataController::class, 'index'])->name('monitoring-data.index');
        Route::get('/monitoring-data/create', [MonitoringDataController::class, 'create'])->name('monitoring-data.create');
        Route::post('/monitoring-data', [MonitoringDataController::class, 'store'])->name('monitoring-data.store');
        Route::get('/monitoring-data/{id}', [MonitoringDataController::class, 'show'])->name('monitoring-data.show');
        Route::get('/monitoring-data/{id}/edit', [MonitoringDataController::class, 'edit'])->name('monitoring-data.edit');
        Route::put('/monitoring-data/{id}', [MonitoringDataController::class, 'update'])->name('monitoring-data.update');
        Route::delete('/monitoring-data/{id}', [MonitoringDataController::class, 'destroy'])->name('monitoring-data.destroy');
        Route::delete('/monitoring-data/{id}/gallery', [MonitoringDataController::class, 'deleteGalleryImage'])->name('monitoring-data.delete-gallery');
    });

    // AI Prediction Routes (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/ai-prediction', [\App\Http\Controllers\AiPredictionController::class, 'index'])->name('ai-prediction.index');
        Route::post('/ai-prediction/analyze', [\App\Http\Controllers\AiPredictionController::class, 'analyze'])->name('ai-prediction.analyze');
    });

    // INDAS Routes - Economic, Tourism & Social Analysis System
    Route::middleware(['province.filter'])->group(function () {
        Route::get('/indas', [IndasController::class, 'index'])->name('indas.index');
        Route::get('/indas/indicators', [IndasController::class, 'indicators'])->name('indas.indicators');
        Route::post('/indas/indicators', [IndasController::class, 'storeIndicator'])->name('indas.indicators.store');
        Route::put('/indas/indicators/{id}', [IndasController::class, 'updateIndicator'])->name('indas.indicators.update');
        Route::delete('/indas/indicators/{id}', [IndasController::class, 'deleteIndicator'])->name('indas.indicators.delete');
        Route::get('/indas/data-entry', [IndasController::class, 'dataEntry'])->name('indas.data-entry');
        Route::post('/indas/data', [IndasController::class, 'storeData'])->name('indas.data.store');
        Route::post('/indas/calculate', [IndasController::class, 'calculateAll'])->name('indas.calculate');
    });

    // Partai Politik Routes
    Route::resource('partai-politik', PartaiPolitikController::class);
    Route::post('/partai-politik/{partaiPolitik}/jumlah-suara', [PartaiPolitikController::class, 'storeJumlahSuara'])->name('partai-politik.jumlah-suara.store');
    Route::put('/jumlah-suara/{jumlahSuara}', [PartaiPolitikController::class, 'updateJumlahSuara'])->name('jumlah-suara.update');
    Route::delete('/jumlah-suara/{jumlahSuara}', [PartaiPolitikController::class, 'destroyJumlahSuara'])->name('jumlah-suara.destroy');

    // Pasar Saham Routes
    Route::get('/pasar-saham', [PasarSahamController::class, 'index'])->name('pasar-saham.index');
    Route::get('/pasar-saham/chart', [PasarSahamController::class, 'chart'])->name('pasar-saham.chart');
    Route::get('/pasar-saham/watchlist', [PasarSahamController::class, 'watchlist'])->name('pasar-saham.watchlist');

    // Sembako Routes
    Route::delete('sembako/bulk-destroy', [SembakoController::class, 'bulkDestroy'])->name('sembako.bulk-destroy');
    Route::get('sembako/export-csv', [SembakoController::class, 'exportCsv'])->name('sembako.export-csv');
    Route::get('sembako/download-sample', [SembakoController::class, 'downloadSample'])->name('sembako.download-sample');
    Route::post('sembako/import-csv', [SembakoController::class, 'importCsv'])->name('sembako.import-csv');
    Route::resource('sembako', SembakoController::class);

    // Categories Routes (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('categories', \App\Http\Controllers\CategoryController::class);
        Route::post('/categories/{category}/toggle-status', [\App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::delete('/categories/{category}/delete-image', [\App\Http\Controllers\CategoryController::class, 'deleteImage'])->name('categories.delete-image');

        // Sub Categories Routes
        Route::resource('sub-categories', \App\Http\Controllers\SubCategoryController::class);
        Route::post('/sub-categories/{subCategory}/toggle-status', [\App\Http\Controllers\SubCategoryController::class, 'toggleStatus'])->name('sub-categories.toggle-status');
        Route::delete('/sub-categories/{subCategory}/delete-image', [\App\Http\Controllers\SubCategoryController::class, 'deleteImage'])->name('sub-categories.delete-image');
    });

    // Event Calendar Routes (Universal for Kamtibmas, Agenda, etc.)
    Route::get('/event', [EventController::class, 'index'])->name('event.index');
    Route::post('/event', [EventController::class, 'store'])->name('event.store');
    Route::get('/event/{event}', [EventController::class, 'show'])->name('event.show');
    Route::put('/event/{event}', [EventController::class, 'update'])->name('event.update');
    Route::delete('/event/{event}', [EventController::class, 'destroy'])->name('event.destroy');
    Route::post('/event/{event}/toggle-status', [EventController::class, 'toggleStatus'])->name('event.toggle-status');
    
    // Specific route for Agenda Internal Korp Brimob POLRI
    Route::get('/agenda-internal-korp-brimob', function() {
        return redirect('/event?category=Agenda Internal Korp Brimob POLRI');
    })->name('agenda-internal-korp-brimob.index');
    
    // Legacy routes for backward compatibility
    Route::get('/kamtibmas-calendar', function() {
        return redirect('/event?event=kamtibmas');
    });
    Route::post('/kamtibmas-events', [EventController::class, 'store'])->name('kamtibmas-events.store');
    Route::get('/kamtibmas-events/{event}', [EventController::class, 'show'])->name('kamtibmas-events.show');
    Route::put('/kamtibmas-events/{event}', [EventController::class, 'update'])->name('kamtibmas-events.update');
    Route::delete('/kamtibmas-events/{event}', [EventController::class, 'destroy'])->name('kamtibmas-events.destroy');
    Route::post('/kamtibmas-events/{event}/toggle-status', [EventController::class, 'toggleStatus'])->name('kamtibmas-events.toggle-status');


    // User Management Routes (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // Settings Routes (Admin only - Fixed settings - only allow viewing and updating values)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
        Route::match(['POST', 'PUT'], '/settings/{key}', [AppSettingController::class, 'update'])->name('settings.update');
        
        // Menu Management Routes
        Route::resource('admin/menu-items', \App\Http\Controllers\Admin\MenuItemController::class, [
            'names' => [
                'index' => 'admin.menu-items.index',
                'create' => 'admin.menu-items.create',
                'store' => 'admin.menu-items.store',
                'show' => 'admin.menu-items.show',
                'edit' => 'admin.menu-items.edit',
                'update' => 'admin.menu-items.update',
                'destroy' => 'admin.menu-items.destroy',
            ]
        ]);
        Route::post('/admin/menu-items/{menuItem}/toggle-status', [\App\Http\Controllers\Admin\MenuItemController::class, 'toggleStatus'])
            ->name('admin.menu-items.toggle-status');
        Route::post('/admin/menu-items/reorder', [\App\Http\Controllers\Admin\MenuItemController::class, 'reorder'])
            ->name('admin.menu-items.reorder');
        Route::post('/admin/menu-items/{menuItem}/indent', [\App\Http\Controllers\Admin\MenuItemController::class, 'indent'])
            ->name('admin.menu-items.indent');
        Route::post('/admin/menu-items/{menuItem}/outdent', [\App\Http\Controllers\Admin\MenuItemController::class, 'outdent'])
            ->name('admin.menu-items.outdent');
        Route::post('/admin/menu-items/reset', [\App\Http\Controllers\Admin\MenuItemController::class, 'reset'])
            ->name('admin.menu-items.reset');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
