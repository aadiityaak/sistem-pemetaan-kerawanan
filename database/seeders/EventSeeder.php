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
            // Kamtibmas Events
            [
                'title' => 'Operasi Kamtibmas Wilayah Jakarta',
                'description' => 'Operasi pemantauan keamanan dan ketertiban masyarakat di wilayah Jakarta',
                'category' => 'Kamtibmas',
                'start_date' => Carbon::now()->addDays(1),
                'end_date' => Carbon::now()->addDays(3),
                'color' => '#DC2626', // Red
                'is_active' => true,
            ],
            [
                'title' => 'Patroli Rutin Wilayah Surabaya',
                'description' => 'Patroli keamanan rutin di wilayah Surabaya dan sekitarnya',
                'category' => 'Kamtibmas',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(12),
                'color' => '#DC2626', // Red
                'is_active' => true,
            ],
            [
                'title' => 'Simulasi Tanggap Darurat',
                'description' => 'Simulasi situasi darurat untuk menguji kesiapan tim',
                'category' => 'Kamtibmas',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(3),
                'color' => '#DC2626', // Red
                'is_active' => true,
            ],
            [
                'title' => 'Training Personel Keamanan',
                'description' => 'Pelatihan untuk meningkatkan kemampuan personel keamanan',
                'category' => 'Kamtibmas',
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(16),
                'color' => '#DC2626', // Red
                'is_active' => true,
            ],

            // Agenda Nasional Events
            [
                'title' => 'Upacara Hari Kemerdekaan RI',
                'description' => 'Upacara peringatan Hari Kemerdekaan Republik Indonesia ke-79',
                'category' => 'Agenda Nasional',
                'start_date' => Carbon::create(2024, 8, 17),
                'end_date' => Carbon::create(2024, 8, 17),
                'color' => '#2563EB', // Blue
                'is_active' => true,
            ],
            [
                'title' => 'Hari Sumpah Pemuda',
                'description' => 'Peringatan Hari Sumpah Pemuda dengan berbagai kegiatan nasional',
                'category' => 'Agenda Nasional',
                'start_date' => Carbon::create(2024, 10, 28),
                'end_date' => Carbon::create(2024, 10, 28),
                'color' => '#2563EB', // Blue
                'is_active' => true,
            ],
            [
                'title' => 'Hari Pahlawan Nasional',
                'description' => 'Peringatan Hari Pahlawan dengan upacara dan ziarah ke TMP',
                'category' => 'Agenda Nasional',
                'start_date' => Carbon::create(2024, 11, 10),
                'end_date' => Carbon::create(2024, 11, 10),
                'color' => '#2563EB', // Blue
                'is_active' => true,
            ],
            [
                'title' => 'Peringatan Hari Pancasila',
                'description' => 'Peringatan Hari Lahir Pancasila dengan upacara dan sosialisasi',
                'category' => 'Agenda Nasional',
                'start_date' => Carbon::create(2024, 6, 1),
                'end_date' => Carbon::create(2024, 6, 1),
                'color' => '#2563EB', // Blue
                'is_active' => true,
            ],

            // Agenda Internasional Events
            [
                'title' => 'KTT ASEAN 2024',
                'description' => 'Konferensi Tingkat Tinggi ASEAN tentang kerjasama regional',
                'category' => 'Agenda Internasional',
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(32),
                'color' => '#059669', // Green
                'is_active' => true,
            ],
            [
                'title' => 'Forum Kerjasama Asia Pasifik',
                'description' => 'Forum diskusi kerjasama ekonomi dan keamanan Asia Pasifik',
                'category' => 'Agenda Internasional',
                'start_date' => Carbon::now()->addDays(45),
                'end_date' => Carbon::now()->addDays(47),
                'color' => '#059669', // Green
                'is_active' => true,
            ],
            [
                'title' => 'Konferensi Internasional Anti Terorisme',
                'description' => 'Konferensi internasional tentang pencegahan dan penanggulangan terorisme',
                'category' => 'Agenda Internasional',
                'start_date' => Carbon::now()->addDays(60),
                'end_date' => Carbon::now()->addDays(62),
                'color' => '#059669', // Green
                'is_active' => true,
            ],

            // Meeting Events
            [
                'title' => 'Rapat Koordinasi Keamanan Nasional',
                'description' => 'Rapat koordinasi antar instansi terkait keamanan nasional',
                'category' => 'Meeting',
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(7),
                'color' => '#7C3AED', // Purple
                'is_active' => true,
            ],
            [
                'title' => 'Evaluasi Sistem Keamanan',
                'description' => 'Meeting evaluasi menyeluruh terhadap sistem keamanan yang telah berjalan',
                'category' => 'Meeting',
                'start_date' => Carbon::now()->addDays(21),
                'end_date' => Carbon::now()->addDays(21),
                'color' => '#7C3AED', // Purple
                'is_active' => true,
            ],

            // Training Events
            [
                'title' => 'Workshop Cyber Security',
                'description' => 'Workshop pelatihan keamanan siber untuk personel IT',
                'category' => 'Training',
                'start_date' => Carbon::now()->addDays(25),
                'end_date' => Carbon::now()->addDays(27),
                'color' => '#EA580C', // Orange
                'is_active' => true,
            ],
            [
                'title' => 'Pelatihan Manajemen Krisis',
                'description' => 'Pelatihan manajemen krisis dan tanggap darurat',
                'category' => 'Training',
                'start_date' => Carbon::now()->addDays(35),
                'end_date' => Carbon::now()->addDays(37),
                'color' => '#EA580C', // Orange
                'is_active' => true,
            ],

            // Conference Events  
            [
                'title' => 'Konferensi Keamanan Nasional 2024',
                'description' => 'Konferensi tahunan tentang strategi keamanan nasional',
                'category' => 'Conference',
                'start_date' => Carbon::now()->addDays(50),
                'end_date' => Carbon::now()->addDays(52),
                'color' => '#0891B2', // Cyan
                'is_active' => true,
            ],

            // Other Events
            [
                'title' => 'Pemantauan Acara Besar',
                'description' => 'Pemantauan keamanan selama berlangsungnya acara besar nasional',
                'category' => 'Other',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addDays(2),
                'color' => '#6B7280', // Gray
                'is_active' => true,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
