<?php

namespace App\Http\Controllers;

use App\Models\KabupatenKota;
use Illuminate\Http\Request;

class KabupatenKotaController extends Controller
{
    public function index()
    {
        return KabupatenKota::all();
    }

    public function show($kode_provinsi, $kode)
    {
        return KabupatenKota::where('kode_provinsi', $kode_provinsi)
            ->where('kode', $kode)
            ->firstOrFail();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_provinsi' => 'required|string|size:2|exists:provinsi,kode',
            'kode' => 'required|string|size:2',
            'nama' => 'required|string',
        ]);
        $kabupatenKota = KabupatenKota::create($data);
        return response()->json($kabupatenKota, 201);
    }

    public function update(Request $request, $kode_provinsi, $kode)
    {
        $kabupatenKota = KabupatenKota::where('kode_provinsi', $kode_provinsi)
            ->where('kode', $kode)
            ->firstOrFail();
        $data = $request->validate([
            'nama' => 'required|string',
        ]);
        $kabupatenKota->update($data);
        return response()->json($kabupatenKota);
    }

    public function destroy($kode_provinsi, $kode)
    {
        $kabupatenKota = KabupatenKota::where('kode_provinsi', $kode_provinsi)
            ->where('kode', $kode)
            ->firstOrFail();
        $kabupatenKota->delete();
        return response()->json(null, 204);
    }
} 