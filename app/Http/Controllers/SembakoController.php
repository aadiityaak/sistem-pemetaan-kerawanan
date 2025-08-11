<?php

namespace App\Http\Controllers;

use App\Models\Sembako;
use App\Models\KabupatenKota;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class SembakoController extends Controller
{
    public function index()
    {
        $sembako = Sembako::with(['kabupatenKota.provinsi'])
            ->orderBy('tanggal_pencatatan', 'desc')
            ->orderBy('nama_komoditas')
            ->paginate(20);

        $kabupatenKota = KabupatenKota::with('provinsi')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Sembako/Index', [
            'sembako' => $sembako,
            'kabupatenKota' => $kabupatenKota,
        ]);
    }

    public function create()
    {
        $kabupatenKota = KabupatenKota::with('provinsi')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Sembako/Create', [
            'kabupatenKota' => $kabupatenKota,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_komoditas' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'tanggal_pencatatan' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        Sembako::create($validated);

        return Redirect::route('sembako.index')->with('success', 'Data sembako berhasil ditambahkan.');
    }

    public function show(Sembako $sembako)
    {
        $sembako->load(['kabupatenKota.provinsi']);

        return Inertia::render('Sembako/Show', [
            'sembako' => $sembako,
        ]);
    }

    public function edit(Sembako $sembako)
    {
        $sembako->load(['kabupatenKota.provinsi']);
        
        $kabupatenKota = KabupatenKota::with('provinsi')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Sembako/Edit', [
            'sembako' => $sembako,
            'kabupatenKota' => $kabupatenKota,
        ]);
    }

    public function update(Request $request, Sembako $sembako)
    {
        $validated = $request->validate([
            'nama_komoditas' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'tanggal_pencatatan' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $sembako->update($validated);

        return Redirect::route('sembako.index')->with('success', 'Data sembako berhasil diperbarui.');
    }

    public function destroy(Sembako $sembako)
    {
        $sembako->delete();

        return Redirect::route('sembako.index')->with('success', 'Data sembako berhasil dihapus.');
    }
}
