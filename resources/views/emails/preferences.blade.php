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

        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="show = false" type="button" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Subscription Settings
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Update your email preferences below.
                </p>
            </div>
            
            <div x-data="{ 
                loading: false,
                selectAll: {{ isset($allSelected) && $allSelected ? 'true' : 'false' }},
                subscriptions: {},
                toggleAll() {
                    for (let key in this.subscriptions) {
                        this.subscriptions[key] = this.selectAll;
                    }
                },
                submitForm() {
                    this.loading = true;
                    this.$refs.preferencesForm.submit();
                }
            }" class="px-4 py-5 sm:p-6">
                <form x-ref="preferencesForm" action="{{ route('email.preferences.update', $token) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600 mb-4">
                            Email: <span class="font-medium">{{ $email }}</span>
                        </p>
                        
                        <div class="flex items-start mb-4">
                            <div class="flex items-center h-5">
                                <input id="select-all" 
                                       type="checkbox" 
                                       x-model="selectAll"
                                       @change="toggleAll()"
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="select-all" class="font-medium text-gray-700">
                                    Select/Deselect All
                                </label>
                            </div>
                        </div>
                        
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
                                           x-model="subscriptions.{{ $key }}"
                                           x-init="subscriptions.{{ $key }} = {{ isset($currentSubscriptions[$key]) && $currentSubscriptions[$key] ? 'true' : 'false' }}"
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
                            <button type="submit" 
                                    @click.prevent="submitForm()"
                                    :disabled="loading"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                <span x-show="!loading">Save Preferences</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving...
                                </span>
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
        </div>
        
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>Need help? <a href="{{ route('contact') }}" class="font-medium text-blue-600 hover:text-blue-500">Contact support</a></p>
            <p class="mt-1">{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</div>
@endsection
