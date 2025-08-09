<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title' => 'Operasi Kamtibmas Wilayah Jakarta',
                'description' => 'Operasi pemantauan keamanan dan ketertiban masyarakat di wilayah Jakarta',
                'start_date' => Carbon::now()->addDays(1),
                'end_date' => Carbon::now()->addDays(3),
                'color' => '#DC2626', // Red
                'is_active' => true,
            ],
            [
                'title' => 'Rapat Koordinasi Keamanan',
                'description' => 'Rapat koordinasi antar instansi terkait keamanan nasional',
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(7),
                'color' => '#3B82F6', // Blue
                'is_active' => true,
            ],
            [
                'title' => 'Patroli Rutin Wilayah Surabaya',
                'description' => 'Patroli keamanan rutin di wilayah Surabaya dan sekitarnya',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(12),
                'color' => '#10B981', // Green
                'is_active' => true,
            ],
            [
                'title' => 'Training Personel Keamanan',
                'description' => 'Pelatihan untuk meningkatkan kemampuan personel keamanan',
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(16),
                'color' => '#F59E0B', // Orange
                'is_active' => true,
            ],
            [
                'title' => 'Evaluasi Sistem Keamanan',
                'description' => 'Evaluasi menyeluruh terhadap sistem keamanan yang telah berjalan',
                'start_date' => Carbon::now()->addDays(21),
                'end_date' => Carbon::now()->addDays(21),
                'color' => '#8B5CF6', // Purple
                'is_active' => true,
            ],
            [
                'title' => 'Simulasi Tanggap Darurat',
                'description' => 'Simulasi situasi darurat untuk menguji kesiapan tim',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(3),
                'color' => '#EC4899', // Pink
                'is_active' => true,
            ],
            [
                'title' => 'Pemantauan Acara Besar',
                'description' => 'Pemantauan keamanan selama berlangsungnya acara besar nasional',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addDays(2),
                'color' => '#14B8A6', // Teal
                'is_active' => true,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
