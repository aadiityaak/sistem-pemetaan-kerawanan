<?php

namespace App\Http\Controllers;

use App\Models\Sembako;
use App\Models\KabupatenKota;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class SembakoController extends Controller
{
    public function index(Request $request)
    {
        $query = Sembako::with(['kabupatenKota.provinsi']);

        // Filter by commodity name
        if ($request->filled('nama_komoditas')) {
            $query->where('nama_komoditas', 'like', '%' . $request->nama_komoditas . '%');
        }

        // Filter by region
        if ($request->filled('kabupaten_kota_id')) {
            $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
        }

        // Filter by province via relationship
        if ($request->filled('provinsi_id')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('provinsi_id', $request->provinsi_id);
            });
        }

        // Filter by date range
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_pencatatan', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->where('tanggal_pencatatan', '<=', $request->tanggal_selesai);
        }

        // Filter by price range
        if ($request->filled('harga_min')) {
            $query->where('harga', '>=', $request->harga_min);
        }

        if ($request->filled('harga_max')) {
            $query->where('harga', '<=', $request->harga_max);
        }

        // Search in notes/keterangan
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_komoditas', 'like', '%' . $searchTerm . '%')
                  ->orWhere('keterangan', 'like', '%' . $searchTerm . '%')
                  ->orWhere('satuan', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sort options
        $sortField = $request->get('sort_field', 'tanggal_pencatatan');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Validate sort fields for security
        $allowedSortFields = ['tanggal_pencatatan', 'nama_komoditas', 'harga', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'tanggal_pencatatan';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Secondary sort for consistency
        if ($sortField !== 'nama_komoditas') {
            $query->orderBy('nama_komoditas');
        }

        $sembako = $query->paginate(20)->withQueryString();

        $kabupatenKota = KabupatenKota::with('provinsi')
            ->orderBy('nama')
            ->get();

        // Get unique commodity names for filter dropdown
        $commodities = Sembako::select('nama_komoditas')
            ->distinct()
            ->orderBy('nama_komoditas')
            ->pluck('nama_komoditas');

        // Get provinces for filter dropdown
        $provinces = \App\Models\Provinsi::orderBy('nama')->get();

        return Inertia::render('Sembako/Index', [
            'sembako' => $sembako,
            'kabupatenKota' => $kabupatenKota,
            'commodities' => $commodities,
            'provinces' => $provinces,
            'filters' => $request->only([
                'nama_komoditas', 
                'kabupaten_kota_id', 
                'provinsi_id',
                'tanggal_mulai', 
                'tanggal_selesai', 
                'harga_min', 
                'harga_max', 
                'search',
                'sort_field',
                'sort_direction'
            ]),
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

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:sembako,id'
        ]);

        $count = Sembako::whereIn('id', $validated['ids'])->count();
        Sembako::whereIn('id', $validated['ids'])->delete();

        return Redirect::route('sembako.index')->with('success', "Berhasil menghapus {$count} data sembako.");
    }
}
