@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Cancellation Policies</h1>
                    <a href="{{ route('admin.appointments.settings') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Back to Settings
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-6">
                    <p class="text-gray-600">
                        Configure your appointment cancellation policies to manage last-minute cancellations and no-shows.
                        Setting clear policies helps reduce revenue loss and ensures your schedule remains optimized.
                    </p>
                </div>

                <form action="{{ route('admin.appointments.policies.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        @if($settings->isEmpty())
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            No appointment settings found. Please create appointment settings first.
                                        </p>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.appointments.settings.create') }}" class="text-sm font-medium text-yellow-700 underline hover:text-yellow-600">
                                                Create Appointment Settings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @foreach($settings as $index => $setting)
                                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                                        @if($setting->location)
                                            {{ $setting->location->name }} Location
                                        @else
                                            Company-wide Settings
                                        @endif
                                    </h3>

                                    <input type="hidden" name="settings[{{ $index }}][id]" value="{{ $setting->id }}">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Cancellation Notice -->
                                        <div class="bg-white p-4 rounded border border-gray-200">
                                            <h4 class="font-medium text-gray-800 mb-3">Cancellation Notice</h4>
                                            
                                            <div class="mb-4">
                                                <label for="cancellation_notice_{{ $index }}" class="block text-sm font-medium text-gray-700">Required notice for cancellation (hours)</label>
                                                <select id="cancellation_notice_{{ $index }}" name="settings[{{ $index }}][cancellation_notice]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                                    @foreach([1, 2, 3, 6, 12, 24, 48, 72] as $hours)
                                                        <option value="{{ $hours }}" {{ $setting->cancellation_notice == $hours ? 'selected' : '' }}>
                                                            {{ $hours }} {{ Str::plural('hour', $hours) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <p class="text-sm text-gray-500 mt-2">
                                                Clients must cancel at least this many hours before their appointment to avoid penalties.
                                            </p>
                                        </div>

                                        <!-- Cancellation Fee -->
                                        <div class="bg-white p-4 rounded border border-gray-200">
                                            <h4 class="font-medium text-gray-800 mb-3">Cancellation Fee</h4>
                                            
                                            <div class="mb-4">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="settings[{{ $index }}][enforce_cancellation_fee]" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" {{ $setting->enforce_cancellation_fee ? 'checked' : '' }}>
                                                    <span class="ml-2">Enforce cancellation fee for late cancellations</span>
                                                </label>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="cancellation_fee_{{ $index }}" class="block text-sm font-medium text-gray-700">Fee amount ($)</label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">$</span>
                                                    </div>
                                                    <input type="number" step="0.01" min="0" name="settings[{{ $index }}][cancellation_fee]" id="cancellation_fee_{{ $index }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" value="{{ $setting->cancellation_fee ?? '0.00' }}">
                                                </div>
                                            </div>
                                            
                                            <p class="text-sm text-gray-500 mt-2">
                                                This fee will be charged for late cancellations or no-shows.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="flex justify-end mt-6">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Save Cancellation Policies
                                </button>
                            </div>
                        @endif
                    </div>
                </form>

                <div class="mt-10 border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">About Cancellation Policies</h2>
                    
                    <div class="prose max-w-none text-gray-600">
                        <p>
                            Effective cancellation policies help protect your business from revenue loss due to last-minute cancellations and no-shows.
                            Here are some best practices for implementing cancellation policies:
                        </p>
                        
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Be transparent about your cancellation policy during the booking process</li>
                            <li>Consider the nature of your services when setting the cancellation notice period</li>
                            <li>Set reasonable cancellation fees that reflect the impact of a missed appointment</li>
                            <li>Implement a system for tracking no-shows and late cancellations</li>
                            <li>Train staff on how to communicate and enforce cancellation policies</li>
                            <li>Consider offering alternatives like rescheduling within a certain timeframe</li>
                        </ul>
                        
                        <p class="mt-4">
                            Remember that while cancellation policies are important for your business, they should be balanced with good customer service.
                            Consider making exceptions for first-time offenders or in cases of genuine emergencies.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
