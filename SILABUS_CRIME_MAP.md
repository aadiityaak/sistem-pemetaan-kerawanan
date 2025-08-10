# SILABUS APLIKASI CRIME MAP

## Platform Pemetaan Data Kriminalitas Indonesia

---

## 1. PENDAHULUAN

### 1.1 Deskripsi Umum

Crime Map adalah aplikasi web modern berbasis Laravel 11 dan Vue 3 yang dirancang untuk memvisualisasikan, menganalisis, dan memonitor data kriminalitas di seluruh wilayah Indonesia. Aplikasi ini menggunakan peta interaktif untuk memberikan insight mendalam tentang distribusi kejahatan berdasarkan geografis, kategori, dan Tingkat Resiko.

### 1.2 Tujuan Pembelajaran

Setelah mempelajari aplikasi ini, peserta diharapkan dapat:

- Memahami arsitektur aplikasi web modern dengan tech stack Laravel-Vue-TypeScript
- Menguasai implementasi sistem pemetaan interaktif menggunakan Leaflet.js
- Memahami pengelolaan data geografis hierarkis Indonesia (Provinsi → Kabupaten/Kota → Kecamatan)
- Menerapkan sistem kategorisasi dan monitoring data yang fleksibel
- Mengimplementasikan sistem deployment untuk shared hosting

### 1.3 Target Audiens

- Web Developer (Intermediate to Advanced)
- Full Stack Developer
- Data Analyst/Scientist
- Geographic Information System (GIS) Developer
- Laravel & Vue.js Enthusiast

---

## 2. SPESIFIKASI TEKNIS

### 2.1 Tech Stack Utama

- **Backend Framework**: Laravel 11 (PHP 8.3+)
- **Frontend Framework**: Vue 3 + TypeScript
- **Database**: MySQL/MariaDB + SQLite (development)
- **CSS Framework**: Tailwind CSS
- **Build Tool**: Vite dengan SSR support
- **Maps Library**: Leaflet.js untuk pemetaan interaktif
- **UI Components**: Radix Vue (shadcn-vue)
- **HTTP Client**: Inertia.js untuk SPA experience

### 2.2 Dependencies Backend

```php
- Laravel 11 (Core Framework)
- Inertia.js PHP Adapter
- Laravel Breeze (Authentication Scaffolding)
- Laravel Pint (Code Style Fixer)
- PHPUnit (Testing Framework)
```

### 2.3 Dependencies Frontend

```json
- Vue 3 (Progressive Framework)
- TypeScript (Type Safety)
- Tailwind CSS (Utility-First CSS)
- Leaflet.js (Interactive Maps)
- Radix Vue (Accessible UI Components)
- Lucide Vue (Icon Library)
- Vue Router (Client-side Routing)
- Vite (Build Tool & Dev Server)
```

### 2.4 Development Tools

- **Linting**: ESLint dengan TypeScript support
- **Code Formatting**: Prettier + Laravel Pint
- **Testing**: PHPUnit (Backend) + Jest/Vitest (Frontend)
- **CI/CD**: GitHub Actions workflow
- **Package Management**: Composer (PHP) + npm/bun (JavaScript)

---

## 3. ARSITEKTUR APLIKASI

### 3.1 Struktur Database

**Tabel Utama:**

- `users` - Manajemen pengguna dan autentikasi
- `provinsi` - Data 38 provinsi Indonesia
- `kabupaten_kota` - Data 514+ kabupaten/kota
- `kecamatan` - Data 7,288+ kecamatan
- `categories` - Kategori monitoring (Ideologi, Politik, Ekonomi, Sosial Budaya, Keamanan)
- `sub_categories` - Sub-kategori detail per kategori
- `monitoring_data` - Data inti kejadian kriminal
- `app_settings` - Konfigurasi aplikasi dinamis

**Relasi Database:**

```
Provinsi (1:N) → KabupatenKota (1:N) → Kecamatan (1:N) → MonitoringData
Category (1:N) → SubCategory (1:N) → MonitoringData
```

### 3.2 Pola Arsitektur

- **MVC Pattern**: Laravel Controller-Model-View
- **Repository Pattern**: Service layer untuk business logic
- **Component-Based Architecture**: Vue 3 Composition API
- **API-First Design**: RESTful endpoints dengan JSON responses

### 3.3 File Structure Overview

