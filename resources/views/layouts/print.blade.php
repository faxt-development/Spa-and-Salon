<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Receipt</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        /* Base Styles */
        body {
            font-family: 'Figtree', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
            color: #1a202c;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        /* Print Styles */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
                padding: 0;
            }
            
            html, body {
                width: 80mm;
                height: 100%;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                font-size: 12px !important;
                line-height: 1.3 !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Hide elements with no-print class */
            .no-print,
            .no-print *,
            .print\:hidden {
                display: none !important;
            }
            
            /* Show elements marked as print-only */
            .print-only,
            .print\:block {
                display: block !important;
            }
            
            /* Ensure the receipt container takes full width */
            .receipt-container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 10px !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            
            /* Ensure text is black and background is white */
            body, .receipt-container {
                color: #000 !important;
                background: #fff !important;
            }
            
            /* Ensure links are black and not underlined */
            a {
                color: #000 !important;
                text-decoration: none !important;
            }
            
            .break-before {
                page-break-before: always;
            }
            
            .break-after {
                page-break-after: always;
            }
            
            .avoid-break {
                page-break-inside: avoid;
            }
        }

        /* Screen Styles */
        @media screen {
            body {
                background-color: #f3f4f6;
                padding: 2rem;
            }
            
            .print-only {
                display: none;
            }
        }

        /* Utility Classes */
        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        
        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        
        .text-base {
            font-size: 1rem;
            line-height: 1.5rem;
        }
        
        .text-lg {
            font-size: 1.125rem;
            line-height: 1.75rem;
        }
        
        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }
        
        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }
        
        .font-medium {
            font-weight: 500;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-gray-600 {
            color: #4b5563;
        }
        
        .text-red-600 {
            color: #dc2626;
        }
        
        .bg-gray-50 {
            background-color: #f9fafb;
        }
        
        .border {
            border-width: 1px;
        }
        
        .border-b {
            border-bottom-width: 1px;
        }
        
        .border-t {
            border-top-width: 1px;
        }
        
        .border-gray-200 {
            border-color: #e5e7eb;
        }
        
        .rounded-lg {
            border-radius: 0.5rem;
        }
        
        .p-6 {
            padding: 1.5rem;
        }
        
        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .mb-4 {
            margin-bottom: 1rem;
        }
        
        .mt-8 {
            margin-top: 2rem;
        }
        
        .flex {
            display: flex;
        }
        
        .grid {
            display: grid;
        }
        
        .grid-cols-12 {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }
        
        .gap-2 {
            gap: 0.5rem;
        }
        
        .justify-between {
            justify-content: space-between;
        }
        
        .justify-center {
            justify-content: center;
        }
        
        .items-center {
            align-items: center;
        }
        
        .w-full {
            width: 100%;
        }
        
        .max-w-md {
            max-width: 28rem;
        }
        
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        
        .capitalize {
            text-transform: capitalize;
        }
    </style>
</head>
<body class="font-sans antialiased">
    @yield('content')

    @stack('scripts')
</body>
</html>
