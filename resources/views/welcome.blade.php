<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Welcome to Cleo's Salon & Spa - Your premier destination for luxury beauty and wellness services. Book your appointment today!">

    <title>{{ config('app.name', "Cleo's Salon & Spa") }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/cleos-hair-salon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|playfair+display:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
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
                            <img class="h-12 w-auto" src="{{ asset('images/cleos-hair-salon.png') }}" alt="Cleo's Salon & Spa">
                            <span class="ml-3 text-2xl font-display font-bold text-purple-700 dark:text-purple-400">Cleo's Salon & Spa</span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-center space-x-8">
                            <a href="#services" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Services</a>
                            <a href="#about" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">About Us</a>
                            <a href="#testimonials" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Testimonials</a>
                            @if (Route::has('login'))
                                @auth
                                    @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ url('/admin/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Dashboard</a>
                                    @else 
                                    <a href="{{ url('/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Dashboard</a>
                                    
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Log in</a>

                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="ml-4 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Register</a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex md:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none" aria-controls="mobile-menu" aria-expanded="false">
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
                    <a href="#services" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-purple-400 dark:hover:bg-gray-800">Services</a>
                    <a href="#about" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-purple-400 dark:hover:bg-gray-800">About Us</a>
                    <a href="#testimonials" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-purple-400 dark:hover:bg-gray-800">Testimonials</a>
                    @if (Route::has('login'))
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ url('/admin/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Dashboard</a>
                                @else 
                                <a href="{{ url('/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 px-3 py-2 text-sm font-medium">Dashboard</a>
                                
                                @endif
                            @else
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-purple-400 dark:hover:bg-gray-800">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-purple-400 dark:hover:bg-gray-800">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-800 dark:to-gray-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                        <span class="block">Welcome to</span>
                        <span class="block text-purple-600 dark:text-purple-400">Cleo's Salon & Spa</span>
                    </h1>
                    <div class="mt-8 mb-8 mx-auto max-w-3xl">
                        <img src="https://images.prasso.io/cleos/ai_generated_1750345785_ZpeE5xTM.png" alt="Luxury hair style" class="rounded-lg shadow-xl w-full h-auto">
                    </div>
                    <p class="mt-3 max-w-md mx-auto text-base text-gray-500 dark:text-gray-300 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                        Experience luxury and relaxation with our premium beauty and wellness services.
                    </p>
                    <div class="mt-8 flex justify-center">
                        <div class="rounded-md shadow">
                            <a href="#services" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 md:py-4 md:text-lg md:px-10">
                                View Services
                            </a>
                        </div>
                        <div class="ml-3 rounded-md shadow">
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-purple-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="flex-1">

        <!-- Services Section -->
        <section id="services" class="py-16 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Our Services</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">Discover our range of premium beauty and wellness services</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Hair Services -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition-transform hover:scale-105">
                        <div class="h-12 w-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mb-4">
                            ✂️
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Hair Services</h3>
                        <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                            <li>• Haircut & Styling</li>
                            <li>• Hair Coloring</li>
                            <li>• Highlights & Balayage</li>
                            <li>• Keratin Treatments</li>
                            <li>• Hair Extensions</li>
                        </ul>
                    </div>

                    <!-- Spa Services -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition-transform hover:scale-105">
                        <div class="h-12 w-12 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center mb-4">
                            💆
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Spa Treatments</h3>
                        <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                            <li>• Facials</li>
                            <li>• Body Treatments</li>
                            <li>• Massage Therapy</li>
                            <li>• Waxing</li>
                            <li>• Skin Care</li>
                        </ul>
                    </div>

                    <!-- Nail Services -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition-transform hover:scale-105">
                        <div class="h-12 w-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mb-4">
                            💅
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nail Care</h3>
                        <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                            <li>• Manicure</li>
                            <li>• Pedicure</li>
                            <li>• Gel Polish</li>
                            <li>• Nail Art</li>
                            <li>• Spa Mani/Pedi</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 text-center">
                    <a href="{{ route('services') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                        View All Services
                        <svg class="ml-2 -mr-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <div class="py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">What Our Clients Say</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">Don't just take our word for it - hear from our satisfied clients</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center text-purple-600 dark:text-purple-300 font-bold">AM</div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900 dark:text-white">Amanda M.</h4>
                                <div class="flex text-yellow-400">
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"The stylists at Cleo's are true artists! I always leave feeling like a million bucks. The atmosphere is so relaxing and the staff is incredibly talented."</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 rounded-full bg-pink-100 dark:bg-pink-900 flex items-center justify-center text-pink-600 dark:text-pink-300 font-bold">TJ</div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900 dark:text-white">Thomas J.</h4>
                                <div class="flex text-yellow-400">
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"I've been coming to Cleo's for years. The attention to detail and personalized service keeps me coming back. Best haircut in town!"</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold">SR</div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900 dark:text-white">Sophia R.</h4>
                                <div class="flex text-yellow-400">
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"The spa treatments are absolutely divine. I had the most relaxing facial and massage. The staff made me feel so pampered and special."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-purple-700 dark:bg-purple-900">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    <span class="block">Ready to experience the difference?</span>
                    <span class="block text-purple-200">Book your appointment today.</span>
                </h2>
                <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                    <div class="inline-flex rounded-md shadow">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-purple-600 bg-white hover:bg-purple-50">
                            Book Now
                        </a>
                    </div>
                    <div class="ml-3 inline-flex rounded-md shadow">
                        <a href="#" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 bg-opacity-60 hover:bg-opacity-70">
                            Call Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
