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
            <div class="text-center">
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
                
                <div class="mt-6">
                    <p class="text-sm text-gray-500">
                        Changed your mind? <a href="{{ route('email.preferences', ['token' => 'preferences_token_here']) }}" class="font-medium text-blue-600 hover:text-blue-500">Update your preferences</a>
                    </p>
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
