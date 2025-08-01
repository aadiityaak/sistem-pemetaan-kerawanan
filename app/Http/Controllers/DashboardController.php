<?php

namespace App\Http\Controllers;

use App\Models\MonitoringData;
use App\Models\Category;
use App\Models\Provinsi;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function index()
  {
    // Ambil semua data monitoring dengan relasi
    $monitoringData = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])->get();

    // Hitung statistik
    $totalData = $monitoringData->count();
    $totalProvinsi = Provinsi::count();
    $totalKabupatenKota = KabupatenKota::count();
    $totalKecamatan = Kecamatan::count();
    $totalCategories = Category::count();

    // Hitung berdasarkan kategori
    $dataByCategory = $monitoringData->groupBy('category.name')->map(function ($data) {
      return $data->count();
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

    return Inertia::render('Dashboard', [
      'monitoringData' => $monitoringData,
      'statistics' => [
        'totalData' => $totalData,
        'totalProvinsi' => $totalProvinsi,
        'totalKabupatenKota' => $totalKabupatenKota,
        'totalKecamatan' => $totalKecamatan,
        'totalCategories' => $totalCategories,
        'dataByCategory' => $dataByCategory,
        'dataByProvinsi' => $dataByProvinsi,
        'dataBySeverity' => $dataBySeverity,
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
