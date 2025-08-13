<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoUploadController extends Controller
{
    /**
     * Handle chunked video upload
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'chunk' => 'required|file',
            'chunkIndex' => 'required|integer',
            'totalChunks' => 'required|integer',
            'uploadId' => 'required|string',
            'fileName' => 'required|string',
            'fileSize' => 'required|integer',
        ]);

        $chunkIndex = $request->input('chunkIndex');
        $totalChunks = $request->input('totalChunks');
        $uploadId = $request->input('uploadId');
        $originalFileName = $request->input('fileName');
        $fileSize = $request->input('fileSize');

        try {
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
                $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                $safeFileName = Str::slug(pathinfo($originalFileName, PATHINFO_FILENAME)) . '_' . time() . '.' . $fileExtension;
                $finalPath = 'videos/' . $safeFileName;
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

                // Remove temporary directory
                rmdir($tempDir);

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
            Log::error('Video chunk upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete uploaded video
     */
    public function deleteVideo(Request $request)
    {
        $request->validate([
            'videoPath' => 'required|string',
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
            Log::error('Video delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}