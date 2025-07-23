<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Appointment Details') }}
            </h2>
            <div class="flex space-x-2">
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Confirmed
                </span>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Client Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Client Information
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-600 text-xl font-medium">{{ substr($appointment->client->full_name, 0, 1) }}</span>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900">{{ $appointment->client->full_name }}</h4>
                            <div class="mt-1 text-sm text-gray-500">
                                <p>{{ $appointment->client->phone ?? 'No phone number' }}</p>
                                <p>{{ $appointment->client->email }}</p>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    VIP Client
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-blue-800">
                                    Last visit: 2 weeks ago
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Notes</h4>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-red-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <span>Fragrance sensitivity</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-red-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <span>Latex allergy</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-blue-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h2a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <span>Client Preferences: Prefers natural products, likes calming music</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Appointment Details
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $appointment->start_time->format('m/d/Y') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $appointment->start_time->format('h:i A') }} - {{ $appointment->end_time->format('h:i A') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Duration</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $appointment->start_time->diffInHours($appointment->end_time) }}h {{ $appointment->start_time->diffInMinutes($appointment->end_time) % 60 }}m
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Assigned Stylist</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $appointment->staff->full_name }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Station/Room</dt>
                            <dd class="mt-1 text-sm text-gray-900">Station 3</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Services -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Services ({{ $appointment->services->count() }})
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($appointment->services as $service)
                        <div class="border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $service->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $service->duration }} minutes</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">${{ number_format($service->price, 2) }}</p>
                                </div>
                            </div>
                            @if(!empty($service->pivot->notes))
                            <div class="mt-2 text-sm text-gray-600 bg-gray-50 p-2 rounded">
                                <p>{{ $service->pivot->notes }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach

                        <!-- Total -->
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <div class="flex justify-between">
                                <span class="font-medium">Total ({{ $appointment->services->count() }} {{ Str::plural('service', $appointment->services->count()) }})</span>
                                <span class="font-medium">${{ number_format($appointment->services->sum('price'), 2) }}</span>
                            </div>
                    </div>
                </div>
            </div>

            <!-- Special Requirements & Notes -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Special Requirements & Notes
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-sm text-gray-600">
                        @if($appointment->notes)
                            <p>{{ $appointment->notes }}</p>
                        @else
                            <p class="text-gray-400 italic">No special requirements or notes.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Payment Information
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-sm text-gray-600">
                        @if($appointment->payment_status)
                            <p>Payment Status: <span class="font-medium">{{ ucfirst($appointment->payment_status) }}</span></p>
                            @if($appointment->payment_method)
                                <p class="mt-1">Payment Method: <span class="font-medium">{{ ucfirst($appointment->payment_method) }}</span></p>
                            @endif
                            @if($appointment->amount_paid)
                                <p class="mt-1">Amount Paid: <span class="font-medium">${{ number_format($appointment->amount_paid / 100, 2) }}</span></p>
                            @endif
                        @else
                            <p class="text-gray-400 italic">No payment information available.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-6">
                <div>
                    @if($appointment->status == 'scheduled' || $appointment->status == 'confirmed')
                    <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel Appointment
                    </button>
                    @endif
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('web.appointments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Calendar
                    </a>
                    <a href="{{ route('web.appointments.edit', $appointment->id) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div id="cancel-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Cancel Appointment</h3>
            <form action="{{ route('web.appointments.cancel', $appointment->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Cancellation</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="mt-1 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Close
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
