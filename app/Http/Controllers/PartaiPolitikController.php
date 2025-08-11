<?php

namespace App\Http\Controllers;

use App\Models\PartaiPolitik;
use App\Models\JumlahSuara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PartaiPolitikController extends Controller
{
    public function index(Request $request)
    {
        $query = PartaiPolitik::with(['jumlahSuara' => function($query) {
            $query->orderBy('tahun_pemilu', 'desc');
        }]);

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('nama_partai', 'like', '%' . $request->search . '%')
                  ->orWhere('singkatan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status_filter') && $request->status_filter !== '') {
            $query->where('status_aktif', $request->status_filter === 'aktif');
        }

        $partaiPolitik = $query->orderBy('nomor_urut')->paginate(15);

        return Inertia::render('PartaiPolitik/Index', [
            'partaiPolitik' => $partaiPolitik,
            'filters' => $request->only(['search', 'status_filter'])
        ]);
    }

    public function create()
    {
        return Inertia::render('PartaiPolitik/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_partai' => 'required|string|max:100',
            'singkatan' => 'required|string|max:50',
            'nomor_urut' => 'required|integer|unique:partai_politik,nomor_urut',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nama_ketua' => 'nullable|string|max:100',
            'foto_ketua' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_aktif' => 'sometimes|boolean'
        ]);

        // Handle checkbox default value
        if (!isset($validated['status_aktif'])) {
            $validated['status_aktif'] = false;
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('partai-logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        if ($request->hasFile('foto_ketua')) {
            $fotoKetuaPath = $request->file('foto_ketua')->store('ketua-fotos', 'public');
            $validated['foto_ketua'] = $fotoKetuaPath;
        }

        unset($validated['logo']);
        PartaiPolitik::create($validated);

        return redirect()->route('partai-politik.index')
            ->with('success', 'Partai politik berhasil ditambahkan.');
    }

    public function show(PartaiPolitik $partaiPolitik)
    {
        $partaiPolitik->load(['jumlahSuara' => function($query) {
            $query->orderBy('tahun_pemilu', 'desc');
        }]);

        return Inertia::render('PartaiPolitik/Show', [
            'partaiPolitik' => $partaiPolitik
        ]);
    }

    public function edit(PartaiPolitik $partaiPolitik)
    {
        return Inertia::render('PartaiPolitik/Edit', [
            'partaiPolitik' => $partaiPolitik
        ]);
    }

    public function update(Request $request, PartaiPolitik $partaiPolitik)
    {
        $validated = $request->validate([
            'nama_partai' => 'required|string|max:100',
            'singkatan' => 'required|string|max:50',
            'nomor_urut' => 'required|integer|unique:partai_politik,nomor_urut,' . $partaiPolitik->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nama_ketua' => 'nullable|string|max:100',
            'foto_ketua' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_aktif' => 'sometimes|boolean'
        ]);

        // Handle checkbox default value
        if (!isset($validated['status_aktif'])) {
            $validated['status_aktif'] = false;
        }

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($partaiPolitik->logo_path) {
                Storage::delete('public/' . $partaiPolitik->logo_path);
            }
            
            $logoPath = $request->file('logo')->store('partai-logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        if ($request->hasFile('foto_ketua')) {
            // Delete old chairman photo if exists
            if ($partaiPolitik->foto_ketua) {
                Storage::delete('public/' . $partaiPolitik->foto_ketua);
            }
            
            $fotoKetuaPath = $request->file('foto_ketua')->store('ketua-fotos', 'public');
            $validated['foto_ketua'] = $fotoKetuaPath;
        }

        unset($validated['logo']);
        $partaiPolitik->update($validated);

        return redirect()->route('partai-politik.index')
            ->with('success', 'Partai politik berhasil diperbarui.');
    }

    public function destroy(PartaiPolitik $partaiPolitik)
    {
        // Delete logo file if exists
        if ($partaiPolitik->logo_path) {
            Storage::delete('public/' . $partaiPolitik->logo_path);
        }

        // Delete chairman photo file if exists
        if ($partaiPolitik->foto_ketua) {
            Storage::delete('public/' . $partaiPolitik->foto_ketua);
        }

        $partaiPolitik->delete();

        return redirect()->route('partai-politik.index')
            ->with('success', 'Partai politik berhasil dihapus.');
    }

    public function storeJumlahSuara(Request $request, PartaiPolitik $partaiPolitik)
    {
        $validated = $request->validate([
            'tahun_pemilu' => 'required|integer|digits:4',
            'jumlah_suara' => 'required|integer|min:0'
        ]);

        $validated['partai_id'] = $partaiPolitik->id;

        JumlahSuara::updateOrCreate(
            ['partai_id' => $partaiPolitik->id, 'tahun_pemilu' => $validated['tahun_pemilu']],
            $validated
        );

        return redirect()->back()
            ->with('success', 'Data jumlah suara berhasil disimpan.');
    }

    public function updateJumlahSuara(Request $request, JumlahSuara $jumlahSuara)
    {
        $validated = $request->validate([
            'tahun_pemilu' => 'required|integer|digits:4',
            'jumlah_suara' => 'required|integer|min:0'
        ]);

        $jumlahSuara->update($validated);

        return redirect()->back()
            ->with('success', 'Data jumlah suara berhasil diperbarui.');
    }

    public function destroyJumlahSuara(JumlahSuara $jumlahSuara)
    {
        $jumlahSuara->delete();

        return redirect()->back()
            ->with('success', 'Data jumlah suara berhasil dihapus.');
    }
}
