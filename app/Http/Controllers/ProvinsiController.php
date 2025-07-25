<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\CrimeData;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;

class ProvinsiController extends Controller
{
    public function index(Request $request)
    {
        $query = Provinsi::query();
        
        if ($request->has('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%');
        }
        
        // Pagination dengan 50 data per halaman
        $provinsiPaginated = $query->paginate(50)->withQueryString();
        
        // Menambahkan statistik untuk setiap provinsi
        $provinsiPaginated->getCollection()->transform(function ($provinsi) {
            $crimeCount = CrimeData::where('provinsi_id', $provinsi->id)->count();
            $kabupatenKotaCount = KabupatenKota::where('provinsi_id', $provinsi->id)->count();
            $kecamatanCount = Kecamatan::whereHas('kabupatenKota', function($query) use ($provinsi) {
                $query->where('provinsi_id', $provinsi->id);
            })->count();
            
            $provinsi->jumlah_tindakan = $crimeCount;
            $provinsi->jumlah_kabupaten_kota = $kabupatenKotaCount;
            $provinsi->jumlah_kecamatan = $kecamatanCount;
            
            return $provinsi;
        });
        
        return Inertia::render('Provinsi', [
            'provinsi' => $provinsiPaginated,
        ]);
    }

    public function show($id)
    {
        $provinsi = Provinsi::findOrFail($id);
        
        // Hitung statistik
        $crimeCount = CrimeData::where('provinsi_id', $provinsi->id)->count();
        $kabupatenKotaCount = KabupatenKota::where('provinsi_id', $provinsi->id)->count();
        $kecamatanCount = Kecamatan::whereHas('kabupatenKota', function($query) use ($provinsi) {
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