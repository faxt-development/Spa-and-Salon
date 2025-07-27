@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-primary-50 to-white">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Appointment Confirmed!</h1>
                <p class="text-lg text-gray-600">Your appointment has been successfully booked</p>
            </div>

            <!-- Appointment Details Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-primary-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white">Appointment Details</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Service Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Service</h3>
                            <div class="space-y-2">
                                @foreach($appointment->services as $service)
                                <div class="flex justify-between">
                                    <span class="text-gray-700">{{ $service->name }}</span>
                                    <span class="font-medium">${{ number_format($service->pivot->price, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Date & Time</h3>
                            <div class="space-y-2 text-gray-700">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $appointment->start_time->format('l, F j, Y') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $appointment->start_time->format('g:i A') }} - {{ $appointment->end_time->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-6">

                    <!-- Location Details -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Location</h3>
                        <div class="text-gray-700">
                            <p class="font-medium">{{ $appointment->staff->location->name }}</p>
                            <p>{{ $appointment->staff->location->address_line_1 }}</p>
                            @if($appointment->staff->location->address_line_2)
                            <p>{{ $appointment->staff->location->address_line_2 }}</p>
                            @endif
                            <p>{{ $appointment->staff->location->city }}, {{ $appointment->staff->location->state }} {{ $appointment->staff->location->postal_code }}</p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <!-- Staff Details -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">With</h3>
                        <div class="flex items-center">
                            @if($appointment->staff->photo_url)
                            <img src="{{ $appointment->staff->photo_url }}" alt="{{ $appointment->staff->full_name }}" class="w-12 h-12 rounded-full mr-3">
                            @else
                            <div class="w-12 h-12 bg-gray-300 rounded-full mr-3 flex items-center justify-center">
                                <span class="text-gray-600 font-medium">{{ substr($appointment->staff->first_name, 0, 1) }}{{ substr($appointment->staff->last_name, 0, 1) }}</span>
                            </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $appointment->staff->full_name }}</p>
                                <p class="text-sm text-gray-600">{{ $appointment->staff->title ?? 'Professional' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($appointment->notes)
                    <hr class="my-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Notes</h3>
                        <p class="text-gray-700">{{ $appointment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Save Your Appointment Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Save Your Appointment</h3>
                        <p class="text-blue-800 mb-4">Save this link to view your appointment details anytime:</p>
                        <div class="bg-white rounded-md p-3 border border-blue-300">
                            <code class="text-sm text-blue-900 break-all">{{ route('guest.appointment.view', ['token' => $token]) }}</code>
                        </div>
                        <button onclick="copyLink()" class="mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- Account Creation Invitation -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white">Make Future Bookings Easier</h2>
                </div>
                
                <div class="p-6">
                    <div class="flex items-start">
                        <svg class="w-12 h-12 text-primary-600 mr-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Create a Free Account</h3>
                            <p class="text-gray-700 mb-4">
                                With an account, you can:
                            </p>
                            <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    View all your appointments in one place
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Reschedule or cancel appointments online
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Get appointment reminders
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save your preferences for faster booking
                                </li>
                            </ul>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 transition-colors">
                                    Create Free Account
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors">
                                    I Already Have an Account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="resendConfirmation()" class="inline-flex items-center justify-center px-6 py-3 border border-primary-600 text-primary-600 font-medium rounded-md hover:bg-primary-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Resend Confirmation Email
                </button>
                <a href="{{ route('guest.booking.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 transition-colors">
                    Book Another Appointment
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyLink() {
    const link = '{{ route("guest.appointment.view", ["token" => $token]) }}';
    navigator.clipboard.writeText(link).then(() => {
        // Show temporary success message
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    });
}

function resendConfirmation() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Sending...';
    button.disabled = true;

    fetch('{{ route("guest.appointment.resend-confirmation", ["token" => $token]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Sent!';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 3000);
        } else {
            throw new Error(data.message || 'Failed to send email');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert('Failed to resend confirmation email. Please try again later.');
    });
}
</script>
@endpush
