<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MonitoringDataController extends Controller
{
    public function index(Request $request)
    {
        $query = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory']);

        // Set default date range to last 6 months if not provided
        $defaultStartDate = now()->subMonths(6)->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');
        
        $startDate = $request->query('start_date', $defaultStartDate);
        $endDate = $request->query('end_date', $defaultEndDate);

        // Filter berdasarkan tanggal
        if ($startDate) {
            $query->whereDate('incident_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('incident_date', '<=', $endDate);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kecamatan', function ($q) use ($request) {
                        $q->where('nama', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('kecamatan.kabupatenKota', function ($q) use ($request) {
                        $q->where('nama', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('category', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by status - mapping dari Vue ke database
        if ($request->has('status') && $request->status != '') {
            $statusMapping = [
                'aktif' => 'active',
                'selesai' => 'resolved',
                'dalam_proses' => 'monitoring',
            ];
            $dbStatus = $statusMapping[$request->status] ?? $request->status;
            $query->where('status', $dbStatus);
        }

        // Filter by level - mapping dari Vue ke database
        if ($request->has('level') && $request->level != '') {
            $levelMapping = [
                'rendah' => 'low',
                'sedang' => 'medium',
                'tinggi' => 'high',
                'kritis' => 'critical',
            ];
            $dbLevel = $levelMapping[$request->level] ?? $request->level;
            $query->where('severity_level', $dbLevel);
        }

        $monitoringData = $query->latest()->paginate(15);

        // Transform data untuk Vue component
        $monitoringData->getCollection()->transform(function ($item) {
            // Mapping status dari database ke Vue
            $statusMapping = [
                'active' => 'aktif',
                'resolved' => 'selesai',
                'monitoring' => 'dalam_proses',
                'archived' => 'arsip',
            ];

            // Mapping level dari database ke Vue
            $levelMapping = [
                'low' => 'rendah',
                'medium' => 'sedang',
                'high' => 'tinggi',
                'critical' => 'kritis',
            ];

            $item->status = $statusMapping[$item->status] ?? $item->status;
            $item->level_kejadian = $levelMapping[$item->severity_level] ?? $item->severity_level;
            $item->tanggal_laporan = $item->incident_date;
            $item->jumlah_korban = $item->jumlah_terdampak;
            
            // Process gallery data
            if ($item->gallery && is_array($item->gallery)) {
                $item->gallery = collect($item->gallery)->map(function ($path) {
                    return [
                        'path' => $path,
                        'url' => asset('storage/' . $path)
                    ];
                })->toArray();
            } else {
                $item->gallery = [];
            }

            return $item;
        });

        // Statistik sesuai dengan yang diharapkan Vue component - apply date filters
        $statsQuery = MonitoringData::query();
        if ($startDate) {
            $statsQuery->whereDate('incident_date', '>=', $startDate);
        }
        if ($endDate) {
            $statsQuery->whereDate('incident_date', '<=', $endDate);
        }
        
        $totalData = (clone $statsQuery)->count();
        $activeData = (clone $statsQuery)->where('status', 'active')->count();
        $completedData = (clone $statsQuery)->where('status', 'resolved')->count();
        $criticalData = (clone $statsQuery)->where('severity_level', 'critical')->count();

        return Inertia::render('MonitoringData/Index', [
            'monitoringData' => $monitoringData,
            'statistics' => [
                'total' => $totalData,
                'active' => $activeData,
                'completed' => $completedData,
                'critical' => $criticalData,
            ],
            'filters' => array_merge(
                $request->only(['search', 'status', 'level']),
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]
            ),
        ]);
    }

    public function create()
    {
        $categories = Category::active()->ordered()->with('subCategories')->get();
        
        // Append image URLs to categories and subcategories
        $categories->each(function ($category) {
            $category->append(['image_url']);
            $category->subCategories->each(function ($subCategory) {
                $subCategory->append(['image_url']);
            });
        });
        
        $provinsiList = Provinsi::orderBy('nama')->get();
        $kabupatenKotaList = KabupatenKota::orderBy('nama')->get();
        $kecamatanList = Kecamatan::orderBy('nama')->get();

        return Inertia::render('MonitoringData/Create', [
            'categories' => $categories,
            'provinsiList' => $provinsiList,
            'kabupatenKotaList' => $kabupatenKotaList,
            'kecamatanList' => $kecamatanList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'jumlah_terdampak' => 'nullable|integer|min:0',
            'severity_level' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:active,resolved,monitoring,archived',
            'incident_date' => 'nullable|date',
            'source' => 'nullable|string|max:255',
            'additional_data' => 'nullable|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
        ]);

        // Handle gallery upload
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('monitoring-data/gallery', 'public');
                $galleryPaths[] = $path;
            }
        }

        $validated['gallery'] = $galleryPaths;
        MonitoringData::create($validated);

        return redirect()->route('monitoring-data.index')
            ->with('success', 'Data monitoring berhasil ditambahkan.');
    }

    public function show($id)
    {
        $monitoringData = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])
            ->findOrFail($id);

        // Append image URLs to category and subcategory
        if ($monitoringData->category) {
            $monitoringData->category->append(['image_url']);
        }
        if ($monitoringData->subCategory) {
            $monitoringData->subCategory->append(['image_url']);
        }

        // Transform data untuk konsistensi dengan index
        $statusMapping = [
            'active' => 'aktif',
            'resolved' => 'selesai',
            'monitoring' => 'dalam_proses',
            'archived' => 'arsip',
        ];

        $levelMapping = [
            'low' => 'rendah',
            'medium' => 'sedang',
            'high' => 'tinggi',
            'critical' => 'kritis',
        ];

        $monitoringData->status = $statusMapping[$monitoringData->status] ?? $monitoringData->status;
        $monitoringData->level_kejadian = $levelMapping[$monitoringData->severity_level] ?? $monitoringData->severity_level;
        $monitoringData->tanggal_laporan = $monitoringData->incident_date;
        $monitoringData->jumlah_korban = $monitoringData->jumlah_terdampak;
        
        // Process gallery data
        if ($monitoringData->gallery && is_array($monitoringData->gallery)) {
            $monitoringData->gallery = collect($monitoringData->gallery)->map(function ($path) {
                return [
                    'path' => $path,
                    'url' => asset('storage/' . $path)
                ];
            })->toArray();
        } else {
            $monitoringData->gallery = [];
        }

        return Inertia::render('MonitoringData/Show', [
            'monitoringData' => $monitoringData,
        ]);
    }

    public function edit($id)
    {
        $monitoringData = MonitoringData::with(['provinsi', 'kabupatenKota', 'kecamatan', 'category', 'subCategory'])->findOrFail($id);
        $categories = Category::active()->ordered()->with('subCategories')->get();
        
        // Append image URLs to categories and subcategories
        $categories->each(function ($category) {
            $category->append(['image_url']);
            $category->subCategories->each(function ($subCategory) {
                $subCategory->append(['image_url']);
            });
        });
        
        $provinsi = Provinsi::orderBy('nama')->get();

        // Send all kabupaten/kota and kecamatan for dynamic filtering in frontend
        $kabupatenKota = KabupatenKota::orderBy('nama')->get();
        $kecamatan = Kecamatan::orderBy('nama')->get();

        return Inertia::render('MonitoringData/Edit', [
            'monitoringData' => $monitoringData,
            'categories' => $categories,
            'provinsi' => $provinsi,
            'kabupatenKota' => $kabupatenKota,
            'kecamatan' => $kecamatan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $monitoringData = MonitoringData::findOrFail($id);

        $validated = $request->validate([
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'jumlah_terdampak' => 'nullable|integer|min:0',
            'severity_level' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:active,resolved,monitoring,archived',
            'incident_date' => 'nullable|date',
            'source' => 'nullable|string|max:255',
            'additional_data' => 'nullable|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
        ]);

        // Handle gallery upload
        $galleryPaths = $monitoringData->gallery ?? [];
        if ($request->hasFile('gallery')) {
            // Upload new files and append to existing ones
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('monitoring-data/gallery', 'public');
                $galleryPaths[] = $path;
            }
        }

        $validated['gallery'] = $galleryPaths;
        $monitoringData->update($validated);

        return redirect()->route('monitoring-data.index')
            ->with('success', 'Data monitoring berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $monitoringData = MonitoringData::findOrFail($id);
        
        // Delete gallery files from storage
        if (!empty($monitoringData->gallery)) {
            foreach ($monitoringData->gallery as $filePath) {
                Storage::disk('public')->delete($filePath);
            }
        }
        
        $monitoringData->delete();

        return redirect()->route('monitoring-data.index')
            ->with('success', 'Data monitoring berhasil dihapus.');
    }

    public function deleteGalleryImage(Request $request, $id)
    {
        $monitoringData = MonitoringData::findOrFail($id);
        $imagePath = $request->input('image_path');
        
        if ($imagePath && $monitoringData->gallery && is_array($monitoringData->gallery)) {
            // Remove from array
            $gallery = $monitoringData->gallery;
            $updatedGallery = array_filter($gallery, function($path) use ($imagePath) {
                return $path !== $imagePath;
            });
            
            // Update database
            $monitoringData->update(['gallery' => array_values($updatedGallery)]);
            
            // Delete physical file
            Storage::disk('public')->delete($imagePath);
            
            return response()->json(['message' => 'Image deleted successfully']);
        }
        
        return response()->json(['error' => 'Image not found'], 404);
    }
}
