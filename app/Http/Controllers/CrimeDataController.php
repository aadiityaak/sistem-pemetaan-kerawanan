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
        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('jenis_kriminal', 'like', '%' . $request->q . '%')
                    ->orWhere('deskripsi', 'like', '%' . $request->q . '%')
                    ->orWhereHas('provinsi', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', '%' . $request->q . '%');
                    })
                    ->orWhereHas('kabupatenKota', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', '%' . $request->q . '%');
                    })
                    ->orWhereHas('kecamatan', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', '%' . $request->q . '%');
                    });
            });
        }

        $data = $query->latest()->paginate(50)->withQueryString();

        // Calculate statistics
        $totalCrimes = CrimeData::count();
        $totalProvinsi = \App\Models\Provinsi::has('crimeData')->count();
        $totalKabupatenKota = \App\Models\KabupatenKota::has('crimeData')->count();
        $totalKecamatan = \App\Models\Kecamatan::has('crimeData')->count();
        $crimeTypes = CrimeData::distinct('jenis_kriminal')->count();

        return Inertia::render('CrimeData', [
            'crimeData' => $data,
            'statistics' => [
                'total_crimes' => $totalCrimes,
                'affected_provinsi' => $totalProvinsi,
                'affected_kabupaten_kota' => $totalKabupatenKota,
                'affected_kecamatan' => $totalKecamatan,
                'crime_types' => $crimeTypes,
            ],
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
