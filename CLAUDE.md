# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Crime Map is a Laravel-Vue crime mapping application for Indonesia with interactive Leaflet maps, dashboard analytics, and comprehensive regional data management. The app visualizes crime data on an interactive map of Indonesia with statistics and filtering capabilities.

## Technology Stack

- **Backend**: Laravel 12.x with PHP 8.3+
- **Frontend**: Vue.js 3 with TypeScript, Inertia.js for SPA experience
- **Styling**: Tailwind CSS 4.x with Radix Vue UI components
- **Build**: Vite for fast development and bundling
- **Maps**: Leaflet.js for interactive mapping
- **Database**: SQLite (default) or MySQL

## Common Commands

### Development
```bash
# Start development environment
composer dev              # Starts Laravel server, queue, and Vite dev server concurrently
php artisan serve         # Laravel development server only
npm run dev              # Vite development server only

# Database
php artisan migrate      # Run migrations
php artisan db:seed      # Seed database with Indonesian regional data
php artisan migrate:fresh --seed  # Fresh migration with seeding
```

### Build & Production
```bash
npm run build            # Build frontend assets for production
npm run build:ssr        # Build with SSR support
npm run build:production # Build and create deployment ZIP
```

### Code Quality
```bash
npm run test             # Run all frontend tests (lint, type-check, format check)
npm run test:all         # Frontend tests + lint:php:check + build
npm run test:all:with-php # All tests including PHP unit tests
npm run ci               # Continuous integration command
npm run fix              # Auto-fix linting and formatting issues

# Individual commands
npm run lint             # ESLint with auto-fix
npm run type-check       # Vue TypeScript checking
npm run format           # Prettier formatting
php ./vendor/bin/pint    # PHP CS Fixer (Laravel Pint)
php artisan test         # PHPUnit tests
```

### Deployment
```bash
# Build deployment package
npm run build:production
# Creates crime-map-deployment.zip with optimized assets

# Cache clearing utilities
php public/clear-cache.php        # Web-based cache clearing
bash clear-cache.sh              # Shell script cache clearing
```

## Architecture Overview

### Backend Structure
- **Controllers**: Standard Laravel controllers in `app/Http/Controllers/`
  - `MonitoringDataController`: Main crime data CRUD operations
  - `DashboardController`: Statistics and dashboard data with month/year filtering
  - `KamtibmasEventController`: Calendar event management with Indonesian holidays API integration
  - Regional controllers for Provinsi, KabupatenKota, Kecamatan
  - `AppSettingController`: Application settings management
- **Models**: Eloquent models with relationships for Indonesian administrative regions
  - `Event`: Calendar events model with categories (Agenda Nasional, Agenda Internasional, Kamtibmas)
- **Services**: `SettingsService` for centralized settings management
- **Middleware**: `InjectAppSettings` for global settings injection

### Frontend Structure
- **Pages**: Inertia.js pages in `resources/js/pages/`
  - `Dashboard.vue`: Main analytics dashboard with maps and charts, includes month/year filtering
  - `KamtibmasCalendar/Index.vue`: Full-featured calendar with event management and Indonesian holidays
  - `MonitoringData/`: Complete CRUD interface for crime data
  - Regional management pages for administrative data
- **Components**: Reusable Vue components with Radix UI base
- **Layouts**: Multiple layout systems including `AppLayout` with sidebar navigation
- **UI Library**: Custom UI components based on Radix Vue in `components/ui/`

### Database Design
- **Regional Hierarchy**: provinces → regencies (kabupaten/kota) → districts (kecamatan)
- **Crime Data**: `monitoring_data` table with coordinate mapping and category relationships
- **Event Management**: `events` table with categories, date ranges, and color coding
- **Settings**: Dynamic application settings with file upload support
- **Seeded Data**: Complete Indonesian administrative regions (38 provinces, 514+ regencies, 7000+ districts)

### API Endpoints
- Public APIs for regional data: `/api/provinsi`, `/api/kabupaten-kota/{id}`, `/api/kecamatan/{id}`
- Category APIs for crime types and sub-categories
- Calendar events: `/kamtibmas-events` with full CRUD operations
- External integration: Indonesian holidays API (https://api-harilibur.vercel.app/api) with 1-day caching
- All admin functions use web routes with Inertia.js rendering

### Key Features
- **Interactive Mapping**: Leaflet.js integration with custom crime type icons
- **Regional Filtering**: Three-tier administrative region selection
- **Statistics Dashboard**: Real-time crime statistics with visual indicators and month/year filtering
- **Calendar System**: Full-featured event management with Indonesian holidays integration
  - Day-based event viewing and CRUD operations
  - Month/year navigation and filtering
  - Event categories: Agenda Nasional, Agenda Internasional, Kamtibmas
  - Multi-day event support with duration tracking
- **Settings Management**: File upload support and grouped settings interface
- **Responsive Design**: Mobile-friendly interface with dark mode support

## Development Notes

- Uses Inertia.js for seamless SPA experience between Laravel backend and Vue frontend
- TypeScript paths configured with `@/` alias for `resources/js/`
- Database uses SQLite by default for easy development setup
- Comprehensive Indonesian regional data pre-seeded from government sources
- UI follows modern design patterns with Tailwind CSS and Radix Vue components
- Testing includes both frontend (ESLint, TypeScript, Prettier) and backend (PHPUnit) validation

### Important Technical Details

**Tailwind CSS v4 Syntax:**
- **CRITICAL**: This project uses Tailwind CSS v4.1.1 with updated syntax
- Use `bg-gray-500/75` instead of `bg-gray-500 bg-opacity-75`
- Use `text-white/80` instead of `text-white text-opacity-80`
- Arbitrary values: `bg-red-500/[0.85]` for custom opacity
- All opacity utilities follow the `/[value]` pattern

**Modal System:**
- Event modals use backdrop click protection to prevent data loss
- Day modals allow backdrop click for quick closing
- Proper z-index layering: backdrop → content container → modal content
- ESC key includes confirmation dialog for unsaved changes

**Calendar Integration:**
- Indonesian holidays cached for 1 day using Laravel Cache
- Event categories are controlled server-side via `Event::getCategories()`
- Month/year filtering with debounced navigation
- TypeScript interfaces include category field for type safety

**API Integration:**
- External holidays API: `https://api-harilibur.vercel.app/api`
- Error handling with fallback to empty array
- Response caching to prevent excessive API calls
- Color coding: red for national holidays, orange for religious holidays

### Common Issues & Solutions

**Tailwind Opacity Not Working:**
- ❌ Wrong: `bg-opacity-75` (v3 syntax)
- ✅ Correct: `bg-gray-500/75` (v4 syntax)

**Modal Backdrop Issues:**
- Ensure proper HTML structure: backdrop → content container → modal content
- Use `fixed inset-0 z-10` for content container over backdrop
- Include `relative` class on modal content for proper stacking

**Calendar Event CRUD:**
- Always include category validation in frontend and backend
- Use debounced watchers for filter changes to prevent loops
- Force close modals after successful save operations