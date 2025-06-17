@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                You're Unsubscribed
            </h2>
        </div>

        <div class="mt-8 bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div x-data="{ 
                showResubscribe: false,
                loading: false,
                success: false,
                error: false,
                message: '',
                resubscribe() {
                    this.loading = true;
                    this.error = false;
                    
                    fetch('{{ route('email.resubscribe', $token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.success = true;
                            this.message = data.message;
                        } else {
                            this.error = true;
                            this.message = data.message || 'An error occurred while resubscribing.';
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        this.error = true;
                        this.message = 'An error occurred while resubscribing.';
                        console.error('Error:', error);
                    });
                }
            }" class="text-center">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                
                <p class="mt-4 text-lg font-medium text-gray-900">
                    @if(isset($fromCampaign) && $fromCampaign)
                        You've been unsubscribed from <span class="font-semibold">{{ $campaignName ?? 'this campaign' }}</span>.
                    @else
                        You've been unsubscribed from all marketing emails.
                    @endif
                </p>
                
                <p class="mt-2 text-sm text-gray-600">
                    We're sorry to see you go. You won't receive any more 
                    @if(isset($fromCampaign) && $fromCampaign)
                        emails from this campaign.
                    @else
                        marketing emails from us.
                    @endif
                </p>
                
                <!-- Success Message -->
                <div x-show="success" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mt-4 p-3 bg-green-50 text-green-800 rounded-md">
                    <p x-text="message"></p>
                </div>
                
                <!-- Error Message -->
                <div x-show="error" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mt-4 p-3 bg-red-50 text-red-800 rounded-md">
                    <p x-text="message"></p>
                </div>
                
                <div class="mt-6">
                    <template x-if="!showResubscribe && !success">
                        <p class="text-sm text-gray-500">
                            Changed your mind? 
                            <button @click="showResubscribe = true" 
                                    type="button" 
                                    class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none">
                                Resubscribe
                            </button>
                            or 
                            <a href="{{ route('email.preferences', $token) }}" 
                               class="font-medium text-blue-600 hover:text-blue-500">
                                Update your preferences
                            </a>
                        </p>
                    </template>
                    
                    <template x-if="showResubscribe && !success">
                        <div class="mt-4 flex flex-col items-center">
                            <p class="text-sm text-gray-700 mb-3">Are you sure you want to resubscribe?</p>
                            <div class="flex space-x-3">
                                <button @click="resubscribe()" 
                                        :disabled="loading"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                    <span x-show="!loading">Yes, resubscribe me</span>
                                    <span x-show="loading" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                </button>
                                <button @click="showResubscribe = false" 
                                        type="button" 
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="success">
                        <p class="text-sm text-gray-500 mt-3">
                            <a href="{{ route('email.preferences', $token) }}" 
                               class="font-medium text-blue-600 hover:text-blue-500">
                                Update your email preferences
                            </a>
                        </p>
                    </template>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
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
