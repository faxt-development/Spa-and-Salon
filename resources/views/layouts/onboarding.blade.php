<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Faxtina') }} - Onboarding</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Simple Header -->
        <header class="bg-white shadow">
            <div class="container py-3">
                <div class="d-flex justify-content-between align-items-center">
                <div class="shrink-0 flex items-center">

                        @role('admin|staff')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        </a>
                        @else
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        </a>
                        @endrole
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
