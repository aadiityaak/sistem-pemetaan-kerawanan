<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    public function index()
    {
        return Kecamatan::all();
    }

    public function show($kode_provinsi, $kode_kabupaten_kota, $kode)
    {
        return Kecamatan::where('kode_provinsi', $kode_provinsi)
            ->where('kode_kabupaten_kota', $kode_kabupaten_kota)
            ->where('kode', $kode)
            ->firstOrFail();
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
        return response()->json($kecamatan, 201);
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
        return response()->json($kecamatan);
    }

    public function destroy($kode_provinsi, $kode_kabupaten_kota, $kode)
    {
        $kecamatan = Kecamatan::where('kode_provinsi', $kode_provinsi)
            ->where('kode_kabupaten_kota', $kode_kabupaten_kota)
            ->where('kode', $kode)
            ->firstOrFail();
        $kecamatan->delete();
        return response()->json(null, 204);
    }
} 