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

        // Apply province filter for non-admin users
        if ($request->has('province_filter')) {
            $query->where('provinsi_id', $request->input('province_filter'));
        }

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

        // Filter by provinsi
        if ($request->has('provinsi_id') && $request->provinsi_id != '') {
            $query->where('provinsi_id', $request->provinsi_id);
        }

        // Filter by kabupaten/kota
        if ($request->has('kabupaten_kota_id') && $request->kabupaten_kota_id != '') {
            $query->where('kabupaten_kota_id', $request->kabupaten_kota_id);
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
        
        // Apply province filter for non-admin users in statistics
        if ($request->has('province_filter')) {
            $statsQuery->where('provinsi_id', $request->input('province_filter'));
        }
        
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

        // Get regional data for filters
        $provinsiQuery = Provinsi::select('id', 'nama')->orderBy('nama');
        $kabupatenKotaQuery = KabupatenKota::select('id', 'nama', 'provinsi_id')->orderBy('nama');

        // Apply province filter if user is restricted
        if ($request->has('province_filter')) {
            $provinsiQuery->where('id', $request->input('province_filter'));
            $kabupatenKotaQuery->where('provinsi_id', $request->input('province_filter'));
        }

        $provinsiList = $provinsiQuery->get();
        $kabupatenKotaList = $kabupatenKotaQuery->get();

        return Inertia::render('MonitoringData/Index', [
            'monitoringData' => $monitoringData,
            'statistics' => [
                'total' => $totalData,
                'active' => $activeData,
                'completed' => $completedData,
                'critical' => $criticalData,
            ],
            'filters' => array_merge(
                $request->only(['search', 'status', 'level', 'provinsi_id', 'kabupaten_kota_id']),
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]
            ),
            'provinsiList' => $provinsiList,
            'kabupatenKotaList' => $kabupatenKotaList,
        ]);
    }

    public function create(Request $request)
    {
        $categories = Category::active()->ordered()->with('subCategories')->get();
        
        // Append image URLs to categories and subcategories
        $categories->each(function ($category) {
            $category->append(['image_url']);
            $category->subCategories->each(function ($subCategory) {
                $subCategory->append(['image_url']);
            });
        });
        
        // Filter provinces based on user's permission
        $provinsiQuery = Provinsi::select('id', 'nama', 'latitude', 'longitude')->orderBy('nama');
        if ($request->has('province_filter')) {
            $provinsiQuery->where('id', $request->input('province_filter'));
        }
        $provinsiList = $provinsiQuery->get();
        
        // Filter kabupaten/kota and kecamatan based on user's province
        $kabupatenKotaQuery = KabupatenKota::orderBy('nama');
        $kecamatanQuery = Kecamatan::orderBy('nama');
        
        if ($request->has('province_filter')) {
            $kabupatenKotaQuery->where('provinsi_id', $request->input('province_filter'));
            $kecamatanQuery->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('provinsi_id', $request->input('province_filter'));
            });
        }
        
        $kabupatenKotaList = $kabupatenKotaQuery->get();
        $kecamatanList = $kecamatanQuery->get();

        return Inertia::render('MonitoringData/Create', [
            'categories' => $categories,
            'provinsiList' => $provinsiList,
            'kabupatenKotaList' => $kabupatenKotaList,
            'kecamatanList' => $kecamatanList,
        ]);
    }

    public function store(Request $request)
    {
        // Check if user can access this province
        if ($request->has('province_filter')) {
            $request->validate([
                'provinsi_id' => 'required|in:' . $request->input('province_filter'),
            ]);
        }
        
        $validated = $request->validate([
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sumber_berita' => 'nullable|string|max:255',
            'jumlah_terdampak' => 'nullable|integer|min:0',
            'severity_level' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:active,resolved,monitoring,archived',
            'incident_date' => 'nullable|date',
            'source' => 'nullable|string|max:255',
            'additional_data' => 'nullable|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,flv,webm|max:102400', // 100MB max for video
            'uploaded_video_path' => 'nullable|string', // For chunked uploaded video
        ]);

        // Handle gallery upload
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('monitoring-data/gallery', 'public');
                $galleryPaths[] = $path;
            }
        }
        
        // Handle video upload
        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('monitoring-data/videos', 'public');
        } elseif ($request->filled('uploaded_video_path')) {
            $videoPath = $request->input('uploaded_video_path');
        }

        // Convert empty kecamatan_id to null
        if (empty($validated['kecamatan_id'])) {
            $validated['kecamatan_id'] = null;
        }
        
        $validated['gallery'] = $galleryPaths;
        $validated['video_path'] = $videoPath;
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
        
        // Process video data
        if ($monitoringData->video_path) {
            $monitoringData->video_url = asset('storage/' . $monitoringData->video_path);
        }

        return Inertia::render('MonitoringData/Show', [
            'monitoringData' => $monitoringData,
        ]);
    }

    public function edit(Request $request, $id)
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
        
        // Process video data
        if ($monitoringData->video_path) {
            $monitoringData->video_url = asset('storage/' . $monitoringData->video_path);
        }
        
        // Filter provinces based on user's permission
        $provinsiQuery = Provinsi::select('id', 'nama', 'latitude', 'longitude')->orderBy('nama');
        if ($request->has('province_filter')) {
            $provinsiQuery->where('id', $request->input('province_filter'));
        }
        $provinsi = $provinsiQuery->get();

        // Filter kabupaten/kota and kecamatan based on user's province
        $kabupatenKotaQuery = KabupatenKota::orderBy('nama');
        $kecamatanQuery = Kecamatan::orderBy('nama');
        
        if ($request->has('province_filter')) {
            $kabupatenKotaQuery->where('provinsi_id', $request->input('province_filter'));
            $kecamatanQuery->whereHas('kabupatenKota', function ($q) use ($request) {
                $q->where('provinsi_id', $request->input('province_filter'));
            });
        }
        
        $kabupatenKota = $kabupatenKotaQuery->get();
        $kecamatan = $kecamatanQuery->get();

        return Inertia::render('MonitoringData/Edit', [
            'monitoringData' => $monitoringData,
            'categories' => $categories,
            'provinsi' => $provinsi,
            'kabupatenKota' => $kabupatenKota,
            'kecamatan' => $kecamatan,
            'isUserRestricted' => $request->has('province_filter'),
            'userProvinsiId' => $request->input('province_filter'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $monitoringData = MonitoringData::findOrFail($id);

        $validated = $request->validate([
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sumber_berita' => 'nullable|string|max:255',
            'jumlah_terdampak' => 'nullable|integer|min:0',
            'severity_level' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:active,resolved,monitoring,archived',
            'incident_date' => 'nullable|date',
            'source' => 'nullable|string|max:255',
            'additional_data' => 'nullable|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,flv,webm|max:102400', // 100MB max for video
            'uploaded_video_path' => 'nullable|string', // For chunked uploaded video
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
        
        // Handle video upload
        if ($request->hasFile('video')) {
            // Delete existing video if present
            if (!empty($monitoringData->video_path)) {
                Storage::disk('public')->delete($monitoringData->video_path);
            }
            $validated['video_path'] = $request->file('video')->store('monitoring-data/videos', 'public');
        } elseif ($request->filled('uploaded_video_path')) {
            // Handle chunked uploaded video
            // Delete existing video if present and different from uploaded one
            if (!empty($monitoringData->video_path) && $monitoringData->video_path !== $request->input('uploaded_video_path')) {
                Storage::disk('public')->delete($monitoringData->video_path);
            }
            $validated['video_path'] = $request->input('uploaded_video_path');
        } else {
            // Preserve existing video if no new video is uploaded
            $validated['video_path'] = $monitoringData->video_path;
        }
        
        // Remove uploaded_video_path from validated data as it's not a database field
        if (isset($validated['uploaded_video_path'])) {
            unset($validated['uploaded_video_path']);
        }
        
        // Convert empty kecamatan_id to null
        if (empty($validated['kecamatan_id'])) {
            $validated['kecamatan_id'] = null;
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
        
        // Delete video file from storage
        if (!empty($monitoringData->video_path)) {
            Storage::disk('public')->delete($monitoringData->video_path);
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
