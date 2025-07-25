<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KecamatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kecamatan::with(['kabupatenKota.provinsi', 'crimeData']);
        
        if ($request->has('provinsi_id')) {
            $query->whereHas('kabupatenKota.provinsi', function ($q) use ($request) {
                $q->where('id', $request->provinsi_id);
            });
        }
        if ($request->has('kabupaten_kota_id')) {
            $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
        }
        if ($request->has('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%');
        }
        
        $data = $query->paginate(50)->withQueryString();
        
        // Calculate statistics
        $totalKecamatan = Kecamatan::count();
        $totalCrimes = \App\Models\CrimeData::count();
        $affectedKecamatan = Kecamatan::has('crimeData')->count();
        $avgCrimesPerKecamatan = $totalKecamatan > 0 ? round($totalCrimes / $totalKecamatan, 2) : 0;
        
        return Inertia::render('Kecamatan', [
            'kecamatan' => $data,
            'statistics' => [
                'total_kecamatan' => $totalKecamatan,
                'total_crimes' => $totalCrimes,
                'affected_kecamatan' => $affectedKecamatan,
                'avg_crimes_per_kecamatan' => $avgCrimesPerKecamatan,
            ],
        ]);
    }

    public function show($kode_provinsi, $kode_kabupaten_kota, $kode)
    {
        $kecamatan = Kecamatan::where('kode_provinsi', $kode_provinsi)
            ->where('kode_kabupaten_kota', $kode_kabupaten_kota)
            ->where('kode', $kode)
            ->firstOrFail();
        return Inertia::render('Kecamatan', [
            'kecamatan' => $kecamatan,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_provinsi' => 'required|string|size:2|exists:provinsi,kode',
            'kode_kabupaten_kota' => 'required|string|size:2',
            'kode' => 'required|string|size:3',
            'nama' => 'required|string',
        ]);
        $kecamatan = Kecamatan::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kecamatan berhasil ditambah',
            'data' => $kecamatan,
        ], 201);
    }

    public function update(Request $request, $kode_provinsi, $kode_kabupaten_kota, $kode)
    {
        $kecamatan = Kecamatan::where('kode_provinsi', $kode_provinsi)
            ->where('kode_kabupaten_kota', $kode_kabupaten_kota)
            ->where('kode', $kode)
            ->firstOrFail();
        $data = $request->validate([
            'nama' => 'required|string',
        ]);
        $kecamatan->update($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kecamatan berhasil diupdate',
            'data' => $kecamatan,
        ]);
    }

    public function destroy($kode_provinsi, $kode_kabupaten_kota, $kode)
    {
        $kecamatan = Kecamatan::where('kode_provinsi', $kode_provinsi)
            ->where('kode_kabupaten_kota', $kode_kabupaten_kota)
            ->where('kode', $kode)
            ->firstOrFail();
        $kecamatan->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Kecamatan berhasil dihapus',
        ], 204);
    }
}
