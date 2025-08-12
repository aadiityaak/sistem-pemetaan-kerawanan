<?php

namespace App\Http\Controllers;

use App\Models\Sembako;
use App\Models\KabupatenKota;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

    public function exportCsv(Request $request)
    {
        // Apply same filters as index method
        $query = Sembako::with(['kabupatenKota.provinsi']);

        // Apply all filters
        if ($request->filled('nama_komoditas')) {
            $query->where('nama_komoditas', 'like', '%' . $request->nama_komoditas . '%');
        }

        if ($request->filled('kabupaten_kota_id')) {
            $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
        }

        if ($request->filled('provinsi_id')) {
            $query->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('provinsi_id', $request->provinsi_id);
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_pencatatan', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->where('tanggal_pencatatan', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('harga_min')) {
            $query->where('harga', '>=', $request->harga_min);
        }

        if ($request->filled('harga_max')) {
            $query->where('harga', '<=', $request->harga_max);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_komoditas', 'like', "%{$search}%")
                  ->orWhere('satuan', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('tanggal_pencatatan', 'desc')->get();

        // Generate CSV content
        $csvData = [];
        $csvData[] = [
            'Nama Komoditas',
            'Satuan', 
            'Harga',
            'Provinsi',
            'Kabupaten/Kota',
            'Tanggal Pencatatan',
            'Keterangan'
        ];

        foreach ($data as $item) {
            $csvData[] = [
                $item->nama_komoditas,
                $item->satuan,
                $item->harga,
                $item->kabupatenKota->provinsi->nama,
                $item->kabupatenKota->nama,
                $item->tanggal_pencatatan,
                $item->keterangan ?? ''
            ];
        }

        $filename = 'data_sembako_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csvData as $row) {
                fputcsv($file, $row, ';'); // Use semicolon as delimiter for better Excel compatibility
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadSample()
    {
        // Get sample data for CSV template
        $provinsi = Provinsi::with('kabupatenKota')->first();
        $kabupatenKota = $provinsi ? $provinsi->kabupatenKota->first() : null;

        $sampleData = [
            ['Nama Komoditas', 'Satuan', 'Harga', 'Provinsi', 'Kabupaten/Kota', 'Tanggal Pencatatan', 'Keterangan'],
            ['Beras Premium', 'kg', '15000', $provinsi->nama ?? 'DKI Jakarta', $kabupatenKota->nama ?? 'Jakarta Selatan', date('Y-m-d'), 'Harga pasar tradisional'],
            ['Minyak Goreng', 'liter', '18000', $provinsi->nama ?? 'DKI Jakarta', $kabupatenKota->nama ?? 'Jakarta Selatan', date('Y-m-d'), 'Harga supermarket'],
            ['Gula Pasir', 'kg', '13000', $provinsi->nama ?? 'DKI Jakarta', $kabupatenKota->nama ?? 'Jakarta Selatan', date('Y-m-d'), 'Harga pasar modern'],
        ];

        $filename = 'sample_import_sembako.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sampleData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($sampleData as $row) {
                fputcsv($file, $row, ';');
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $user = Auth::user();
        $startTime = now();
        
        // Log import start
        Log::info('CSV Import Started', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'timestamp' => $startTime,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            $request->validate([
                'csv_file' => 'required|file|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel|max:5120', // 5MB max
            ]);

            $file = $request->file('csv_file');
            $originalFileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $path = $file->getPathname();
            
            // Log file details
            Log::info('CSV Import File Details', [
                'user_id' => $user->id,
                'original_filename' => $originalFileName,
                'file_size' => $fileSize,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'mime_type' => $file->getMimeType()
            ]);
            
            $data = [];
            $errors = [];
            $successCount = 0;
            $errorCount = 0;
            $rowNumber = 0; // Initialize rowNumber

            Log::info('CSV Import: Starting file processing', [
                'user_id' => $user->id,
                'file_path' => $path,
                'file_exists' => file_exists($path),
                'file_readable' => is_readable($path)
            ]);

        if (($handle = fopen($path, "r")) !== FALSE) {
            Log::info('CSV Import: File opened successfully', ['user_id' => $user->id]);
            
            $header = fgetcsv($handle, 1000, ";"); // Skip header row
            $rowNumber = 1;
            
            // Log header info
            Log::info('CSV Import Header', [
                'user_id' => $user->id,
                'header' => $header,
                'header_count' => count($header ?? [])
            ]);

            // Check if file has header
            if ($header === FALSE || empty($header)) {
                Log::warning('CSV Import: Empty or invalid file', ['user_id' => $user->id]);
                return Redirect::route('sembako.index')
                    ->withErrors(['csv_file' => 'File CSV kosong atau tidak valid']);
            }

            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $rowNumber++;
                
                // Log raw row data for debugging
                if ($rowNumber <= 5) { // Log first 5 rows only
                    Log::info('CSV Row Data', [
                        'user_id' => $user->id,
                        'row_number' => $rowNumber,
                        'row_data' => $row,
                        'row_count' => count($row)
                    ]);
                }
                
                if (count($row) < 6) {
                    $errors[] = "Baris {$rowNumber}: Data tidak lengkap (hanya " . count($row) . " kolom)";
                    $errorCount++;
                    Log::warning('CSV Row Incomplete', [
                        'user_id' => $user->id,
                        'row_number' => $rowNumber,
                        'expected_columns' => 6,
                        'actual_columns' => count($row),
                        'row_data' => $row
                    ]);
                    continue;
                }

                // Map CSV columns
                $namaKomoditas = trim($row[0]);
                $satuan = trim($row[1]);
                $harga = trim($row[2]);
                $provinsiNama = trim($row[3]);
                $kabupatenNama = trim($row[4]);
                $tanggalPencatatan = trim($row[5]);
                $keterangan = isset($row[6]) ? trim($row[6]) : null;

                // Validate required fields
                if (empty($namaKomoditas) || empty($satuan) || empty($harga) || empty($provinsiNama) || empty($kabupatenNama) || empty($tanggalPencatatan)) {
                    $errors[] = "Baris {$rowNumber}: Ada kolom yang kosong";
                    $errorCount++;
                    continue;
                }

                // Validate price
                if (!is_numeric($harga) || $harga < 0) {
                    $errors[] = "Baris {$rowNumber}: Harga tidak valid ({$harga})";
                    $errorCount++;
                    continue;
                }

                // Validate date
                if (!strtotime($tanggalPencatatan)) {
                    $errors[] = "Baris {$rowNumber}: Format tanggal tidak valid ({$tanggalPencatatan})";
                    $errorCount++;
                    continue;
                }

                // Find kabupaten/kota by name and province
                $kabupatenKota = KabupatenKota::whereHas('provinsi', function($q) use ($provinsiNama) {
                    $q->where('nama', 'like', "%{$provinsiNama}%");
                })->where('nama', 'like', "%{$kabupatenNama}%")->first();

                if (!$kabupatenKota) {
                    $errors[] = "Baris {$rowNumber}: Kabupaten/Kota '{$kabupatenNama}' di provinsi '{$provinsiNama}' tidak ditemukan";
                    $errorCount++;
                    continue;
                }

                try {
                    Sembako::create([
                        'nama_komoditas' => $namaKomoditas,
                        'satuan' => $satuan,
                        'harga' => (int)$harga,
                        'kabupaten_kota_id' => $kabupatenKota->id,
                        'tanggal_pencatatan' => date('Y-m-d', strtotime($tanggalPencatatan)),
                        'keterangan' => $keterangan,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: Gagal menyimpan data - " . $e->getMessage();
                    $errorCount++;
                }
            }
            
            fclose($handle);
        } else {
            Log::error('CSV Import: Cannot open file', [
                'user_id' => $user->id,
                'file_path' => $path,
                'file_exists' => file_exists($path)
            ]);
            
            return Redirect::route('sembako.index')
                ->withErrors(['csv_file' => 'Tidak dapat membaca file CSV']);
        }

        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);
        
        // Log final results
        Log::info('CSV Import Completed', [
            'user_id' => $user->id,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_rows' => $rowNumber - 1,
            'duration_seconds' => $duration,
            'errors' => $errors
        ]);

        $message = "Import selesai: {$successCount} data berhasil";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} data gagal";
        }

        return Redirect::route('sembako.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
        } catch (\Exception $e) {
            Log::error('CSV Import Failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $originalFileName ?? 'unknown',
                'timestamp' => now()
            ]);
            
            return Redirect::route('sembako.index')
                ->withErrors(['csv_file' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }
}
