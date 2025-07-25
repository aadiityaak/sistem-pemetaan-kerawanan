<?php

namespace App\Http\Controllers;

use App\Models\CrimeData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CrimeDataController extends Controller
{
    public function index(Request $request)
    {
        $query = CrimeData::query();
        if ($request->has('kode_provinsi')) {
            $query->where('kode_provinsi', $request->kode_provinsi);
        }
        if ($request->has('kode_kabupaten_kota')) {
            $query->where('kode_kabupaten_kota', $request->kode_kabupaten_kota);
        }
        if ($request->has('kode_kecamatan')) {
            $query->where('kode_kecamatan', $request->kode_kecamatan);
        }
        if ($request->has('jenis_kriminal')) {
            $query->where('jenis_kriminal', $request->jenis_kriminal);
        }
        $data = $query->paginate(50)->withQueryString();
        return Inertia::render('CrimeData', [
            'crimeData' => $data,
        ]);
    }

    public function show($id)
    {
        $crime = CrimeData::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Detail data kriminal',
            'data' => $crime,
        ]);
    }

    public function create()
    {
        return Inertia::render('CrimeData/Create');
    }

    public function edit($id)
    {
        $crime = CrimeData::findOrFail($id);
        return Inertia::render('CrimeDataForm', [
            'mode' => 'edit',
            'crime' => $crime,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_provinsi' => 'required|string|size:2|exists:provinsi,kode',
            'kode_kabupaten_kota' => 'required|string|size:2',
            'kode_kecamatan' => 'required|string|size:3',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'jenis_kriminal' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);
        $crime = CrimeData::create($data);
        return redirect()->route('crime_data.index')->with('success', 'Data kriminal berhasil ditambah');
    }

    public function update(Request $request, $id)
    {
        $crime = CrimeData::findOrFail($id);
        $data = $request->validate([
            'kode_provinsi' => 'sometimes|required|string|size:2|exists:provinsi,kode',
            'kode_kabupaten_kota' => 'sometimes|required|string|size:2',
            'kode_kecamatan' => 'sometimes|required|string|size:3',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'jenis_kriminal' => 'sometimes|required|string',
            'deskripsi' => 'nullable|string',
        ]);
        $crime->update($data);
        return redirect()->route('crime_data.index')->with('success', 'Data kriminal berhasil diupdate');
    }

    public function destroy($id)
    {
        $crime = CrimeData::findOrFail($id);
        $crime->delete();
        return redirect()->route('crime_data.index')->with('success', 'Data kriminal berhasil dihapus');
    }
}
