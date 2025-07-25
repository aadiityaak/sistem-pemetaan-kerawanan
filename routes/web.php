<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\CrimeDataController;
use App\Http\Controllers\DashboardController;

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

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/provinsi', [ProvinsiController::class, 'index'])->name('provinsi.index');
    Route::get('/provinsi/{id}', [ProvinsiController::class, 'show'])->name('provinsi.show');

    Route::get('/kabupaten-kota', [KabupatenKotaController::class, 'index'])->name('kabupaten_kota.index');
    Route::get('/kabupaten-kota/{id}', [KabupatenKotaController::class, 'show'])->name('kabupaten_kota.show');

    Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');
    Route::get('/kecamatan/{id}', [KecamatanController::class, 'show'])->name('kecamatan.show');

    Route::get('/crime-data', [CrimeDataController::class, 'index'])->name('crime_data.index');
    Route::get('/crime-data/create', [CrimeDataController::class, 'create'])->name('crime_data.create');
    Route::post('/crime-data', [CrimeDataController::class, 'store'])->name('crime_data.store');
    Route::get('/crime-data/{id}/edit', [CrimeDataController::class, 'edit'])->name('crime_data.edit');
    Route::put('/crime-data/{id}', [CrimeDataController::class, 'update'])->name('crime_data.update');
    Route::delete('/crime-data/{id}', [CrimeDataController::class, 'destroy'])->name('crime_data.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
