# Detail Teknologi - Pemetaan Kerawanan

Dokumen ini merinci tumpukan teknologi (tech stack) yang digunakan dalam pengembangan aplikasi **Pemetaan Kerawanan**.

## 🏗️ Arsitektur Utama

Aplikasi ini dibangun menggunakan arsitektur modern **Monolith with a Single Page App (SPA) experience** melalui integrasi Laravel dan Vue.js yang dijembatani oleh Inertia.js.

- **Backend Framework**: [Laravel 12.0](https://laravel.com/) (PHP 8.2+)
- **Frontend Framework**: [Vue 3](https://vuejs.org/) (Composition API)
- **Bridge**: [Inertia.js 2.0](https://inertiajs.com/) (Menghubungkan Laravel & Vue tanpa perlu membuat REST API terpisah)
- **Language**: [TypeScript](https://www.typescriptlang.org/) (Digunakan di seluruh frontend untuk keamanan tipe data)

---

## 🖥️ Frontend Stack

### UI & Styling

- **CSS Framework**: [Tailwind CSS 4.1](https://tailwindcss.com/) - Utility-first CSS framework terbaru.
- **Icons**: [Lucide Vue Next](https://lucide.dev/) - Koleksi ikon SVG yang konsisten dan ringan.
- **Components**: Terinspirasi oleh **shadcn-vue**, menggunakan library seperti `reka-ui`, `clsx`, dan `tailwind-merge` untuk komponen UI yang aksesibel dan dapat dikustomisasi.
- **Animations**: `tw-animate-css` untuk transisi UI yang halus.

### Fitur Lanjutan & Editor

- **Rich Text Editor**: [Tiptap](https://tiptap.dev/) - Headless framework untuk membangun editor teks yang kaya (digunakan untuk deskripsi data).
- **Charts**: [Chart.js](https://www.chartjs.org/) - Visualisasi data statistik kriminalitas dan bencana.

---

## 🗺️ GIS & Mapping (Geospasial)

Fitur utama pemetaan aplikasi ini menggunakan kombinasi teknologi:

- **[Leaflet](https://leafletjs.com/)**: Library JavaScript open-source untuk peta interaktif.
- **[@vue-leaflet/vue-leaflet](https://vue-leaflet.github.io/vue-leaflet/)**: Integrasi Leaflet yang dioptimalkan untuk Vue 3.
- **SVG Mapping**: Penggunaan file SVG (seperti `indonesia.svg`) untuk visualisasi peta wilayah yang ringan dan responsif.

## 🔗 Integrasi Data Pihak Ketiga (External Data)

Aplikasi ini mengintegrasikan berbagai sumber data eksternal melalui mekanisme proxy dan embedding:

### 🛡️ Pemetaan Kerawanan (Pusiknas)

- **Sumber**: [Pusiknas Polri](https://pusiknas.polri.go.id/peta_kriminalitas)
- **Teknologi**: Backend Proxy (`ProxyController.php`)
- **Mekanisme**: Server-side fetching dengan teknik _Content Rewriting_ untuk mengubah URL relatif menjadi absolut, pembersihan header keamanan (X-Frame-Options), dan caching selama 24 jam untuk performa optimal.

### ⚔️ War Monitor (Custom Installation)

- **Sumber**: [Custom World Monitor](https://world.pemetaankerawanan.my.id/)
- **Teknologi**: Self-Hosted Proxy & Script Injection
- **Mekanisme**: Instalasi kustom yang dihosting secara mandiri untuk mengakomodasi kebutuhan data spesifik klien. Menggunakan proxy server-side yang menginjeksi `<base>` tag dan script kustom untuk menangani request `fetch` dan `XMLHttpRequest` dinamis agar data tetap akurat dan relevan.

### 🌋 Data Bencana (BNPB)

- **Sumber**: [GIS BNPB](https://gis.bnpb.go.id/dev/map/)
- **Teknologi**: Iframe Embedding
- **Mekanisme**: Integrasi langsung melalui komponen Vue (`Bnpb.vue`) dengan penanganan status pemuatan (_loading state_) untuk pengalaman pengguna yang lebih baik.

## 🤖 Artificial Intelligence (AI)

Aplikasi ini terintegrasi dengan kecerdasan buatan untuk analisis data:

- **Model**: [Google Gemini 2.0 Flash](https://ai.google.dev/)
- **Implementasi**: `GeminiService.php` yang memungkinkan analisis prediktif terhadap data kriminalitas dan bencana melalui API Google Generative AI.

---

## 📱 Progressive Web App (PWA)

Aplikasi ini dirancang untuk dapat diinstal dan berjalan secara offline (sebagian):

- **Vite PWA Plugin**: Mengelola registrasi Service Worker dan manifest.
- **Service Worker**: Menggunakan `generateSW` dengan strategi `NetworkFirst` untuk navigasi halaman dan caching aset (JS, CSS, Gambar).
- **Manifest**: Mendukung instalasi di perangkat mobile (Android/iOS) dengan ikon yang sesuai.

---

## 📂 Backend & Database

- **Database**: [MySQL](https://www.mysql.com/) - Digunakan sebagai penyimpanan utama data wilayah, kriminalitas, bencana, dan pengaturan aplikasi.
- **ORM**: [Eloquent](https://laravel.com/docs/eloquent) - Memudahkan manipulasi data database menggunakan objek PHP.
- **Excel Support**: [Laravel Excel](https://docs.laravel-excel.com/) - Untuk fitur ekspor/impor data.
- **Authentication**: Laravel Session-based auth dengan dukungan peran (Super Admin, Admin, Admin VIP).

---

## 🛠️ Build & Development Tools

- **Build Tool**: [Vite 7.0](https://vitejs.dev/) - Bundle tool super cepat untuk aset frontend.
- **Runtime & Scripting**: [Bun](https://bun.sh/) - Digunakan untuk menjalankan script deployment kustom (`build-deployment.js`).
- **Linter & Formatter**: ESLint, Prettier, dan Laravel Pint untuk menjaga kualitas kode.
- **Package Manager**: NPM / Bun.

---

## 🚀 Deployment

Aplikasi ini memiliki script deployment khusus untuk lingkungan **Shared Hosting**:

- **Script**: `scripts/build-deployment.js`
- **Output**: Menghasilkan file ZIP yang sudah terstruktur untuk shared hosting (memisahkan folder `laravel-app` dan `public_html` demi keamanan).
- **Optimization**: Melakukan minifikasi aset dan modifikasi otomatis `index.php` untuk menyesuaikan struktur path hosting.

---

_Dokumen ini diperbarui pada 10 Maret 2026._
