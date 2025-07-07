<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $companyName = $company->name ?? config('app.name');
            $theme = $company->theme_settings ?? [
                'primary' => '#4f46e5',
                'secondary' => '#10b981',
                'accent' => '#f59e0b',
                'logo' => null
            ];
        @endphp

        <title>{{ $companyName }} - Salon & Spa Management</title>

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
                --color-secondary: {{ $theme['secondary'] ?? '#10b981' }};
                --color-accent: {{ $theme['accent'] ?? '#f59e0b' }};
            }
            .btn-primary {
                background-color: var(--color-primary);
            }
            .btn-primary:hover {
                background-color: color-mix(in srgb, var(--color-primary), black 10%);
            }
            .text-primary {
                color: var(--color-primary);
            }
            .border-primary {
                border-color: var(--color-primary);
            }
            .focus\:ring-primary:focus {
                --tw-ring-color: var(--color-primary);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-8 text-center">
                <a href="/" class="block">
                    @if(isset($company->logo_url))
                        <img src="{{ $company->logo_url }}" alt="{{ $companyName }} Logo" class="h-16 w-auto mx-auto">
                    @else
                        <x-application-logo class="h-16 w-auto text-gray-800" />
                    @endif
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">{{ $companyName }}</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-lg rounded-lg">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </div>
        </div>
    </body>
</html>
