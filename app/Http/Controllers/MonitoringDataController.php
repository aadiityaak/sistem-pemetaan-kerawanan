<?php

namespace App\Http\Controllers;

use App\Models\MonitoringData;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Provinsi;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringDataController extends Controller
{
  public function index(Request $request)
  {
    $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory']);

    // Filter berdasarkan kategori
    if ($request->has('category_id')) {
      $query->where('category_id', $request->category_id);
    }

    // Filter berdasarkan sub kategori  
    if ($request->has('sub_category_id')) {
      $query->where('sub_category_id', $request->sub_category_id);
    }

    // Filter berdasarkan wilayah
    if ($request->has('provinsi_id')) {
      $query->where('provinsi_id', $request->provinsi_id);
    }
    if ($request->has('kabupaten_kota_id')) {
      $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
    }
    if ($request->has('kecamatan_id')) {
      $query->where('kecamatan_id', $request->kecamatan_id);
    }

    // Filter berdasarkan severity
    if ($request->has('severity_level')) {
      $query->where('severity_level', $request->severity_level);
    }

    // Filter berdasarkan status
    if ($request->has('status')) {
      $query->where('status', $request->status);
    }

    // Search
    if ($request->has('q')) {
      $query->where(function ($q) use ($request) {
        $q->where('title', 'like', '%' . $request->q . '%')
          ->orWhere('description', 'like', '%' . $request->q . '%');
      });
    }

    $monitoringData = $query->latest()->paginate(50);

    // Statistik
    $totalData = MonitoringData::count();
    $totalProvinsi = MonitoringData::distinct('provinsi_id')->count();
    $totalKabupatenKota = MonitoringData::distinct('kabupaten_kota_id')->count();
    $totalKecamatan = MonitoringData::distinct('kecamatan_id')->count();
    $totalCategories = MonitoringData::distinct('category_id')->count();

    // Data untuk dropdown filter
    $categories = Category::active()->ordered()->get();
    $provinsi = Provinsi::orderBy('nama')->get();

    return Inertia::render('MonitoringData/Index', [
      'monitoringData' => $monitoringData,
      'statistics' => [
        'totalData' => $totalData,
        'totalProvinsi' => $totalProvinsi,
        'totalKabupatenKota' => $totalKabupatenKota,
        'totalKecamatan' => $totalKecamatan,
        'totalCategories' => $totalCategories,
      ],
      'categories' => $categories,
      'provinsi' => $provinsi,
      'filters' => $request->only(['category_id', 'sub_category_id', 'provinsi_id', 'kabupaten_kota_id', 'kecamatan_id', 'severity_level', 'status', 'q']),
    ]);
  }

  public function create()
  {
    $categories = Category::active()->ordered()->with('subCategories')->get();
    $provinsi = Provinsi::orderBy('nama')->get();

    return Inertia::render('MonitoringData/Create', [
      'categories' => $categories,
      'provinsi' => $provinsi,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'provinsi_id' => 'required|exists:provinsi,id',
      'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
      'kecamatan_id' => 'required|exists:kecamatan,id',
      'category_id' => 'required|exists:categories,id',
      'sub_category_id' => 'required|exists:sub_categories,id',
      'latitude' => 'required|numeric|between:-90,90',
      'longitude' => 'required|numeric|between:-180,180',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'jumlah_terdampak' => 'nullable|integer|min:0',
      'severity_level' => 'required|in:low,medium,high,critical',
      'status' => 'required|in:active,resolved,monitoring,archived',
      'incident_date' => 'nullable|date',
      'source' => 'nullable|string|max:255',
      'additional_data' => 'nullable|array',
    ]);

    MonitoringData::create($validated);

    return redirect()->route('monitoring_data.index')
      ->with('success', 'Data monitoring berhasil ditambahkan.');
  }

  public function edit($id)
  {
    $monitoringData = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])->findOrFail($id);
    $categories = Category::active()->ordered()->with('subCategories')->get();
    $provinsi = Provinsi::orderBy('nama')->get();
    $kabupatenKota = KabupatenKota::where('provinsi_id', $monitoringData->provinsi_id)->orderBy('nama')->get();
    $kecamatan = Kecamatan::where('kabupaten_kota_id', $monitoringData->kabupaten_kota_id)->orderBy('nama')->get();

    return Inertia::render('MonitoringData/Edit', [
      'monitoringData' => $monitoringData,
      'categories' => $categories,
      'provinsi' => $provinsi,
      'kabupatenKota' => $kabupatenKota,
      'kecamatan' => $kecamatan,
    ]);
  }

  public function update(Request $request, $id)
  {
    $monitoringData = MonitoringData::findOrFail($id);

    $validated = $request->validate([
      'provinsi_id' => 'required|exists:provinsi,id',
      'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
      'kecamatan_id' => 'required|exists:kecamatan,id',
      'category_id' => 'required|exists:categories,id',
      'sub_category_id' => 'required|exists:sub_categories,id',
      'latitude' => 'required|numeric|between:-90,90',
      'longitude' => 'required|numeric|between:-180,180',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'jumlah_terdampak' => 'nullable|integer|min:0',
      'severity_level' => 'required|in:low,medium,high,critical',
      'status' => 'required|in:active,resolved,monitoring,archived',
      'incident_date' => 'nullable|date',
      'source' => 'nullable|string|max:255',
      'additional_data' => 'nullable|array',
    ]);

    $monitoringData->update($validated);

    return redirect()->route('monitoring_data.index')
      ->with('success', 'Data monitoring berhasil diperbarui.');
  }

  public function destroy($id)
  {
    $monitoringData = MonitoringData::findOrFail($id);
    $monitoringData->delete();

    return redirect()->route('monitoring_data.index')
      ->with('success', 'Data monitoring berhasil dihapus.');
  }
}