```
crime-map/
├── app/
│   ├── Http/Controllers/     # API & Web Controllers
│   ├── Models/              # Eloquent Models
│   └── Services/            # Business Logic Services
├── database/
│   ├── migrations/          # Database Schema
│   ├── seeders/            # Sample Data
│   └── data/               # Indonesian Geographic Data
├── resources/
│   ├── js/                 # Vue 3 + TypeScript Frontend
│   └── css/                # Tailwind CSS Styles
└── public/                 # Static Assets & Entry Point
```

---

## 4. FITUR UTAMA APLIKASI

### 4.1 Sistem Autentikasi

- **Registration & Login**: Form pendaftaran dan masuk pengguna
- **Email Verification**: Verifikasi email otomatis
- **Password Reset**: Reset password via email
- **Remember Me**: Session persistence
- **Profile Management**: Update profil dan password

### 4.2 Dashboard & Analytics

- **Interactive Dashboard**: Overview statistik real-time
- **Filter by Category**: Filter data berdasarkan kategori
- **Geographic Statistics**: Statistik per provinsi, kabupaten, kecamatan
- **Severity Analysis**: Analisis Tingkat Resiko (Low, Medium, High, Critical)
- **Status Monitoring**: Tracking status (Active, Resolved, Monitoring, Archived)

### 4.3 Pemetaan Interaktif

- **Leaflet.js Integration**: Peta interaktif dengan zoom dan pan
- **Marker Clustering**: Pengelompokan marker untuk performa optimal
- **Custom Markers**: Marker berbeda per kategori dan Tingkat Resiko
- **Popup Information**: Detail lengkap kejadian dalam popup
- **Layer Control**: Toggle visibility berbagai layer data

### 4.4 Manajemen Data Geografis

**Hierarki Administrasi:**

- **38 Provinsi** dengan data lengkap
- **514+ Kabupaten/Kota** terstruktur per provinsi
- **7,288+ Kecamatan** dengan relasi ke kabupaten/kota
- **Dynamic Filtering**: Filter lokasi berjenjang (Provinsi → Kabupaten → Kecamatan)

### 4.5 Sistem Kategorisasi

**5 Kategori Utama:**

1. **Ideologi** - Monitoring paham dan ideologi
2. **Politik** - Dinamika politik dan pemerintahan
3. **Ekonomi** - Aspek ekonomi dan keuangan
4. **Sosial Budaya** - Isu sosial dan budaya
5. **Keamanan** - Aspek keamanan dan kriminalitas

**Sub-Kategori Fleksibel:**

- Setiap kategori memiliki multiple sub-kategori
- Icon dan color coding untuk visual identification
- Active/Inactive status management
- Sort order untuk prioritas tampilan

### 4.6 Monitoring Data Management

**Data Entry Features:**

- **Geographic Input**: Latitude/longitude dengan map picker
- **Category Selection**: Dropdown kategori dan sub-kategori
- **Severity Levels**: 4 Tingkat Resiko (Low → Critical)
- **Status Tracking**: Lifecycle management (Active → Resolved)
- **Additional Data**: JSON field untuk data custom per kategori
- **Source Attribution**: Tracking sumber data

**Validation & Security:**

- Server-side validation untuk semua input
- Geographic bounds validation
- Required field enforcement
- XSS protection
- CSRF token protection

### 4.7 Search & Filtering System

- **Global Search**: Pencarian across title, description, lokasi
- **Advanced Filters**:
    - Filter by kategori dan sub-kategori
    - Filter by severity level
    - Filter by status
    - Filter by date range
    - Filter by geographic location
- **Pagination**: Performance-optimized dengan Laravel pagination
- **Export Capabilities**: Data export untuk analisis external

### 4.8 Settings Management

**Dynamic Application Settings:**

- **General Settings**: Site name, description, contact info
- **Appearance**: Logo, favicon, color schemes
- **Map Configuration**: Default center, zoom levels
- **Email Settings**: SMTP configuration
- **File Upload Settings**: Size limits, allowed formats

---

## 5. KOMPONEN TEKNIS DETAIL

### 5.1 Backend Architecture

#### 5.1.1 Controllers

```php
- DashboardController: Analytics dan dashboard utama
- MonitoringDataController: CRUD data monitoring
- ProvinsiController: Manajemen data provinsi
- KabupatenKotaController: Manajemen kabupaten/kota
- KecamatanController: Manajemen kecamatan
- CategoryController: Manajemen kategori
- SubCategoryController: Manajemen sub-kategori
- AppSettingController: Konfigurasi aplikasi
- Auth/*: Authentication controllers
```

#### 5.1.2 Models & Relationships

