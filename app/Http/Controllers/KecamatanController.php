<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KecamatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kecamatan::query();
        if ($request->has('kode_provinsi')) {
            $query->where('kode_provinsi', $request->kode_provinsi);
        }
        if ($request->has('kode_kabupaten_kota')) {
            $query->where('kode_kabupaten_kota', $request->kode_kabupaten_kota);
        }
        if ($request->has('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%');
        }
        $data = $query->get();
        return Inertia::render('Kecamatan', [
            'kecamatan' => $data,
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