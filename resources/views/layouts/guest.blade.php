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

        <title>@yield('title', $companyName . ' - Salon & Spa Management')</title>

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
    <body class="font-sans text-gray-900 antialiased bg-gray-50 flex flex-col min-h-screen">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center">
                            @if(isset($company->logo_url))
                                <img class="h-8 w-auto" src="{{ $company->logo_url }}" alt="{{ $companyName }} Logo">
                            @else
                                <x-application-logo class="h-8 w-auto" />
                            @endif
                            <span class="ml-2 text-xl font-bold text-gray-900">{{ $companyName }}</span>
                        </a>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('pricing') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Pricing
                            </a>
                            <a href="{{ route('contact') }}" class="border-primary-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Contact
                            </a>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="bg-primary-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-primary-700">
                            Sign up
                        </a>
                    </div>
                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Icon when menu is closed -->
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Icon when menu is open -->
                            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state -->
            <div class="sm:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('pricing') }}" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Pricing
                    </a>
                    <a href="{{ route('contact') }}" class="bg-primary-50 border-primary-500 text-primary-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Contact
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('login') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="block px-4 py-2 text-base font-medium text-primary-600 hover:text-primary-800 hover:bg-primary-50">
                            Sign up
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <x-footer />

        <!-- Mobile menu toggle script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuButton = document.querySelector('button[aria-controls="mobile-menu"]');
                const mobileMenu = document.getElementById('mobile-menu');
                
                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', function() {
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        this.setAttribute('aria-expanded', !isExpanded);
                        mobileMenu.classList.toggle('hidden');
                        
                        // Toggle between menu and close icons
                        const menuIcon = this.querySelector('svg:not(.hidden)');
                        const closeIcon = this.querySelector('svg.hidden');
                        if (menuIcon) menuIcon.classList.add('hidden');
                        if (closeIcon) closeIcon.classList.remove('hidden');
                    });
                }
            });
        </script>
    </body>
</html>
