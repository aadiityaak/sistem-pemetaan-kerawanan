<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/provinsi', [ProvinsiController::class, 'index'])->name('provinsi.index');
    Route::get('/provinsi/{kode}', [ProvinsiController::class, 'show'])->name('provinsi.show');

    Route::get('/kabupaten-kota', [KabupatenKotaController::class, 'index'])->name('kabupaten_kota.index');
    Route::get('/kabupaten-kota/{kode_provinsi}/{kode}', [KabupatenKotaController::class, 'show'])->name('kabupaten_kota.show');

    Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');
    Route::get('/kecamatan/{kode_provinsi}/{kode_kabupaten_kota}/{kode}', [KecamatanController::class, 'show'])->name('kecamatan.show');

    Route::get('/crime-data', [\App\Http\Controllers\CrimeDataController::class, 'index'])->name('crime_data.index');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
