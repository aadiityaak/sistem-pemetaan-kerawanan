<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
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
        $data = $query->get();
        return Inertia::render('Provinsi', [
            'provinsi' => $data,
        ]);
    }

    public function show($kode)
    {
        $provinsi = Provinsi::findOrFail($kode);
        return Inertia::render('Provinsi', [
            'provinsi' => $provinsi,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|size:2|unique:provinsi,kode',
            'nama' => 'required|string',
        ]);
        $provinsi = Provinsi::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Provinsi berhasil ditambah',
            'data' => $provinsi,
        ], 201);
    }

    public function update(Request $request, $kode)
    {
        $provinsi = Provinsi::findOrFail($kode);
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

    public function destroy($kode)
    {
        $provinsi = Provinsi::findOrFail($kode);
        $provinsi->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Provinsi berhasil dihapus',
        ], 204);
    }
} 