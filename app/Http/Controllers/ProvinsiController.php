<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use Illuminate\Http\Request;

class ProvinsiController extends Controller
{
    public function index()
    {
        return Provinsi::all();
    }

    public function show($kode)
    {
        return Provinsi::findOrFail($kode);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|size:2|unique:provinsi,kode',
            'nama' => 'required|string',
        ]);
        $provinsi = Provinsi::create($data);
        return response()->json($provinsi, 201);
    }

    public function update(Request $request, $kode)
    {
        $provinsi = Provinsi::findOrFail($kode);
        $data = $request->validate([
            'nama' => 'required|string',
        ]);
        $provinsi->update($data);
        return response()->json($provinsi);
    }

    public function destroy($kode)
    {
        $provinsi = Provinsi::findOrFail($kode);
        $provinsi->delete();
        return response()->json(null, 204);
    }
} 