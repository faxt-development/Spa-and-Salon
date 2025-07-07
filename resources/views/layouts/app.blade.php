@php
    $company = $company ?? null;
    $companyName = $company->name ?? config('app.name');
    $theme = $theme ?? null;
    
    // Set page title
    $pageTitle = isset($pageTitle) ? "$pageTitle - $companyName" : $companyName;
    
    // Set theme variables for inline styles if needed
    $inlineThemeVars = [];
    if ($theme) {
        $inlineThemeVars = [
            '--primary-color' => $theme->primary_color,
            '--secondary-color' => $theme->secondary_color,
            '--accent-color' => $theme->accent_color,
            '--text-color' => $theme->text_color,
            '--background-color' => '#ffffff',
        ];
    }
@endphp

@if(!empty($inlineThemeVars))
    @push('styles')
        <style>
            :root {
                @foreach($inlineThemeVars as $var => $value)
                    {{ $var }}: {{ $value }};
                @endforeach
            }
            
            /* Dark mode overrides */
            .dark {
                --background-color: #0f172a;
                --text-color: #f8fafc;
                --text-color-light: #cbd5e1;
                --text-color-lighter: #94a3b8;
                --card-bg: #1e293b;
                --popover-bg: #1e293b;
                --border-color: #334155;
                --input-border: #475569;
            }
        </style>
    @endpush
@endif

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ $company->favicon_url ?? asset('favicon.ico') }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ $company->favicon_url ?? asset('favicon.ico') }}" type="image/x-icon">

        @include('partials.seo-headers')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
    @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --color-primary: {{ $theme['primary'] ?? '#4f46e5' }};
                --color-primary-hover: {{ isset($theme['primary']) ? color_mix('black', $theme['primary'], 10) : '#4338ca' }};
                --color-secondary: {{ $theme['secondary'] ?? '#10b981' }};
                --color-accent: {{ $theme['accent'] ?? '#f59e0b' }};
            }

            .btn-primary {
                @apply bg-primary-600 hover:bg-primary-700 text-white;
            }

            .text-primary {
                @apply text-primary-600;
            }

            .border-primary {
                @apply border-primary-600;
            }

            .focus\:ring-primary:focus {
                --tw-ring-color: var(--color-primary);
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            ::-webkit-scrollbar-track {
                @apply bg-gray-100;
            }

            ::-webkit-scrollbar-thumb {
                @apply bg-gray-300 rounded-full hover:bg-gray-400;
            }

            ::-webkit-scrollbar-thumb:hover {
                @apply bg-gray-400;
            }
        </style>

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 min-h-screen flex flex-col">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="flex-1">
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $header }}</h1>
                        </div>
                    </header>
                @endisset

                {{ $slot }}
            </main>

            @include('partials.footer')
        </div>

        @stack('modals')
        @stack('scripts')
    </body>
</html>
