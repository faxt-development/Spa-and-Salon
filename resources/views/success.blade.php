@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <main class="container mx-auto px-4 py-12 md:py-24">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <div class="mb-8 flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl mb-4">
                Thank you for your subscription!
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-8">
                Your subscription has been successfully processed. You'll receive a confirmation email shortly.
            </p>
            <div class="mt-8">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </main>
</div>
@endsection
