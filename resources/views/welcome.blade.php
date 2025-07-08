<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $companyName }} - The all-in-one business management platform for modern spas and salons. Streamline operations, boost bookings, and grow your business.">

    <title>{{ config('app.name', $companyName) }} - Business Management for Spas & Salons</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|playfair+display:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-display {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 flex flex-col min-h-full">
    <div class="flex flex-col min-h-full">
        <!-- Main Header -->
        <header class="bg-white dark:bg-gray-900 shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <!-- Replace src with your actual logo path -->
                            <img class="h-12 w-auto" src="{{ asset('images/faxtina-logo.jpg') }}" alt="{{ $companyName }}">
                            <span class="ml-3 text-2xl font-display font-bold text-primary700 dark:text-primary400">{{ $companyName }}</span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="hidden md:block">
                        <nav class="hidden md:flex space-x-8">
                            <a href="#features" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Features</a>
                            <a href="#pricing" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Pricing</a>
                            <a href="#testimonials" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Success Stories</a>
                            @if (Route::has('login'))
                                @auth
                                    @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ url('/admin/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Dashboard</a>
                                    @else
                                    <a href="{{ url('/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Dashboard</a>

                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Log in</a>

                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="ml-4 px-4 py-2 bg-primary600 text-white text-sm font-medium rounded-md hover:bg-primary700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Register</a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex md:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Menu icon -->
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="#services" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-primary400 dark:hover:bg-gray-800">Services</a>
                    <a href="#about" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-primary400 dark:hover:bg-gray-800">About Us</a>
                    <a href="#testimonials" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-primary400 dark:hover:bg-gray-800">Testimonials</a>
                    @if (Route::has('login'))
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ url('/admin/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Dashboard</a>
                                @else
                                <a href="{{ url('/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Dashboard</a>

                                @endif
                            @else
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-primary400 dark:hover:bg-gray-800">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-primary400 dark:hover:bg-gray-800">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Section for Business Owners -->
        <section class="bg-gradient-to-r from-primary-600 to-primary-800 dark:from-gray-800 dark:to-gray-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                        <span class="block">The All-in-One Solution for</span>
                        <span class="block text-accent-300">Modern Spas & Salons</span>
                    </h1>
                    <div class="mt-8 mb-8 mx-auto max-w-4xl">
                        <img src="/images/HeaderFaxtin2.jpg" alt="Spa and salon management software" class="rounded-lg shadow-2xl w-full h-auto border-4 border-white">
                    </div>
                    <p class="mt-3 max-w-2xl mx-auto text-xl text-primary-100 sm:mt-5 md:mt-5 md:max-w-3xl md:text-2xl">
                        Streamline your business, delight your clients, and grow your revenue with our powerful salon and spa management platform.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
                        <div class="rounded-md shadow">
                            <a href="https://faxtina.prasso.io/#features" class="w-full flex items-center justify-center px-8 py-4 border border-transparent text-base font-bold rounded-md text-primary-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10 transition duration-150 ease-in-out transform hover:scale-105">
                                Explore Features
                            </a>
                        </div>
                        <div class="rounded-md shadow">
                            <a href="/pricing" class="w-full flex items-center justify-center px-8 py-4 border-2 border-white text-base font-bold rounded-md text-white bg-transparent hover:bg-white hover:bg-opacity-10 md:py-4 md:text-lg md:px-10 transition duration-150 ease-in-out transform hover:scale-105">
                                View Pricing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="flex-1">

        <!-- Features Section -->
        <section id="features" class="py-16 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Powerful Features for Your Business</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">Everything you need to manage and grow your spa or salon business</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Appointment Management -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition-transform hover:scale-105">
                        <div class="h-12 w-12 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Online Booking</h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Let clients book 24/7 with our easy-to-use online scheduling system that syncs with your calendar in real-time.
                        </p>
                    </div>

                    <!-- Client Management -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition-transform hover:scale-105">
                        <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Client Management</h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Track client history, preferences, and notes all in one place to provide personalized service every visit.
                        </p>
                    </div>

                    <!-- Point of Sale -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition-transform hover:scale-105">
                        <div class="h-12 w-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Point of Sale</h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Process transactions quickly with our integrated POS system that handles payments, tips, and receipts with ease.
                        </p>
                    </div>
                </div>
                <div class="mt-12 text-center">
                    <a href="https://faxtina.prasso.io#features" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition-colors duration-200">
                        Explore All Features
                        <svg class="ml-2 -mr-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <!-- Pricing Section -->
        <section id="pricing" class="py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Simple, Transparent Pricing</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">Choose the plan that works best for your business</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Starter Plan -->
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border-2 border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">Starter</h3>
                        <div class="text-center mb-8">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">$29</span>
                            <span class="text-gray-600 dark:text-gray-400">/month</span>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Perfect for solo practitioners</p>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Up to 2 staff members
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Online booking
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Client management
                            </li>
                        </ul>
                        <a href="/pricing" class="block w-full py-3 px-4 text-center rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 font-medium transition-colors duration-200">
                            Get Started
                        </a>
                    </div>

                    <!-- Popular Plan -->
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border-2 border-primary-500 transform scale-105 relative">
                        <div class="absolute top-0 right-0 bg-primary-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">MOST POPULAR</div>
                        <h3 class="text-2xl font-bold text-center text-primary-600 dark:text-primary-400 mb-4">Professional</h3>
                        <div class="text-center mb-8">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">$79</span>
                            <span class="text-gray-600 dark:text-gray-400">/month</span>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Perfect for growing teams</p>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Up to 10 staff members
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Everything in Starter, plus:
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Point of Sale
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Inventory management
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Reporting & analytics
                            </li>
                        </ul>
                        <a href="/pricing" class="block w-full py-3 px-4 text-center rounded-md bg-primary-600 text-white hover:bg-primary-700 font-medium transition-colors duration-200">
                            Start Free Trial
                        </a>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border-2 border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">Enterprise</h3>
                        <div class="text-center mb-8">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">Custom</span>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">For large businesses with custom needs</p>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Unlimited staff members
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Everything in Professional, plus:
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Dedicated account manager
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Custom integrations
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Priority support
                            </li>
                        </ul>
                        <a href="{{ route('contact') }}" class="block w-full py-3 px-4 text-center rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 font-medium transition-colors duration-200">
                            Contact Sales
                        </a>
                    </div>
                </div>
                <div class="mt-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>All plans include a 14-day free trial with no credit card required.</p>
                    <p class="mt-2">Need help choosing? <a href="{{ route('contact') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">Contact our sales team</a></p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-16 bg-gradient-to-b from-white to-gray-50 dark:from-gray-800 dark:to-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="inline-block px-3 py-1 text-sm font-semibold text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 rounded-full mb-4">Join Our Growing Community</span>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Be Among Our First Success Stories</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-12">
                        We're just getting started on our journey to revolutionize spa and salon management.
                        While we're new, our mission is to help businesses like yours thrive.
                        Here's what we're hearing from our early partners:
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <!-- Testimonial 1 -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all hover:shadow-xl">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-300 font-bold text-xl mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Early Adopter Program</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pilot Phase Feedback</p>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 italic">"We were looking for a modern solution that could grow with our business. Faxtina's team has been incredibly responsive to our feedback, and we're excited to be part of shaping the future of salon management software."</p>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all hover:shadow-xl">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xl mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Beta Tester</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Spa Owner</p>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 italic">"The potential here is incredible. The team's vision for the product aligns perfectly with what we need to streamline our operations. We're looking forward to being one of the first to implement Faxtina in our locations."</p>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all hover:shadow-xl">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-300 font-bold text-xl mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Industry Expert</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Salon Consultant</p>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 italic">"Having worked with numerous salon management solutions, I'm impressed with Faxtina's fresh approach. Their focus on user experience and modern technology sets them apart in this space."</p>
                    </div>
                </div>

                <!-- CTA Box -->
                <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 rounded-2xl p-8 text-center">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Be Among the First</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6 max-w-2xl mx-auto">
                        Join our exclusive group of early adopters and help shape the future of spa and salon management software.
                        Enjoy special founding member benefits and pricing.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Schedule a Demo
                        </a>
                        <a href="/pricing" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-primary-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors dark:bg-gray-800 dark:text-primary-400 dark:hover:bg-gray-700">
                            Start Free Trial
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Product</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#features" class="text-base text-gray-300 hover:text-white transition-colors">Features</a></li>
                            <li><a href="#pricing" class="text-base text-gray-300 hover:text-white transition-colors">Pricing</a></li>
                            <li><a href="#testimonials" class="text-base text-gray-300 hover:text-white transition-colors">Success Stories</a></li>
                            <li><a href="{{ route('login') }}" class="text-base text-gray-300 hover:text-white transition-colors">Login</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Resources</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="https://faxtina.com/blog" class="text-base text-gray-300 hover:text-white transition-colors">Blog</a></li>
                            <li><a href="https://faxtina.com/help" class="text-base text-gray-300 hover:text-white transition-colors">Help Center</a></li>
                            <li><a href="https://faxtina.com/webinars" class="text-base text-gray-300 hover:text-white transition-colors">Webinars</a></li>
                            <li><a href="https://faxtina.com/contact" class="text-base text-gray-300 hover:text-white transition-colors">Contact Support</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Company</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="https://faxt.com/#about" class="text-base text-gray-300 hover:text-white transition-colors">About Us</a></li>
                            <li><a href="https://faxtina.com/press" class="text-base text-gray-300 hover:text-white transition-colors">Press</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="https://faxtina.com/privacy" class="text-base text-gray-300 hover:text-white transition-colors">Privacy Policy</a></li>
                            <li><a href="https://faxtina.com/terms" class="text-base text-gray-300 hover:text-white transition-colors">Terms of Service</a></li>
                            <li><a href="https://faxtina.com/security" class="text-base text-gray-300 hover:text-white transition-colors">Security</a></li>
                            <li><a href="https://faxtina.com/gdpr" class="text-base text-gray-300 hover:text-white transition-colors">GDPR</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-gray-800">
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex justify-center md:order-2 space-x-6">
                            <a href="https://facebook.com/faxtina" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Facebook</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="https://instagram.com/faxtina" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Instagram</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="https://twitter.com/faxtina" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Twitter</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                            <a href="https://linkedin.com/company/faxtina" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                        </div>
                        <div class="mt-8 md:mt-0">
                            <p class="text-base text-gray-400">
                                &copy; {{ date('Y') }} Faxtina Technologies, Inc. All rights reserved.
                            </p>
                            <p class="mt-2 text-sm text-gray-500">
                                Made with ❤️ for spa and salon professionals worldwide.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
