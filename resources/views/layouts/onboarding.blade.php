@php
    $company = $company ?? null;
    $companyName = $company->name ?? config('app.name');
    $theme = $company->theme_settings ?? [
        'primary' => '#4f46e5',
        'secondary' => '#10b981',
        'accent' => '#f59e0b',
        'logo' => null
    ];

    // Set page title
    $pageTitle = "Onboarding - $companyName";
@endphp

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
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen flex flex-col">
    <div class="flex-1">
        <!-- Simple Header -->
        <header class="bg-white shadow">
            <div class="container mx-auto px-4 py-3">
                <div class="flex justify-between items-center">
                    <div class="shrink-0 flex items-center">
                        @if(isset($company->logo_url))
                            <img src="{{ $company->logo_url }}" alt="{{ $companyName }} Logo" class="h-8 w-auto">
                        @else
                            <x-application-logo class="block h-9 w-auto fill-current text-primary-600" />
                        @endif
                    </div>

                    <div>
                        <h4 class="m-0">Onboarding</h4>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white shadow mt-auto py-3">
            <div class="container text-center">
                <p class="text-muted mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Faxtina') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
