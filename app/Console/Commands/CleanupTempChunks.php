<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupTempChunks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chunks:cleanup {--older-than=1 : Remove chunks older than X hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary video chunks that are older than specified time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $olderThanHours = $this->option('older-than');
        $tempDir = storage_path('app/temp-chunks');
        
        if (!is_dir($tempDir)) {
            $this->info('No temp-chunks directory found.');
            return;
        }
        
        $cutoffTime = time() - ($olderThanHours * 3600);
        $removedDirs = 0;
        
        $directories = scandir($tempDir);
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            $dirPath = $tempDir . '/' . $dir;
            if (is_dir($dirPath)) {
                // Check if directory is older than cutoff time
                $dirMTime = filemtime($dirPath);
                if ($dirMTime < $cutoffTime) {
                    $this->info("Removing old chunk directory: $dir");
                    $this->removeDirectory($dirPath);
                    $removedDirs++;
                }
            }
        }
        
        $this->info("Cleanup completed. Removed $removedDirs old chunk directories.");
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