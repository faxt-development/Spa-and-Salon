<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', "Cleo's Salon and Spa") }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="{{ asset('node_modules/@fullcalendar/core/main.css') }}" rel="stylesheet" />
    <link href="{{ asset('node_modules/@fullcalendar/daygrid/main.css') }}" rel="stylesheet" />
    <link href="{{ asset('node_modules/@fullcalendar/timegrid/main.css') }}" rel="stylesheet" />
    <link href="{{ asset('node_modules/@fullcalendar/list/main.css') }}" rel="stylesheet" />
    
    <!-- Flatpickr CSS -->
    <link href="{{ asset('node_modules/flatpickr/dist/flatpickr.min.css') }}" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <!-- FullCalendar Scripts -->
    <script src="{{ asset('node_modules/@fullcalendar/core/main.js') }}"></script>
    <script src="{{ asset('node_modules/@fullcalendar/daygrid/main.js') }}"></script>
    <script src="{{ asset('node_modules/@fullcalendar/timegrid/main.js') }}"></script>
    <script src="{{ asset('node_modules/@fullcalendar/interaction/main.js') }}"></script>
    <script src="{{ asset('node_modules/@fullcalendar/list/main.js') }}"></script>
    
    <!-- Flatpickr Script -->
    <script src="{{ asset('node_modules/flatpickr/dist/flatpickr.min.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
