<?php

namespace App\Http\Controllers;

use App\Models\KabupatenKota;
use App\Models\MonitoringData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KabupatenKotaController extends Controller
{
    public function index(Request $request)
    {
        $query = KabupatenKota::with(['provinsi']);

        if ($request->has('provinsi_id')) {
            $query->where('provinsi_id', $request->provinsi_id);
        }
        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // Pagination dengan 50 data per halaman
        $kabupatenKotaPaginated = $query->paginate(50)->withQueryString();

        // Menambahkan jumlah tindakan kriminal untuk setiap kabupaten/kota
        $kabupatenKotaPaginated->getCollection()->transform(function ($kabupatenKota) {
            $crimeCount = MonitoringData::where('kabupaten_kota_id', $kabupatenKota->id)->count();
            $kecamatanCount = \App\Models\Kecamatan::where('kabupaten_kota_id', $kabupatenKota->id)->count();

            $kabupatenKota->jumlah_tindakan = $crimeCount;
            $kabupatenKota->jumlah_kecamatan = $kecamatanCount;
            $kabupatenKota->crime_data = MonitoringData::where('kabupaten_kota_id', $kabupatenKota->id)->get();

            return $kabupatenKota;
        });

        // Hitung statistik keseluruhan
        $totalKabKota = KabupatenKota::count();
        $totalCrimes = MonitoringData::count();
        $affectedKabKota = KabupatenKota::whereHas('monitoringData')->count();
        $avgCrimesPerKabKota = $totalKabKota > 0 ? number_format($totalCrimes / $totalKabKota, 1) : '0';

        $statistics = [
            'total_kabkota' => $totalKabKota,
            'total_crimes' => $totalCrimes,
            'affected_kabkota' => $affectedKabKota,
            'avg_crimes_per_kabkota' => $avgCrimesPerKabKota,
        ];

        return Inertia::render('KabupatenKota', [
            'kabupatenKota' => $kabupatenKotaPaginated,
            'statistics' => $statistics,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function show($id)
    {
        $kabupatenKota = KabupatenKota::with(['provinsi'])->findOrFail($id);
        $crimeCount = MonitoringData::where('kabupaten_kota_id', $kabupatenKota->id)->count();
        $kabupatenKota->jumlah_tindakan = $crimeCount;

        return response()->json([
            'status' => 'success',
            'message' => 'Detail kabupaten/kota',
            'data' => $kabupatenKota,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provinsi_id' => 'required|integer|exists:provinsi,id',
            'nama' => 'required|string',
        ]);
        $kabupatenKota = KabupatenKota::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kabupaten/kota berhasil ditambah',
            'data' => $kabupatenKota,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $kabupatenKota = KabupatenKota::findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string',
            'provinsi_id' => 'sometimes|required|integer|exists:provinsi,id',
        ]);
        $kabupatenKota->update($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kabupaten/kota berhasil diupdate',
            'data' => $kabupatenKota,
        ]);
    }

    public function destroy($id)
    {
        $kabupatenKota = KabupatenKota::findOrFail($id);
        $kabupatenKota->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Kabupaten/kota berhasil dihapus',
        ], 204);
    }
}
