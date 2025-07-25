<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\KabupatenKotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\CrimeDataController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/provinsi', function () {
    return \App\Models\Provinsi::select('kode as id', 'nama')->get();
});

Route::get('/api/kabupaten-kota/{provinsi_kode}', function ($provinsi_kode) {
    return \App\Models\KabupatenKota::where('kode_provinsi', $provinsi_kode)
        ->select('kode as id', 'nama', 'kode_provinsi')
        ->get();
});

Route::get('/api/kecamatan/{provinsi_kode}/{kabupaten_kota_kode}', function ($provinsi_kode, $kabupaten_kota_kode) {
    return \App\Models\Kecamatan::where('kode_provinsi', $provinsi_kode)
        ->where('kode_kabupaten_kota', $kabupaten_kota_kode)
        ->select('kode as id', 'nama', 'kode_provinsi', 'kode_kabupaten_kota')
        ->get();
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

    Route::get('/crime-data', [CrimeDataController::class, 'index'])->name('crime_data.index');
    Route::get('/crime-data/create', [CrimeDataController::class, 'create'])->name('crime_data.create');
    Route::post('/crime-data', [CrimeDataController::class, 'store'])->name('crime_data.store');
    Route::get('/crime-data/{id}/edit', [CrimeDataController::class, 'edit'])->name('crime_data.edit');
    Route::put('/crime-data/{id}', [CrimeDataController::class, 'update'])->name('crime_data.update');
    Route::delete('/crime-data/{id}', [CrimeDataController::class, 'destroy'])->name('crime_data.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
