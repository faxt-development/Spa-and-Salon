@extends('layouts.guest-content')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Unsubscribe Link Invalid
            </h2>
        </div>

        <div class="mt-8 bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>

                <p class="mt-4 text-lg font-medium text-gray-900">
                    Unable to process your request
                </p>

                <p class="mt-2 text-sm text-gray-600">
                    The unsubscribe link you used is invalid or has expired.
                </p>

                <div class="mt-6">
                    <p class="text-sm text-gray-500">
                        If you're having trouble unsubscribing, please contact our support team for assistance.
                    </p>

                    <div class="mt-6
                        <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Contact Support
                    </a>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        {{ config('app.name') }}<br>
                        <a href="{{ url('/') }}" class="hover:underline">{{ url('/') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
