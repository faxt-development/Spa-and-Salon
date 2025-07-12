<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Welcome to {{ $company->name }} - Your premier destination for luxury beauty and wellness services. Book your appointment today!">

    <title>{{ $company->name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|playfair+display:400,500,600,700&display=swap" rel="stylesheet" />
   <!-- API Token for AJAX Requests -->
   <script>
            window.apiToken = '{{ session()->get('api_token') }}';
        </script>

    <!-- Scripts -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Apply custom theme settings if available */
        @if($company->theme_settings)
            :root {
                @if(isset($company->theme_settings['primaryColor']))
                --primary-color: {{ $company->theme_settings['primaryColor'] }};
                @endif

                @if(isset($company->theme_settings['secondaryColor']))
                --secondary-color: {{ $company->theme_settings['secondaryColor'] }};
                @endif

                @if(isset($company->theme_settings['accentColor']))
                --accent-color: {{ $company->theme_settings['accentColor'] }};
                @endif
            }
        @endif
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
                        <a href="/" class="flex items-center">
                            @if($company->logo)
                                <img class="h-12 w-auto" src="{{ asset($company->logo) }}" alt="{{ $company->name }}">
                            @else
                                <img class="h-12 w-auto" src="{{ asset('images/faxtina-logo.jpg') }}" alt="{{ $company->name }}">
                            @endif
                            <span class="ml-3 text-2xl font-display font-bold text-primary700 dark:text-primary400">{{ $company->name }}</span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-center space-x-8">
                            <a href="#services" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Services</a>
                            <a href="#about" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">About Us</a>
                            <a href="#testimonials" class="text-gray-700 dark:text-gray-300 hover:text-primary600 dark:hover:text-primary400 px-3 py-2 text-sm font-medium">Testimonials</a>
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
        </header>

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-primary-400 to-primary-500 dark:from-gray-800 dark:to-gray-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 mb-8 md:mb-0">
                        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                            @if(isset($company->homepage_content['heroTitle']))
                                {{ $company->homepage_content['heroTitle'] }}
                            @else
                                Welcome to {{ $company->name }}
                            @endif
                        </h1>
                        <p class="text-xl text-white mb-8">
                            @if(isset($company->homepage_content['heroSubtitle']))
                                {{ $company->homepage_content['heroSubtitle'] }}
                            @else
                                Your premier destination for luxury beauty and wellness services
                            @endif
                        </p>
                        <div>
                            <a href="{{ route('register') }}" class="inline-block px-6 py-3 bg-white text-primary600 font-medium rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">Book Now</a>
                        </div>
                    </div>
                    <div class="md:w-1/2 flex justify-center">
                        @if(isset($company->homepage_content['heroImage']))
                            <img src="{{ asset($company->homepage_content['heroImage']) }}" alt="Beauty Services" class="rounded-lg shadow-xl max-h-96">
                        @else
                            <img src="{{ asset('images/spa-hero.jpg') }}" alt="Beauty Services" class="rounded-lg shadow-xl max-h-96">
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-16 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Our Services</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">
                        @if(isset($company->homepage_content['servicesTagline']))
                            {{ $company->homepage_content['servicesTagline'] }}
                        @else
                            Discover our range of premium beauty and wellness services
                        @endif
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @if(isset($company->homepage_content['services']) && is_array($company->homepage_content['services']))
                        @foreach($company->homepage_content['services'] as $service)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                                @if(isset($service['icon']))
                                    <div class="text-primary600 dark:text-primary400 mb-4">
                                        <i class="{{ $service['icon'] }} text-4xl"></i>
                                    </div>
                                @endif
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ $service['title'] ?? 'Service' }}</h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $service['description'] ?? 'Service description' }}</p>
                                <p class="text-primary600 dark:text-primary400 font-semibold">{{ $service['price'] ?? 'Contact for pricing' }}</p>
                            </div>
                        @endforeach
                    @else
                        <!-- Default services if none are defined -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="text-primary600 dark:text-primary400 mb-4">
                                <i class="fas fa-spa text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Spa Treatments</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Relax and rejuvenate with our premium spa treatments.</p>
                            <p class="text-primary600 dark:text-primary400 font-semibold">From $75</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="text-primary600 dark:text-primary400 mb-4">
                                <i class="fas fa-cut text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Hair Styling</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Professional hair styling services for any occasion.</p>
                            <p class="text-primary600 dark:text-primary400 font-semibold">From $45</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="text-primary600 dark:text-primary400 mb-4">
                                <i class="fas fa-hand-sparkles text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Nail Care</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Complete nail care services including manicures and pedicures.</p>
                            <p class="text-primary600 dark:text-primary400 font-semibold">From $35</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                        @if(isset($company->homepage_content['aboutImage']))
                            <img src="{{ asset($company->homepage_content['aboutImage']) }}" alt="About {{ $company->name }}" class="rounded-lg shadow-xl">
                        @else
                            <img src="{{ asset('images/about-spa.jpg') }}" alt="About {{ $company->name }}" class="rounded-lg shadow-xl">
                        @endif
                    </div>
                    <div class="md:w-1/2">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            @if(isset($company->homepage_content['aboutTitle']))
                                {{ $company->homepage_content['aboutTitle'] }}
                            @else
                                About {{ $company->name }}
                            @endif
                        </h2>
                        <div class="text-gray-600 dark:text-gray-300 space-y-4">
                            @if(isset($company->homepage_content['aboutContent']))
                                <p>{{ $company->homepage_content['aboutContent'] }}</p>
                            @else
                                <p>{{ $company->description ?? 'Welcome to our premier beauty and wellness destination. We are dedicated to providing exceptional service and a relaxing experience for all our clients.' }}</p>
                                <p>Our team of experienced professionals is committed to helping you look and feel your best. We use only the highest quality products and the latest techniques to ensure outstanding results.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-16 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">What Our Clients Say</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">Don't just take our word for it - hear from our satisfied clients</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @if(isset($company->homepage_content['testimonials']) && is_array($company->homepage_content['testimonials']))
                        @foreach($company->homepage_content['testimonials'] as $testimonial)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6">
                                <div class="flex items-center mb-4">
                                    <div class="text-yellow-400">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < ($testimonial['rating'] ?? 5))
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $testimonial['content'] ?? 'Testimonial content' }}</p>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="{{ isset($testimonial['avatar']) ? asset($testimonial['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($testimonial['name'] ?? 'Client') }}" alt="{{ $testimonial['name'] ?? 'Client' }}">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $testimonial['name'] ?? 'Client' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Default testimonials if none are defined -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">"The service was exceptional! I've never felt so pampered and relaxed. Will definitely be coming back!"</p>
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Sarah+J" alt="Sarah J.">
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Sarah J.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">"The staff was incredibly professional and friendly. My hair has never looked better!"</p>
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Michael+T" alt="Michael T.">
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Michael T.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">"I love coming here for my regular treatments. The atmosphere is so calming and the results are always amazing."</p>
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Emma+R" alt="Emma R.">
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Emma R.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Contact Us</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-12">Get in touch with us to book your appointment or ask any questions</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Contact Information</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-primary600 dark:text-primary400">
                                    <i class="fas fa-map-marker-alt text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-600 dark:text-gray-300">
                                        {{ $company->address }}<br>
                                        {{ $company->city }}, {{ $company->state }} {{ $company->zip }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-primary600 dark:text-primary400">
                                    <i class="fas fa-phone-alt text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-600 dark:text-gray-300">{{ $company->phone }}</p>
                                </div>
                            </div>

                            @if($company->website)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-primary600 dark:text-primary400">
                                    <i class="fas fa-globe text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-600 dark:text-gray-300">{{ $company->website }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="flex items-start">
                                <div class="flex-shrink-0 text-primary600 dark:text-primary400">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @if(isset($company->homepage_content['businessHours']))
                                            {!! nl2br(e($company->homepage_content['businessHours'])) !!}
                                        @else
                                            Monday - Friday: 9:00 AM - 7:00 PM<br>
                                            Saturday: 10:00 AM - 6:00 PM<br>
                                            Sunday: Closed
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Send Us a Message</h3>
                        <form action="#" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary500 focus:ring-primary500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary500 focus:ring-primary500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                                <textarea name="message" id="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary500 focus:ring-primary500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary600 hover:bg-primary700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary500">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">{{ $company->name }}</h3>
                        <p class="text-gray-400">{{ $company->description ?? 'Your premier destination for beauty and wellness services.' }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="#services" class="text-gray-400 hover:text-white">Services</a></li>
                            <li><a href="#about" class="text-gray-400 hover:text-white">About Us</a></li>
                            <li><a href="#testimonials" class="text-gray-400 hover:text-white">Testimonials</a></li>
                            <li><a href="#contact" class="text-gray-400 hover:text-white">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i class="fab fa-facebook-f text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i class="fab fa-twitter text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                    <p class="text-gray-400">&copy; {{ date('Y') }} {{ $company->name }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
