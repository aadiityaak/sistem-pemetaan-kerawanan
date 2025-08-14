<?php

namespace App\Console\Commands;

use App\Models\MonitoringData;
use Illuminate\Console\Command;

class CheckVideoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:video-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check video data in monitoring_data table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking video_path records in monitoring_data table...');

        $dataWithVideo = MonitoringData::whereNotNull('video_path')->latest()->first();

        if ($dataWithVideo) {
            $this->info("Found record with video_path:");
            $this->line("ID: " . $dataWithVideo->id);
            $this->line("Title: " . $dataWithVideo->title);
            $this->line("Video Path: " . $dataWithVideo->video_path);
        } else {
            $this->warn("No records with video_path found.");
        }

        // Check total records
        $total = MonitoringData::count();
        $this->info("Total monitoring_data records: " . $total);

        // Check recent records (last 5)
        $this->info("\nLast 5 records:");
        $recent = MonitoringData::latest()->take(5)->get(['id', 'title', 'video_path', 'created_at']);
        
        if ($recent->count() > 0) {
            $this->table(
                ['ID', 'Title', 'Video Path', 'Created At'],
                $recent->map(function ($record) {
                    return [
                        $record->id,
                        $record->title,
                        $record->video_path ?? 'NULL',
                        $record->created_at
                    ];
                })
            );
        } else {
            $this->warn("No monitoring data records found.");
        }
    }
}