```php
User: Basic user model dengan authentication
Provinsi: hasMany → KabupatenKota, MonitoringData
KabupatenKota: belongsTo → Provinsi, hasMany → Kecamatan
Kecamatan: belongsTo → KabupatenKota, hasMany → MonitoringData
Category: hasMany → SubCategory, MonitoringData
SubCategory: belongsTo → Category, hasMany → MonitoringData
MonitoringData: belongsTo → semua models above
AppSetting: Key-value configuration storage
```

#### 5.1.3 Services Layer

```php
SettingsService: Business logic untuk app settings
- Dynamic setting creation/update
- File upload handling
- Default value management
- Cache integration untuk performance
```

### 5.2 Frontend Architecture

#### 5.2.1 Vue 3 Components Structure

```typescript
/pages/
  - Dashboard.vue: Main analytics dashboard
  - MonitoringData/: CRUD pages untuk data monitoring
  - Categories/: Manajemen kategori
  - SubCategories/: Manajemen sub-kategori
  - Settings/: Application settings pages
  - Auth/: Authentication pages

/components/
  - ui/: Reusable UI components (buttons, forms, etc.)
  - maps/: Map-related components
  - charts/: Analytics dan visualization components

/layouts/
  - AppLayout.vue: Main application layout
  - AuthLayout.vue: Authentication layout
  - GuestLayout.vue: Public pages layout
```

#### 5.2.2 TypeScript Integration

- **Type Safety**: Full TypeScript coverage untuk components
- **Interface Definitions**: Type definitions untuk API responses
- **Props Typing**: Strongly typed component props
- **Composables**: Reusable logic dengan TypeScript support

#### 5.2.3 State Management

- **Inertia.js**: Seamless SPA experience tanpa complex state management
- **Vue Composition API**: Local component state dengan reactivity
- **Props Drilling**: Data flow dari parent ke child components

### 5.3 Database Design

#### 5.3.1 Geographic Data Structure

```sql
-- Hierarchical Indonesian Administrative Data
provinsi(id, nama) → 38 records
kabupaten_kota(id, provinsi_id, nama) → 514+ records
kecamatan(id, kabupaten_kota_id, nama) → 7,288+ records

-- Indexing Strategy
INDEX on foreign keys untuk join performance
INDEX on search fields (nama) untuk query speed
```

#### 5.3.2 Monitoring Data Schema

```sql
monitoring_data:
  - Geographic: provinsi_id, kabupaten_kota_id, kecamatan_id, lat, lng
  - Categorization: category_id, sub_category_id
  - Content: title, description, jumlah_terdampak
  - Metadata: severity_level, status, incident_date, source
  - Flexibility: additional_data (JSON) untuk custom fields
```

#### 5.3.3 Configuration System

```sql
app_settings:
  - key: Unique setting identifier
  - value: Setting value (TEXT untuk flexibility)
  - type: Data type (text, image, file, boolean, number)
  - group: Logical grouping (general, appearance, email)
  - label/description: UI metadata
```

---

## 6. IMPLEMENTASI PEMETAAN

### 6.1 Leaflet.js Integration

```typescript
// Map initialization dengan custom configuration
const map = L.map('mapContainer', {
    center: [-2.5489, 118.0149], // Indonesia center
    zoom: 5,
    zoomControl: true,
    attributionControl: true,
});

// Tile layer configuration
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 18,
}).addTo(map);
```

### 6.2 Marker Management

```typescript
// Dynamic marker creation berdasarkan kategori dan severity
const createMarker = (data: MonitoringData) => {
    const icon = getIconByCategory(data.category, data.severity_level);
    const marker = L.marker([data.latitude, data.longitude], { icon });

    // Custom popup dengan data lengkap
    marker.bindPopup(createPopupContent(data));
    return marker;
};

// Marker clustering untuk performance
const markerCluster = L.markerClusterGroup({
    chunkedLoading: true,
    chunkInterval: 200,
    chunkDelay: 50,
});
```

### 6.3 Interactive Features

- **Click Events**: Detail popup saat marker diklik
- **Zoom Controls**: Custom zoom controls dengan level restrictions
- **Layer Toggle**: Show/hide berbagai kategori data
- **Search Integration**: Map focus berdasarkan search results
- **Responsive Design**: Map adaptation untuk mobile devices

---

## 7. SISTEM DEPLOYMENT

### 7.1 Development Environment

