@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-red-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white text-center">Appointment Not Found</h2>
            </div>
            
            <div class="p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                
                <p class="text-gray-700 mb-6">{{ $message ?? 'This appointment link is invalid or has expired.' }}</p>
                
                <div class="space-y-3">
                    <a href="{{ route('guest.booking.index') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 transition-colors">
                        Book a New Appointment
                    </a>
                    
                    <p class="text-sm text-gray-600">
                        Need help? Contact the salon directly or
                        <a href="mailto:support@faxtina.com" class="text-primary-600 hover:text-primary-800">email support</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
