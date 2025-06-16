@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">
                Email Preferences
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Manage your email subscription preferences for {{ config('app.name') }}
            </p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Subscription Settings
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Update your email preferences below.
                </p>
            </div>
            
            <form action="{{ route('email.preferences.update', $token) }}" method="POST" class="px-4 py-5 sm:p-6">
                @csrf
                
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Email: <span class="font-medium">{{ $email }}</span>
                    </p>
                    
                    <div class="space-y-4">
                        <p class="text-sm font-medium text-gray-700">
                            I'd like to receive:
                        </p>
                        
                        @foreach($subscriptionTypes as $key => $label)
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="subscription-{{ $key }}" 
                                       name="subscriptions[{{ $key }}]" 
                                       type="checkbox" 
                                       value="1"
                                       {{ isset($currentSubscriptions[$key]) && $currentSubscriptions[$key] ? 'checked' : '' }}
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="subscription-{{ $key }}" class="font-medium text-gray-700">
                                    {{ $label }}
                                </label>
                                @if(isset($subscriptionDescriptions[$key]))
                                <p class="text-gray-500 text-xs">
                                    {{ $subscriptionDescriptions[$key] }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200 mt-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Preferences
                        </button>
                        
                        <p class="mt-3 text-sm text-gray-500">
                            <a href="{{ route('email.unsubscribe', $token) }}" class="text-red-600 hover:text-red-500">
                                Or, unsubscribe from all emails
                            </a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>Need help? <a href="{{ route('contact') }}" class="font-medium text-blue-600 hover:text-blue-500">Contact support</a></p>
            <p class="mt-1">{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Add Alpine.js for any client-side interactions if needed
    document.addEventListener('alpine:init', () => {
        // Any client-side interactivity can go here
    });
</script>
@endpush
@endsection
