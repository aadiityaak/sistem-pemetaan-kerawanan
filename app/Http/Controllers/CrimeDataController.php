<?php

namespace App\Http\Controllers;

use App\Models\CrimeData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CrimeDataController extends Controller
{
    public function index(Request $request)
    {
        $query = CrimeData::with(['provinsi', 'kabupatenKota', 'kecamatan']);

        if ($request->has('provinsi_id')) {
            $query->where('provinsi_id', $request->provinsi_id);
        }
        if ($request->has('kabupaten_kota_id')) {
            $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
        }
        if ($request->has('kecamatan_id')) {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }
        if ($request->has('jenis_kriminal')) {
            $query->where('jenis_kriminal', 'like', '%' . $request->jenis_kriminal . '%');
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
        return Inertia::render('CrimeDataForm');
    }

    public function edit($id)
    {
        $crime = CrimeData::with(['provinsi', 'kabupatenKota', 'kecamatan'])->findOrFail($id);
        return Inertia::render('CrimeDataForm', [
            'crime' => $crime,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provinsi_id' => 'required|integer|exists:provinsi,id',
            'kabupaten_kota_id' => 'required|integer|exists:kabupaten_kota,id',
            'kecamatan_id' => 'required|integer|exists:kecamatan,id',
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
            'provinsi_id' => 'sometimes|required|integer|exists:provinsi,id',
            'kabupaten_kota_id' => 'sometimes|required|integer|exists:kabupaten_kota,id',
            'kecamatan_id' => 'sometimes|required|integer|exists:kecamatan,id',
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
