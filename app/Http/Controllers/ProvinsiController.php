<?php

namespace App\Http\Controllers;

use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProvinsiController extends Controller
{
    public function index(Request $request)
    {
        $query = Provinsi::query();

        if ($request->has('search')) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        // Pagination dengan 50 data per halaman
        $provinsiPaginated = $query->paginate(50)->withQueryString();

        // Menambahkan statistik untuk setiap provinsi
        $provinsiPaginated->getCollection()->transform(function ($provinsi) {
            $crimeCount = MonitoringData::where('provinsi_id', $provinsi->id)->count();
            $kabupatenKotaCount = KabupatenKota::where('provinsi_id', $provinsi->id)->count();
            $kecamatanCount = Kecamatan::whereHas('kabupatenKota', function ($query) use ($provinsi) {
                $query->where('provinsi_id', $provinsi->id);
            })->count();

            $provinsi->jumlah_tindakan = $crimeCount;
            $provinsi->jumlah_kabupaten_kota = $kabupatenKotaCount;
            $provinsi->jumlah_kecamatan = $kecamatanCount;
            $provinsi->crime_data = MonitoringData::where('provinsi_id', $provinsi->id)->get();

            return $provinsi;
        });

        // Hitung statistik keseluruhan
        $totalProvinsi = Provinsi::count();
        $totalCrimes = MonitoringData::count();
        $affectedProvinsi = Provinsi::whereHas('monitoringData')->count();
        $avgCrimesPerProvinsi = $totalProvinsi > 0 ? number_format($totalCrimes / $totalProvinsi, 1) : '0';

        $statistics = [
            'total_provinsi' => $totalProvinsi,
            'total_crimes' => $totalCrimes,
            'affected_provinsi' => $affectedProvinsi,
            'avg_crimes_per_provinsi' => $avgCrimesPerProvinsi,
        ];

        return Inertia::render('Provinsi', [
            'provinsi' => $provinsiPaginated,
            'statistics' => $statistics,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function show($id)
    {
        $provinsi = Provinsi::findOrFail($id);

        // Hitung statistik
        $crimeCount = MonitoringData::where('provinsi_id', $provinsi->id)->count();
        $kabupatenKotaCount = KabupatenKota::where('provinsi_id', $provinsi->id)->count();
        $kecamatanCount = Kecamatan::whereHas('kabupatenKota', function ($query) use ($provinsi) {
            $query->where('provinsi_id', $provinsi->id);
        })->count();

        $provinsi->jumlah_tindakan = $crimeCount;
        $provinsi->jumlah_kabupaten_kota = $kabupatenKotaCount;
        $provinsi->jumlah_kecamatan = $kecamatanCount;

        return response()->json([
            'status' => 'success',
            'message' => 'Detail provinsi',
            'data' => $provinsi,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string',
        ]);
        $provinsi = Provinsi::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Provinsi berhasil ditambah',
            'data' => $provinsi,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $provinsi = Provinsi::findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string',
        ]);
        $provinsi->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Provinsi berhasil diupdate',
            'data' => $provinsi,
        ]);
    }

    public function destroy($id)
    {
        $provinsi = Provinsi::findOrFail($id);
        $provinsi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Provinsi berhasil dihapus',
        ], 204);
    }
}