```bash
# Local development setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

### 7.2 Production Deployment

**Automated Deployment Script:**

- `scripts/build-deployment.js`: Comprehensive packaging solution
- Environment optimization untuk production
- Asset compilation dan minification
- Database migration automation
- Symlink creation untuk shared hosting

### 7.3 Shared Hosting Compatibility

**Advanced Symlink Creation:**

```php
public/create-symlink-advanced.php:
- Multiple symlink creation methods
- Fallback strategies untuk hosting restrictions
- Error handling dan logging
- Compatibility dengan berbagai hosting providers
```

### 7.4 Performance Optimizations

- **Laravel Optimization**: Route caching, config caching, view caching
- **Frontend Optimization**: Code splitting, lazy loading, tree shaking
- **Database Optimization**: Query optimization, indexing strategy
- **CDN Integration**: Static asset delivery optimization

---

## 8. TESTING & QUALITY ASSURANCE

### 8.1 Backend Testing

```php
PHPUnit Test Coverage:
- Feature Tests: End-to-end functionality testing
- Unit Tests: Individual component testing
- Database Testing: Migration dan seeder validation
- API Testing: Endpoint response validation
```

### 8.2 Frontend Testing

```typescript
Jest/Vitest Setup:
- Component Testing: Vue component behavior
- Integration Testing: Component interaction
- E2E Testing: User workflow validation
- TypeScript Testing: Type safety validation
```

### 8.3 Code Quality Tools

- **ESLint**: JavaScript/TypeScript linting dengan custom rules
- **Prettier**: Code formatting consistency
- **Laravel Pint**: PHP code style fixing
- **GitHub Actions**: Automated CI/CD pipeline

### 8.4 Quality Standards

- **PSR Standards**: PHP coding standards compliance
- **Vue Style Guide**: Official Vue.js style guide adherence
- **TypeScript Strict Mode**: Maximum type safety enforcement
- **Performance Monitoring**: Page load time optimization

---

## 9. SECURITY IMPLEMENTATION

### 9.1 Authentication & Authorization

- **Laravel Breeze**: Secure authentication scaffolding
- **Email Verification**: Account activation requirement
- **Password Hashing**: Bcrypt dengan salt
- **CSRF Protection**: Token-based request validation
- **Session Security**: Secure session configuration

### 9.2 Data Protection

- **Input Validation**: Server-side validation untuk semua input
- **SQL Injection Prevention**: Eloquent ORM dengan parameter binding
- **XSS Protection**: Output escaping dan content sanitization
- **File Upload Security**: Type validation dan storage isolation

### 9.3 API Security

- **Rate Limiting**: Request throttling untuk abuse prevention
- **CORS Configuration**: Cross-origin request control
- **HTTP Headers**: Security headers implementation
- **Error Handling**: Information disclosure prevention

---

## 10. PEMBELAJARAN DAN IMPLEMENTASI

### 10.1 Prerequisites

**Technical Skills Required:**

- PHP (Intermediate): Laravel framework understanding
- JavaScript/TypeScript (Intermediate): Modern ES6+ syntax
- Vue.js (Basic to Intermediate): Component-based development
- MySQL/Database (Basic): SQL queries dan database design
- HTML/CSS (Basic): Web markup dan styling fundamentals

**Tools & Environment:**

- PHP 8.3+ dengan extensions: mbstring, xml, fileinfo, openssl
- Node.js 18+ dengan npm/yarn/bun package manager
- MySQL 8.0+ atau MariaDB 10.3+
- Git untuk version control
- Code editor dengan PHP/Vue support (VSCode recommended)

### 10.2 Learning Path

**Phase 1: Foundation (Week 1-2)**

- Laravel basics: routing, controllers, models, views
- Vue 3 fundamentals: composition API, components, reactivity
- Database design: migrations, seeders, relationships
- Development environment setup

**Phase 2: Core Features (Week 3-4)**

- Authentication implementation dengan Laravel Breeze
- CRUD operations untuk geographic data
- Vue component development dengan TypeScript
- API integration dengan Inertia.js

**Phase 3: Advanced Features (Week 5-6)**

- Leaflet.js map integration
- Complex filtering dan search functionality
- File upload dan settings management
- Performance optimization techniques

**Phase 4: Deployment & Testing (Week 7-8)**

- Production deployment configuration
- Testing implementation (PHPUnit, Jest/Vitest)
- Security hardening
- Performance monitoring dan optimization

### 10.3 Hands-on Exercises

**Exercise 1: Geographic Data Management**

- Implement CRUD operations untuk provinsi/kabupaten/kecamatan
- Create dynamic dropdown filtering (Provinsi → Kabupaten → Kecamatan)
- Add search functionality dengan pagination

**Exercise 2: Map Integration**

- Integrate Leaflet.js dengan Vue 3 components
- Implement marker creation dan clustering
- Add interactive popup dengan data display

**Exercise 3: Category System**

- Build flexible category/sub-category management
- Implement drag-and-drop sorting
- Add color-coded visualization

**Exercise 4: Data Monitoring**

- Create monitoring data entry forms
- Implement real-time filtering dan search
- Add export functionality untuk data analysis

---

## 11. TROUBLESHOOTING & FAQ

### 11.1 Common Development Issues

**Database Connection Problems:**

```bash
# Check database configuration
php artisan config:cache
php artisan migrate:status

