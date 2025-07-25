<?php

namespace App\Http\Controllers;

use App\Models\KabupatenKota;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KabupatenKotaController extends Controller
{
    public function index(Request $request)
    {
        $query = KabupatenKota::query();
        if ($request->has('kode_provinsi')) {
            $query->where('kode_provinsi', $request->kode_provinsi);
        }
        if ($request->has('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%');
        }
        $data = $query->get();
        return Inertia::render('KabupatenKota', [
            'kabupatenKota' => $data,
        ]);
    }

    public function show($kode_provinsi, $kode)
    {
        $kabupatenKota = KabupatenKota::where('kode_provinsi', $kode_provinsi)
            ->where('kode', $kode)
            ->firstOrFail();
        return Inertia::render('KabupatenKota', [
            'kabupatenKota' => $kabupatenKota,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_provinsi' => 'required|string|size:2|exists:provinsi,kode',
            'kode' => 'required|string|size:2',
            'nama' => 'required|string',
        ]);
        $kabupatenKota = KabupatenKota::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kabupaten/kota berhasil ditambah',
            'data' => $kabupatenKota,
        ], 201);
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
        return response()->json([
            'status' => 'success',
            'message' => 'Kabupaten/kota berhasil diupdate',
            'data' => $kabupatenKota,
        ]);
    }

    public function destroy($kode_provinsi, $kode)
    {
        $kabupatenKota = KabupatenKota::where('kode_provinsi', $kode_provinsi)
            ->where('kode', $kode)
            ->firstOrFail();
        $kabupatenKota->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Kabupaten/kota berhasil dihapus',
        ], 204);
    }
} 