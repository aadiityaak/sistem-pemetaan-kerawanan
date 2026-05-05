<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoUploadController extends Controller
{
    /**
     * Handle chunked video upload
     */
    public function uploadChunk(Request $request)
    {
        if (AppSetting::get('monitoring_video_enabled', 'false') !== 'true') {
            return response()->json([
                'success' => false,
                'message' => 'Fitur video sedang dinonaktifkan.',
            ], 403);
        }

        $request->validate([
            'chunk' => 'required|file|max:10240',
            'chunkIndex' => 'required|integer|min:0',
            'totalChunks' => 'required|integer|min:1|max:5000',
            'uploadId' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]{8,100}$/'],
            'fileName' => 'required|string|max:255',
            'fileSize' => 'required|integer|min:1|max:104857600',
        ]);

        $chunkIndex = (int) $request->input('chunkIndex');
        $totalChunks = (int) $request->input('totalChunks');
        $uploadId = $request->input('uploadId');
        $originalFileName = $request->input('fileName');
        $fileSize = $request->input('fileSize');

        try {
            if ($chunkIndex >= $totalChunks) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chunk index tidak valid.',
                ], 422);
            }

            $fileExtension = strtolower((string) pathinfo($originalFileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm'];
            if ($fileExtension === '' || !in_array($fileExtension, $allowedExtensions, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format video tidak didukung.',
                ], 422);
            }

            // Create temporary directory for chunks
            $tempDir = storage_path('app/temp-chunks/' . $uploadId);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Save chunk
            $chunkFile = $request->file('chunk');
            $chunkPath = $tempDir . '/chunk_' . $chunkIndex;
            $chunkFile->move($tempDir, 'chunk_' . $chunkIndex);

            // Check if all chunks are uploaded
            $uploadedChunks = 0;
            for ($i = 0; $i < $totalChunks; $i++) {
                if (file_exists($tempDir . '/chunk_' . $i)) {
                    $uploadedChunks++;
                }
            }

            // If all chunks are uploaded, combine them
            if ($uploadedChunks === $totalChunks) {
                $safeFileName = Str::slug(pathinfo($originalFileName, PATHINFO_FILENAME)) . '_' . time() . '.' . $fileExtension;
                $finalPath = 'monitoring-data/videos/' . $safeFileName;
                $fullPath = storage_path('app/public/' . $finalPath);

                // Create videos directory if it doesn't exist
                $videoDir = dirname($fullPath);
                if (!is_dir($videoDir)) {
                    mkdir($videoDir, 0755, true);
                }

                // Combine chunks
                $finalFile = fopen($fullPath, 'wb');
                for ($i = 0; $i < $totalChunks; $i++) {
                    $chunkPath = $tempDir . '/chunk_' . $i;
                    $chunkData = file_get_contents($chunkPath);
                    fwrite($finalFile, $chunkData);
                    unlink($chunkPath); // Delete chunk after combining
                }
                fclose($finalFile);

                // Remove temporary directory (ensure it's empty first)
                $this->removeDirectory($tempDir);

                // Verify file size
                $combinedFileSize = filesize($fullPath);
                if ($combinedFileSize !== (int)$fileSize) {
                    unlink($fullPath);
                    return response()->json([
                        'success' => false,
                        'message' => 'File size mismatch. Upload may be corrupted.',
                    ], 400);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Video uploaded successfully',
                    'videoPath' => $finalPath,
                    'videoUrl' => Storage::url($finalPath),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chunk uploaded successfully',
                'uploadedChunks' => $uploadedChunks,
                'totalChunks' => $totalChunks,
            ]);

        } catch (\Exception $e) {
            report($e);
            
            return response()->json([
                'success' => false,
                'message' => 'Upload gagal.',
            ], 500);
        }
    }

    /**
     * Delete uploaded video
     */
    public function deleteVideo(Request $request)
    {
        if (AppSetting::get('monitoring_video_enabled', 'false') !== 'true') {
            return response()->json([
                'success' => false,
                'message' => 'Fitur video sedang dinonaktifkan.',
            ], 403);
        }

        $request->validate([
            'videoPath' => ['required', 'string', 'regex:/^monitoring-data\/videos\/[A-Za-z0-9_-]+_\d+\.(mp4|mov|avi|wmv|flv|webm)$/i'],
        ]);

        $videoPath = $request->input('videoPath');
        
        try {
            if (Storage::disk('public')->exists($videoPath)) {
                Storage::disk('public')->delete($videoPath);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Video deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Video not found',
            ], 404);

        } catch (\Exception $e) {
            report($e);
            
            return response()->json([
                'success' => false,
                'message' => 'Hapus video gagal.',
            ], 500);
        }
    }
    
    /**
     * Recursively remove directory and all contents
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        return rmdir($dir);
    }
}