# Reset database
php artisan migrate:fresh --seed
```

**Node.js Build Issues:**

```bash
# Clear npm cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install

# Vite development server issues
npm run dev --host 0.0.0.0
```

**Laravel Permission Issues:**

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 11.2 Deployment Troubleshooting

**Shared Hosting Symlink Issues:**

- Use `public/create-symlink-advanced.php` script
- Check hosting provider symlink support
- Alternative: file copying fallback method
- Contact hosting support untuk symlink enabling

**Database Migration Problems:**

```bash
# Production migration
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11.3 Performance Issues

**Slow Map Loading:**

- Implement marker clustering
- Use pagination untuk large datasets
- Optimize database queries dengan indexing
- Consider CDN untuk static assets

**Database Query Optimization:**

```sql
-- Add indexes untuk frequently queried columns
CREATE INDEX idx_monitoring_data_location ON monitoring_data(provinsi_id, kabupaten_kota_id, kecamatan_id);
CREATE INDEX idx_monitoring_data_category ON monitoring_data(category_id, sub_category_id);
```

---

## 12. RESOURCES & REFERENCES

### 12.1 Official Documentation

- **Laravel 11**: https://laravel.com/docs/11.x
- **Vue 3**: https://vuejs.org/guide/
- **TypeScript**: https://www.typescriptlang.org/docs/
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Leaflet.js**: https://leafletjs.com/reference.html
- **Inertia.js**: https://inertiajs.com/

### 12.2 Community Resources

- **Laravel Indonesia**: https://laravel.id/
- **Vue.js Indonesia**: https://vuejs.id/
- **Stack Overflow**: Tagged questions untuk troubleshooting
- **GitHub Issues**: Project-specific problem solving

### 12.3 Additional Learning Materials

- **Laravel Bootcamp**: https://bootcamp.laravel.com/
- **Vue.js Mastery**: https://www.vuemastery.com/
- **TypeScript Handbook**: Microsoft official documentation
- **GIS Programming**: Geographic information system development

### 12.4 Tools & Extensions

**VSCode Extensions:**

- Laravel Extension Pack
- Vue Language Features (Volar)
- TypeScript Vue Plugin (Volar)
- Tailwind CSS IntelliSense
- PHP Intelephense

**Browser Developer Tools:**

- Vue.js devtools extension
- Laravel Telescope untuk debugging
- Browser network tab untuk API monitoring

---

## 13. KESIMPULAN

Crime Map Application merupakan implementasi komprehensif dari modern web development practices dengan focus pada geographic data visualization dan crime monitoring. Aplikasi ini mendemonstrasikan:

### 13.1 Technical Excellence

- **Full-Stack Integration**: Seamless Laravel-Vue-TypeScript integration
- **Scalable Architecture**: Modular design untuk future extensions
- **Performance Optimization**: Efficient database design dan frontend optimization
- **Security Implementation**: Comprehensive security measures

### 13.2 Real-World Application

- **Geographic Data Management**: Complete Indonesian administrative hierarchy
- **Interactive Visualization**: User-friendly map interface
- **Flexible Categorization**: Adaptable monitoring categories
- **Production-Ready**: Deployment-optimized dengan shared hosting support

### 13.3 Learning Outcomes

Dengan mempelajari aplikasi ini, developer akan mendapatkan:

- Modern PHP development dengan Laravel 11
- Vue 3 + TypeScript frontend development
- Geographic Information System (GIS) integration
- Full-stack application deployment
- Security best practices implementation
- Performance optimization techniques

### 13.4 Future Enhancements

Potential improvements untuk development lanjutan:

- **Real-time Updates**: WebSocket integration untuk live data updates
- **Mobile Application**: React Native atau Flutter companion app
- **Advanced Analytics**: Machine learning untuk crime prediction
- **API Integration**: Integration dengan external crime databases
- **Multi-language Support**: Internationalization untuk broader usage

---

**Dokumen ini disusun sebagai panduan lengkap untuk memahami, mengembangkan, dan mengimplementasikan Crime Map Application. Untuk pertanyaan atau kontribusi, silakan merujuk ke dokumentasi project dan community resources yang tersedia.**
