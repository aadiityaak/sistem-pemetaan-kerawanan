<?php

namespace App\Http\Controllers;

use App\Models\CrimeData;
use App\Models\Provinsi;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function index()
  {
    // Ambil semua data kriminal dengan relasi
    $crimeData = CrimeData::with(['provinsi', 'kabupatenKota', 'kecamatan'])->get();

    // Hitung statistik
    $totalCrimes = $crimeData->count();
    $totalProvinsi = Provinsi::count();
    $totalKabupatenKota = KabupatenKota::count();
    $totalKecamatan = Kecamatan::count();

    // Hitung berdasarkan jenis kriminal
    $crimesByType = $crimeData->groupBy('jenis_kriminal')->map(function ($crimes) {
      return $crimes->count();
    });

    // Hitung berdasarkan provinsi
    $crimesByProvinsi = $crimeData->groupBy(function ($crime) {
      return $crime->provinsi->nama ?? 'Unknown';
    })->map(function ($crimes) {
      return $crimes->count();
    });

    return Inertia::render('Dashboard', [
      'crimeData' => $crimeData,
      'statistics' => [
        'totalCrimes' => $totalCrimes,
        'totalProvinsi' => $totalProvinsi,
        'totalKabupatenKota' => $totalKabupatenKota,
        'totalKecamatan' => $totalKecamatan,
        'crimesByType' => $crimesByType,
        'crimesByProvinsi' => $crimesByProvinsi,
      ]
    ]);
  }

  public function ideologi()
  {
    return Inertia::render('Dashboard/Ideologi', [
      'title' => 'Dashboard Ideologi'
    ]);
  }

  public function politik()
  {
    return Inertia::render('Dashboard/Politik', [
      'title' => 'Dashboard Politik'
    ]);
  }

  public function ekonomi()
  {
    return Inertia::render('Dashboard/Ekonomi', [
      'title' => 'Dashboard Ekonomi'
    ]);
  }

  public function sosialBudaya()
  {
    return Inertia::render('Dashboard/SosialBudaya', [
      'title' => 'Dashboard Sosial Budaya'
    ]);
  }

  public function keamanan()
  {
    return Inertia::render('Dashboard/Keamanan', [
      'title' => 'Dashboard Keamanan'
    ]);
  }
}
