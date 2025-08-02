<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;
use App\Models\MonitoringData;
use App\Models\Provinsi;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MonitoringDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data referensi
        $categories = Category::all();
        $provinces = Provinsi::all();
        $keamananCategory = Category::where('slug', 'keamanan')->first();
        $ideologiCategory = Category::where('slug', 'ideologi')->first();
        $politikCategory = Category::where('slug', 'politik')->first();
        $ekonomiCategory = Category::where('slug', 'ekonomi')->first();
        $sosbudCategory = Category::where('slug', 'sosial-budaya')->first();

        // Data sample untuk setiap kategori
        $sampleData = [
            // KEAMANAN - Security Related Data
            [
                'category' => $keamananCategory,
                'data' => [
                    [
                        'title' => 'Pencurian Kendaraan Bermotor',
                        'description' => 'Terjadi pencurian sepeda motor di area parkir mall pada malam hari',
                        'jumlah_terdampak' => 1,
                        'severity_level' => 'medium',
                        'status' => 'active',
                        'source' => 'Kepolisian',
                        'sub_category_name' => 'Kriminalitas',
                        'additional_data' => [
                            'victims' => 1,
                            'suspects' => 2,
                            'losses' => 15000000,
                        ],
                    ],
                    [
                        'title' => 'Laporan Terduga Teroris',
                        'description' => 'Penemuan benda mencurigakan di area publik yang memerlukan penanganan Densus 88',
                        'jumlah_terdampak' => 200,
                        'severity_level' => 'critical',
                        'status' => 'resolved',
                        'source' => 'Densus 88',
                        'sub_category_name' => 'Terorisme',
                        'additional_data' => [
                            'response_time' => '15 minutes',
                            'evacuated' => 200,
                        ],
                    ],
                    [
                        'title' => 'Tawuran Antar Warga',
                        'description' => 'Bentrokan antara dua kelompok warga akibat perselisihan lahan',
                        'jumlah_terdampak' => 15,
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'Satpol PP',
                        'sub_category_name' => 'Gangguan Kamtibmas',
                        'additional_data' => [
                            'injured' => 5,
                            'duration' => '2 hours',
                        ],
                    ],
                    [
                        'title' => 'Banjir Akibat Hujan Deras',
                        'description' => 'Banjir melanda 3 kelurahan akibat luapan sungai dan hujan deras berkelanjutan',
                        'jumlah_terdampak' => 600,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'BNPB',
                        'sub_category_name' => 'Bencana Alam',
                        'additional_data' => [
                            'affected_families' => 150,
                            'water_level' => '1.5 meter',
                        ],
                    ],
                    [
                        'title' => 'Serangan Phishing Banking',
                        'description' => 'Laporan nasabah terkait upaya pencurian data rekening melalui email palsu',
                        'jumlah_terdampak' => 25,
                        'severity_level' => 'medium',
                        'status' => 'resolved',
                        'source' => 'OJK',
                        'sub_category_name' => 'Cyber Crime',
                        'additional_data' => [
                            'reported_cases' => 25,
                            'financial_loss' => 500000000,
                        ],
                    ],
                ],
            ],

            // IDEOLOGI - Ideology Related Data
            [
                'category' => $ideologiCategory,
                'data' => [
                    [
                        'title' => 'Penyebaran Konten Radikal',
                        'description' => 'Terdeteksi penyebaran konten radikal melalui media sosial di wilayah tertentu',
                        'jumlah_terdampak' => 500,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'BNPT',
                        'sub_category_name' => 'Radikalisme',
                        'additional_data' => [
                            'accounts_blocked' => 15,
                            'posts_removed' => 45,
                        ],
                    ],
                    [
                        'title' => 'Gerakan Separatis',
                        'description' => 'Aktivitas kelompok yang diduga ingin memisahkan diri dari NKRI',
                        'jumlah_terdampak' => 1000,
                        'severity_level' => 'critical',
                        'status' => 'monitoring',
                        'source' => 'TNI',
                        'sub_category_name' => 'Separatisme',
                        'additional_data' => [
                            'group_members' => 30,
                            'weapons_seized' => 12,
                        ],
                    ],
                    [
                        'title' => 'Propaganda Ekstremis',
                        'description' => 'Ditemukan pamflet dan poster berisi ajakan ekstremisme di area kampus',
                        'jumlah_terdampak' => 200,
                        'severity_level' => 'medium',
                        'status' => 'resolved',
                        'source' => 'Kemendikbud',
                        'sub_category_name' => 'Ekstremisme',
                        'additional_data' => [
                            'pamphlets_found' => 100,
                            'suspects' => 3,
                        ],
                    ],
                ],
            ],

            // POLITIK - Political Related Data
            [
                'category' => $politikCategory,
                'data' => [
                    [
                        'title' => 'Dugaan Korupsi APBD',
                        'description' => 'Laporan masyarakat terkait penyalahgunaan dana APBD di proyek infrastruktur',
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'KPK',
                        'sub_category_name' => 'Korupsi',
                        'additional_data' => [
                            'amount' => 5000000000,
                            'officials_involved' => 3,
                        ],
                    ],
                    [
                        'title' => 'Sengketa Hasil Pilkada',
                        'description' => 'Perselisihan hasil penghitungan suara pilkada yang berujung pada protes massa',
                        'severity_level' => 'medium',
                        'status' => 'monitoring',
                        'source' => 'Bawaslu',
                        'sub_category_name' => 'Pilkada',
                        'additional_data' => [
                            'disputed_votes' => 5000,
                            'protesters' => 200,
                        ],
                    ],
                    [
                        'title' => 'Demo Mahasiswa',
                        'description' => 'Demonstrasi mahasiswa menolak kenaikan UKT di berbagai universitas',
                        'severity_level' => 'low',
                        'status' => 'resolved',
                        'source' => 'Polda',
                        'sub_category_name' => 'Demonstrasi',
                        'additional_data' => [
                            'participants' => 500,
                            'duration' => '4 hours',
                        ],
                    ],
                ],
            ],

            // EKONOMI - Economic Related Data
            [
                'category' => $ekonomiCategory,
                'data' => [
                    [
                        'title' => 'Kenaikan Harga Pangan',
                        'description' => 'Inflasi harga beras dan minyak goreng mencapai 15% dalam sebulan',
                        'severity_level' => 'medium',
                        'status' => 'active',
                        'source' => 'BPS',
                        'sub_category_name' => 'Inflasi',
                        'additional_data' => [
                            'rice_price_increase' => '15%',
                            'oil_price_increase' => '20%',
                        ],
                    ],
                    [
                        'title' => 'Angka Kemiskinan Meningkat',
                        'description' => 'Survei menunjukkan peningkatan angka kemiskinan di daerah terpencil',
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Kemsos',
                        'sub_category_name' => 'Kemiskinan',
                        'additional_data' => [
                            'poverty_rate' => '12.5%',
                            'affected_families' => 2500,
                        ],
                    ],
                    [
                        'title' => 'Lonjakan Pengangguran',
                        'description' => 'PHK massal di sektor industri menyebabkan peningkatan pengangguran',
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'Disnaker',
                        'sub_category_name' => 'Pengangguran',
                        'additional_data' => [
                            'laid_off_workers' => 1000,
                            'unemployment_rate' => '8.2%',
                        ],
                    ],
                ],
            ],

            // SOSIAL BUDAYA - Social Cultural Data
            [
                'category' => $sosbudCategory,
                'data' => [
                    [
                        'title' => 'Konflik Antar Etnis',
                        'description' => 'Ketegangan antar kelompok etnis akibat isu tanah ulayat',
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'Kemendagri',
                        'sub_category_name' => 'Konflik Etnis',
                        'additional_data' => [
                            'ethnic_groups' => 2,
                            'mediation_sessions' => 5,
                        ],
                    ],
                    [
                        'title' => 'Pelanggaran Toleransi Beragama',
                        'description' => 'Laporan penolakan pembangunan tempat ibadah oleh kelompok tertentu',
                        'severity_level' => 'medium',
                        'status' => 'resolved',
                        'source' => 'Kemenag',
                        'sub_category_name' => 'Toleransi',
                        'additional_data' => [
                            'religious_groups' => 2,
                            'dialogue_sessions' => 3,
                        ],
                    ],
                    [
                        'title' => 'Krisis Guru di Daerah Terpencil',
                        'description' => 'Kekurangan tenaga pengajar di wilayah 3T menyebabkan penurunan kualitas pendidikan',
                        'severity_level' => 'medium',
                        'status' => 'active',
                        'source' => 'Kemendikbud',
                        'sub_category_name' => 'Pendidikan',
                        'additional_data' => [
                            'teacher_shortage' => 50,
                            'affected_schools' => 25,
                        ],
                    ],
                    [
                        'title' => 'Wabah DBD',
                        'description' => 'Peningkatan kasus demam berdarah dengue di musim hujan',
                        'severity_level' => 'medium',
                        'status' => 'active',
                        'source' => 'Dinkes',
                        'sub_category_name' => 'Kesehatan',
                        'additional_data' => [
                            'cases' => 150,
                            'deaths' => 3,
                        ],
                    ],
                    [
                        'title' => 'Jaringan Narkoba Pelajar',
                        'description' => 'Penggerebekan jaringan peredaran narkoba di lingkungan sekolah',
                        'severity_level' => 'high',
                        'status' => 'resolved',
                        'source' => 'BNN',
                        'sub_category_name' => 'Narkoba',
                        'additional_data' => [
                            'students_arrested' => 8,
                            'drugs_seized' => '2 kg',
                        ],
                    ],
                ],
            ],
        ];

        // Koordinat untuk berbagai provinsi Indonesia (sample)
        $provinceCoordinates = [
            'DKI Jakarta' => [
                ['lat' => -6.2088, 'lng' => 106.8456],
                ['lat' => -6.1751, 'lng' => 106.8650],
                ['lat' => -6.2615, 'lng' => 106.7815],
            ],
            'Jawa Barat' => [
                ['lat' => -6.9175, 'lng' => 107.6191],
                ['lat' => -6.9039, 'lng' => 107.6186],
                ['lat' => -6.8957, 'lng' => 107.6338],
            ],
            'Jawa Tengah' => [
                ['lat' => -7.2575, 'lng' => 110.4017],
                ['lat' => -7.7956, 'lng' => 110.3695],
                ['lat' => -6.9932, 'lng' => 110.4203],
            ],
            'Jawa Timur' => [
                ['lat' => -7.2504, 'lng' => 112.7688],
                ['lat' => -7.9666, 'lng' => 112.6326],
                ['lat' => -8.1132, 'lng' => 113.2093],
            ],
            'Sumatera Utara' => [
                ['lat' => 3.5952, 'lng' => 98.6722],
                ['lat' => 3.5840, 'lng' => 98.6756],
                ['lat' => 2.9442, 'lng' => 99.0681],
            ],
            'Sumatera Barat' => [
                ['lat' => -0.9471, 'lng' => 100.4172],
                ['lat' => -0.7893, 'lng' => 100.6543],
                ['lat' => -1.6101, 'lng' => 103.6131],
            ],
            'Bali' => [
                ['lat' => -8.4095, 'lng' => 115.1889],
                ['lat' => -8.3405, 'lng' => 115.0920],
                ['lat' => -8.6500, 'lng' => 115.2167],
            ],
        ];

        // Buat data monitoring untuk setiap kategori
        foreach ($sampleData as $categoryData) {
            $category = $categoryData['category'];
            if (! $category) {
                continue;
            }

            foreach ($categoryData['data'] as $index => $item) {
                // Ambil sub category berdasarkan nama
                $subCategory = SubCategory::where('category_id', $category->id)
                    ->where('name', 'like', '%'.$item['sub_category_name'].'%')
                    ->first();

                if (! $subCategory) {
                    continue;
                }

                // Pilih provinsi secara acak
                $randomProvince = $provinces->random();

                // Ambil kabupaten/kota dari provinsi tersebut
                $kabupatenKota = KabupatenKota::where('provinsi_id', $randomProvince->id)->inRandomOrder()->first();
                if (! $kabupatenKota) {
                    continue;
                }

                // Ambil kecamatan dari kabupaten/kota tersebut
                $kecamatan = Kecamatan::where('kabupaten_kota_id', $kabupatenKota->id)->inRandomOrder()->first();
                if (! $kecamatan) {
                    continue;
                }

                // Tentukan koordinat berdasarkan provinsi atau gunakan koordinat acak Indonesia
                $coordinates = $provinceCoordinates[$randomProvince->nama] ?? null;
                if ($coordinates) {
                    $coord = $coordinates[array_rand($coordinates)];
                    $latitude = $coord['lat'] + (rand(-100, 100) / 10000); // Variasi kecil
                    $longitude = $coord['lng'] + (rand(-100, 100) / 10000);
                } else {
                    // Koordinat acak untuk Indonesia
                    $latitude = rand(-11000, 6000) / 1000; // -11 sampai 6
                    $longitude = rand(95000, 141000) / 1000; // 95 sampai 141
                }

                // Tanggal kejadian (1-30 hari yang lalu)
                $incidentDate = Carbon::now()->subDays(rand(1, 30));

                MonitoringData::create([
                    'provinsi_id' => $randomProvince->id,
                    'kabupaten_kota_id' => $kabupatenKota->id,
                    'kecamatan_id' => $kecamatan->id,
                    'category_id' => $category->id,
                    'sub_category_id' => $subCategory->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'jumlah_terdampak' => $item['jumlah_terdampak'] ?? null,
                    'additional_data' => $item['additional_data'],
                    'severity_level' => $item['severity_level'],
                    'status' => $item['status'],
                    'incident_date' => $incidentDate,
                    'source' => $item['source'],
                ]);
            }
        }

        // Tambahkan beberapa data acak tambahan untuk memperbanyak dataset
        $this->createAdditionalRandomData($categories, $provinces);

        echo "MonitoringDataSeeder selesai dijalankan\n";
    }

    private function createAdditionalRandomData($categories, $provinces)
    {
        $severityLevels = ['low', 'medium', 'high', 'critical'];
        $statuses = ['active', 'resolved', 'monitoring', 'archived'];

        $randomTitles = [
            'keamanan' => [
                'Aksi Vandalisme di Fasilitas Publik',
                'Kecelakaan Lalu Lintas Beruntun',
                'Kebakaran Hutan dan Lahan',
                'Gempa Bumi Skala Ringan',
                'Ancaman Bom Palsu',
                'Penipuan Online Berkedok Investasi',
            ],
            'ideologi' => [
                'Kampanye Anti-Pancasila',
                'Penyebaran Paham Intoleransi',
                'Gerakan Anti-NKRI',
                'Propaganda Asing',
                'Ajaran Sesat',
            ],
            'politik' => [
                'Kampanye Hitam Jelang Pemilu',
                'Konflik Internal Partai',
                'Politik Uang di Pilkades',
                'Unjuk Rasa Menolak Kebijakan',
                'Sengketa Batas Wilayah',
            ],
            'ekonomi' => [
                'Kelangkaan BBM Subsidi',
                'Penurunan Daya Beli Masyarakat',
                'Gagal Panen Akibat Cuaca',
                'Tutupnya Pabrik Besar',
                'Kenaikan Tarif Listrik',
            ],
            'sosial-budaya' => [
                'Konflik Adat vs Modernisasi',
                'Degradasi Bahasa Daerah',
                'Kenakalan Remaja Meningkat',
                'Kesenjangan Digital',
                'Pencemaran Lingkungan',
            ],
        ];

        foreach ($categories as $category) {
            $categorySlug = $category->slug;
            $titles = $randomTitles[$categorySlug] ?? ['Insiden '.$category->name];

            // Buat 3-5 data tambahan per kategori
            for ($i = 0; $i < rand(3, 5); $i++) {
                $randomProvince = $provinces->random();
                $kabupatenKota = KabupatenKota::where('provinsi_id', $randomProvince->id)->inRandomOrder()->first();
                $kecamatan = $kabupatenKota ? Kecamatan::where('kabupaten_kota_id', $kabupatenKota->id)->inRandomOrder()->first() : null;
                $subCategory = SubCategory::where('category_id', $category->id)->inRandomOrder()->first();

                if (! $kabupatenKota || ! $kecamatan || ! $subCategory) {
                    continue;
                }

                $title = $titles[array_rand($titles)];
                $latitude = rand(-11000, 6000) / 1000;
                $longitude = rand(95000, 141000) / 1000;
                $incidentDate = Carbon::now()->subDays(rand(1, 90));

                MonitoringData::create([
                    'provinsi_id' => $randomProvince->id,
                    'kabupaten_kota_id' => $kabupatenKota->id,
                    'kecamatan_id' => $kecamatan->id,
                    'category_id' => $category->id,
                    'sub_category_id' => $subCategory->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'title' => $title,
                    'description' => 'Deskripsi untuk '.$title.' di wilayah '.$randomProvince->nama,
                    'jumlah_terdampak' => rand(1, 100), // Random jumlah terdampak 1-100 orang
                    'additional_data' => [
                        'reporter' => 'Masyarakat',
                        'verified' => rand(0, 1) == 1,
                    ],
                    'severity_level' => $severityLevels[array_rand($severityLevels)],
                    'status' => $statuses[array_rand($statuses)],
                    'incident_date' => $incidentDate,
                    'source' => 'Laporan Masyarakat',
                ]);
            }
        }
    }
}
