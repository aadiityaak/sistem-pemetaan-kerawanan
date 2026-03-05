<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark'=> ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @php
    $faviconSetting = \App\Models\AppSetting::where('key', 'app_favicon')->first();
    $faviconPath = $faviconSetting ? $faviconSetting->value : '/favicon.ico';

    // Use setting's updated_at timestamp for cache busting
    $timestamp = $faviconSetting ? $faviconSetting->updated_at->timestamp : time();
    $faviconWithTimestamp = $faviconPath . '?v=' . $timestamp;

    // Determine file type
    $isSvg = str_ends_with($faviconPath, '.svg');
    $isPng = str_ends_with($faviconPath, '.png');
    $isJpg = str_ends_with($faviconPath, '.jpg') || str_ends_with($faviconPath, '.jpeg');
    @endphp

    @if($isSvg)
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/svg+xml" sizes="any">
    @elseif($isPng)
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/png" sizes="16x16">
    <link rel="shortcut icon" href="{{ $faviconWithTimestamp }}" type="image/png">
    @elseif($isJpg)
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/jpeg" sizes="32x32">
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/jpeg" sizes="16x16">
    <link rel="shortcut icon" href="{{ $faviconWithTimestamp }}" type="image/jpeg">
    @else
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/x-icon" sizes="32x32">
    <link rel="icon" href="{{ $faviconWithTimestamp }}" type="image/x-icon" sizes="16x16">
    <link rel="shortcut icon" href="{{ $faviconWithTimestamp }}" type="image/x-icon">
    @endif
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    {{-- PWA Manifest & Meta Tags --}}
    @php
    $appVersion = config('app.version', '1.0.0');
    // For Vite-PWA in Laravel:
    // 1. In development, manifest is served by Vite dev server.
    // 2. In production, manifest is in public/build/.
    $manifestPath = null;
    if (file_exists(public_path('hot'))) {
        $hotUrl = rtrim(file_get_contents(public_path('hot')), " \t\n\r\0\x0B/");
        $manifestPath = $hotUrl . '/manifest.webmanifest';
    } elseif (file_exists(public_path('build/manifest.webmanifest'))) {
        $manifestPath = '/build/manifest.webmanifest';
    } elseif (file_exists(public_path('manifest.webmanifest'))) {
        $manifestPath = '/manifest.webmanifest';
    }
    @endphp

    @if($manifestPath)
    <link rel="manifest" href="{{ $manifestPath }}?v={{ $appVersion }}">
    @endif
    <meta name="theme-color" content="#3b82f6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Crime Map">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="" />

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    @routes
    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    {{-- Global Hacker Backgrounds (only visible in dark mode) --}}
    <div class="matrix-bg hidden dark:block"></div>
    <div class="ai-circuit-bg hidden dark:block"></div>
    <div class="scanline-global hidden dark:block"></div>
    <div class="scanline-beam hidden dark:block"></div>
    @inertia
</body>

</html>