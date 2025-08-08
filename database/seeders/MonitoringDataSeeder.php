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

        // Data sample untuk setiap kategori sesuai CSV
        $sampleData = [
            // KEAMANAN - Security Related Data
            [
                'category' => $keamananCategory,
                'data' => [
                    [
                        'title' => 'Ancaman Bom di Stasiun Kereta',
                        'description' => 'Laporan ancaman bom melalui telepon di Stasiun Gambir, Jakarta. Tim Densus 88 dan Gegana melakukan sterilisasi area.',
                        'jumlah_terdampak' => 500,
                        'severity_level' => 'critical',
                        'status' => 'resolved',
                        'source' => 'Densus 88',
                        'sub_category_name' => 'Teror',
                        'additional_data' => [
                            'response_time' => '10 minutes',
                            'evacuated' => 500,
                            'threat_type' => 'Ancaman Bom',
                        ],
                    ],
                    [
                        'title' => 'Ledakan BOM di Area Publik',
                        'description' => 'Ledakan bom rakitan di area pasar tradisional menyebabkan korban jiwa dan luka-luka.',
                        'jumlah_terdampak' => 15,
                        'severity_level' => 'critical',
                        'status' => 'active',
                        'source' => 'Polda Metro',
                        'sub_category_name' => 'Teror',
                        'additional_data' => [
                            'fatalities' => 3,
                            'injured' => 12,
                            'bomb_type' => 'Rakitan',
                        ],
                    ],
                    [
                        'title' => 'Penangkapan Kelompok KKB',
                        'description' => 'TNI berhasil menangkap anggota Kelompok Kriminal Bersenjata (KKB) di Papua yang melakukan serangan terhadap aparat.',
                        'jumlah_terdampak' => 8,
                        'severity_level' => 'high',
                        'status' => 'resolved',
                        'source' => 'TNI',
                        'sub_category_name' => 'Keamanan Negara',
                        'additional_data' => [
                            'arrested' => 5,
                            'weapons_seized' => 8,
                            'location' => 'Papua',
                        ],
                    ],
                    [
                        'title' => 'Bentrok TNI-POLRI',
                        'description' => 'Insiden bentrokan antara anggota TNI dan Polri akibat kesalahpahaman dalam operasi keamanan.',
                        'jumlah_terdampak' => 6,
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'Mabes TNI-Polri',
                        'sub_category_name' => 'Keamanan Negara',
                        'additional_data' => [
                            'tni_involved' => 3,
                            'polri_involved' => 3,
                            'resolution' => 'Mediasi internal',
                        ],
                    ],
                    [
                        'title' => 'RMS (Republik Maluku Selatan) Propaganda',
                        'description' => 'Penyebaran propaganda RMS melalui media sosial dan pamflet di wilayah Maluku.',
                        'jumlah_terdampak' => 1000,
                        'severity_level' => 'medium',
                        'status' => 'active',
                        'source' => 'TNI-Polri Maluku',
                        'sub_category_name' => 'Keamanan Negara',
                        'additional_data' => [
                            'pamphlets_seized' => 500,
                            'social_media_posts' => 50,
                            'suspects' => 3,
                        ],
                    ],
                ],
            ],

            // IDEOLOGI - Ideology Related Data
            [
                'category' => $ideologiCategory,
                'data' => [
                    [
                        'title' => 'Aktivitas Front Pembela Islam (FPI)',
                        'description' => 'Kegiatan FPI yang melakukan sweeping terhadap tempat hiburan malam dengan dalil penegakan syariat.',
                        'jumlah_terdampak' => 200,
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'Polda Metro',
                        'sub_category_name' => 'Ideologi Kanan',
                        'additional_data' => [
                            'locations_targeted' => 5,
                            'businesses_affected' => 12,
                            'members_involved' => 50,
                        ],
                    ],
                    [
                        'title' => 'Jamaah Islamiah (JI) Cell Discovery',
                        'description' => 'Penggerebekan sel Jamaah Islamiah yang merencanakan aksi teror di Jakarta.',
                        'jumlah_terdampak' => 10,
                        'severity_level' => 'critical',
                        'status' => 'resolved',
                        'source' => 'Densus 88',
                        'sub_category_name' => 'Ideologi Kanan',
                        'additional_data' => [
                            'arrested' => 5,
                            'weapons_found' => 8,
                            'bomb_materials' => 'Chemical components',
                        ],
                    ],
                    [
                        'title' => 'Serikat Buruh Radikal Demo',
                        'description' => 'Demonstrasi serikat buruh dengan ideology kiri yang menuntut perubahan sistem ekonomi kapitalis.',
                        'jumlah_terdampak' => 500,
                        'severity_level' => 'medium',
                        'status' => 'resolved',
                        'source' => 'Polda',
                        'sub_category_name' => 'Ideologi Kiri',
                        'additional_data' => [
                            'protesters' => 500,
                            'duration' => '6 hours',
                            'demands' => 'Economic system change',
                        ],
                    ],
                    [
                        'title' => 'PKI Symbol Graffiti Campaign',
                        'description' => 'Penemuan grafiti dengan simbol PKI dan ajakan untuk kebangkitan komunisme di berbagai tempat.',
                        'jumlah_terdampak' => 1000,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Polres',
                        'sub_category_name' => 'Ideologi Kiri',
                        'additional_data' => [
                            'graffiti_locations' => 25,
                            'symbols_found' => 'Palu arit, bintang merah',
                            'suspects_identified' => 3,
                        ],
                    ],
                    [
                        'title' => 'Mujahidin Indonesia Timur (MIT) Activities',
                        'description' => 'Aktivitas kelompok MIT di Poso yang melakukan penyerangan terhadap aparat keamanan.',
                        'jumlah_terdampak' => 15,
                        'severity_level' => 'critical',
                        'status' => 'active',
                        'source' => 'TNI-Polri',
                        'sub_category_name' => 'Ideologi Kanan',
                        'additional_data' => [
                            'casualties' => 2,
                            'location' => 'Poso, Sulawesi Tengah',
                            'operations' => 'Ongoing pursuit',
                        ],
                    ],
                ],
            ],

            // POLITIK - Political Related Data
            [
                'category' => $politikCategory,
                'data' => [
                    [
                        'title' => 'Sengketa Hasil Pemilu Legislatif',
                        'description' => 'Perselisihan hasil penghitungan suara pemilu yang menyebabkan protes dari berbagai partai politik.',
                        'jumlah_terdampak' => 1000,
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'KPU',
                        'sub_category_name' => 'Dalam Negeri',
                        'additional_data' => [
                            'disputed_votes' => 10000,
                            'parties_involved' => 5,
                            'recounts_requested' => 3,
                        ],
                    ],
                    [
                        'title' => 'Konflik Batas Wilayah Pilkada',
                        'description' => 'Sengketa hasil pilkada tingkat kabupaten yang memicu konflik antar pendukung.',
                        'jumlah_terdampak' => 500,
                        'severity_level' => 'medium',
                        'status' => 'active',
                        'source' => 'Bawaslu',
                        'sub_category_name' => 'Dalam Negeri',
                        'additional_data' => [
                            'candidates_involved' => 2,
                            'vote_margin' => 500,
                            'legal_challenges' => 2,
                        ],
                    ],
                    [
                        'title' => 'Hubungan Diplomatik dengan Malaysia',
                        'description' => 'Ketegangan diplomatik dengan Malaysia terkait isu TKI dan batas wilayah laut.',
                        'jumlah_terdampak' => 5000,
                        'severity_level' => 'medium',
                        'status' => 'monitoring',
                        'source' => 'Kemlu',
                        'sub_category_name' => 'Luar Negeri',
                        'additional_data' => [
                            'tki_affected' => 2000,
                            'diplomatic_meetings' => 3,
                            'issues' => 'Border, labor rights',
                        ],
                    ],
                    [
                        'title' => 'Isu Pemindahan Ibu Kota',
                        'description' => 'Pro kontra kebijakan pemindahan ibu kota dari Jakarta ke IKN Nusantara.',
                        'jumlah_terdampak' => 100000,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Kemendagri',
                        'sub_category_name' => 'Isu Menonjol',
                        'additional_data' => [
                            'budget_allocated' => 466700000000000,
                            'affected_residents' => 50000,
                            'completion_target' => 2045,
                        ],
                    ],
                ],
            ],

            // EKONOMI - Economic Related Data
            [
                'category' => $ekonomiCategory,
                'data' => [
                    [
                        'title' => 'Lonjakan Harga Beras dan Sagu',
                        'description' => 'Kenaikan drastis harga beras mencapai 25% dan sagu 30% akibat gagal panen dan gangguan distribusi.',
                        'jumlah_terdampak' => 50000,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Bulog',
                        'sub_category_name' => 'Harga Sembako',
                        'additional_data' => [
                            'rice_price_increase' => '25%',
                            'sagu_price_increase' => '30%',
                            'affected_regions' => 15,
                        ],
                    ],
                    [
                        'title' => 'Krisis Minyak Goreng Nasional',
                        'description' => 'Kelangkaan dan kenaikan harga minyak goreng hingga 200% menyebabkan keresahan masyarakat.',
                        'jumlah_terdampak' => 100000,
                        'severity_level' => 'critical',
                        'status' => 'active',
                        'source' => 'Kemendag',
                        'sub_category_name' => 'Harga Sembako',
                        'additional_data' => [
                            'price_increase' => '200%',
                            'shortage_duration' => '3 months',
                            'government_intervention' => 'Subsidy program',
                        ],
                    ],
                    [
                        'title' => 'Penurunan UMR Daerah',
                        'description' => 'Beberapa daerah menurunkan UMR akibat tekanan ekonomi dan permintaan pengusaha.',
                        'jumlah_terdampak' => 25000,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Disnaker',
                        'sub_category_name' => 'Index Pendapatan masyarakat',
                        'additional_data' => [
                            'regions_affected' => 8,
                            'umr_decrease' => '15%',
                            'workers_impacted' => 25000,
                        ],
                    ],
                    [
                        'title' => 'Kesenjangan Akses Pendidikan',
                        'description' => 'Disparitas akses pendidikan antara daerah urban dan rural semakin melebar.',
                        'jumlah_terdampak' => 75000,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Kemendikbud',
                        'sub_category_name' => 'Kesenjangan Sosial',
                        'additional_data' => [
                            'urban_schools' => 500,
                            'rural_schools' => 200,
                            'teacher_ratio' => '1:35',
                        ],
                    ],
                    [
                        'title' => 'OTT KPK Korupsi Bansos',
                        'description' => 'Operasi Tangkap Tangan KPK terhadap pejabat yang korupsi dana bantuan sosial COVID-19.',
                        'jumlah_terdampak' => 10000,
                        'severity_level' => 'critical',
                        'status' => 'resolved',
                        'source' => 'KPK',
                        'sub_category_name' => 'Korupsi',
                        'additional_data' => [
                            'officials_arrested' => 5,
                            'corruption_value' => 50000000000,
                            'affected_families' => 10000,
                        ],
                    ],
                    [
                        'title' => 'Inflasi Nasional Melonjak',
                        'description' => 'Inflasi nasional mencapai 7.5% tertinggi dalam 10 tahun terakhir.',
                        'jumlah_terdampak' => 270000000,
                        'severity_level' => 'critical',
                        'status' => 'active',
                        'source' => 'BPS',
                        'sub_category_name' => 'Ekonomi Asing',
                        'additional_data' => [
                            'inflation_rate' => '7.5%',
                            'main_drivers' => 'Energy, food prices',
                            'bi_rate_response' => '6.0%',
                        ],
                    ],
                ],
            ],

            // SOSIAL BUDAYA - Social Cultural Data
            [
                'category' => $sosbudCategory,
                'data' => [
                    [
                        'title' => 'Konflik AMP (Asosiasi Papua Merdeka)',
                        'description' => 'Kegiatan organisasi masyarakat AMP yang menuntut referendum kemerdekaan Papua.',
                        'jumlah_terdampak' => 5000,
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'TNI-Polri Papua',
                        'sub_category_name' => 'Ormas',
                        'additional_data' => [
                            'members_identified' => 200,
                            'protests_organized' => 5,
                            'security_measures' => 'Increased',
                        ],
                    ],
                    [
                        'title' => 'Banjir Bandang Sulawesi',
                        'description' => 'Banjir bandang akibat hujan deras dan jebolnya tanggul sungai di Sulawesi.',
                        'jumlah_terdampak' => 2500,
                        'severity_level' => 'critical',
                        'status' => 'active',
                        'source' => 'BNPB',
                        'sub_category_name' => 'Bencana Alam',
                        'additional_data' => [
                            'fatalities' => 15,
                            'missing' => 8,
                            'houses_damaged' => 500,
                        ],
                    ],
                    [
                        'title' => 'Gunung Merapi Meletus',
                        'description' => 'Erupsi Gunung Merapi dengan status waspada level III, mengancam penduduk sekitar.',
                        'jumlah_terdampak' => 15000,
                        'severity_level' => 'critical',
                        'status' => 'active',
                        'source' => 'PVMBG',
                        'sub_category_name' => 'Bencana Alam',
                        'additional_data' => [
                            'evacuation_radius' => '7 km',
                            'evacuees' => 3000,
                            'alert_level' => 'Siaga III',
                        ],
                    ],
                    [
                        'title' => 'Demo Menolak Omnibus Law',
                        'description' => 'Unjuk rasa besar-besaran menolak UU Cipta Kerja di berbagai kota.',
                        'jumlah_terdampak' => 50000,
                        'severity_level' => 'medium',
                        'status' => 'resolved',
                        'source' => 'Polda Metro',
                        'sub_category_name' => 'Unjuk rasa',
                        'additional_data' => [
                            'protesters' => 50000,
                            'cities_affected' => 20,
                            'arrests' => 100,
                        ],
                    ],
                    [
                        'title' => 'Konflik Pertambangan vs Adat',
                        'description' => 'Konflik antara perusahaan pertambangan dengan masyarakat adat terkait tanah ulayat.',
                        'jumlah_terdampak' => 800,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Kemendagri',
                        'sub_category_name' => 'Konflik sosial',
                        'additional_data' => [
                            'land_area' => '500 ha',
                            'affected_families' => 200,
                            'mediation_attempts' => 5,
                        ],
                    ],
                    [
                        'title' => 'PHK Massal Industri Tekstil',
                        'description' => 'Pemutusan hubungan kerja massal di industri tekstil akibat penurunan permintaan ekspor.',
                        'jumlah_terdampak' => 5000,
                        'severity_level' => 'high',
                        'status' => 'active',
                        'source' => 'Disnaker',
                        'sub_category_name' => 'PHK',
                        'additional_data' => [
                            'companies_involved' => 8,
                            'workers_laid_off' => 5000,
                            'severance_disputes' => 15,
                        ],
                    ],
                    [
                        'title' => 'Konflik SARA di Ambon',
                        'description' => 'Ketegangan antar kelompok agama di Ambon terkait pembangunan tempat ibadah.',
                        'jumlah_terdampak' => 1000,
                        'severity_level' => 'high',
                        'status' => 'monitoring',
                        'source' => 'Polda Maluku',
                        'sub_category_name' => 'SARA',
                        'additional_data' => [
                            'religious_groups' => 2,
                            'security_personnel' => 200,
                            'dialogue_sessions' => 8,
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
