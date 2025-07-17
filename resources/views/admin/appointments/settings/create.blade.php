@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.appointments.settings') }}" class="mr-4 text-blue-500 hover:text-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
        </a>
        <h1 class="text-3xl font-bold">Create Appointment Settings</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.appointments.settings.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">Apply Settings To</label>
                <select id="location_id" name="location_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    @if(!$hasCompanyWideSettings)
                        <option value="">Company-wide (All Locations)</option>
                    @endif
                    @foreach($availableLocations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">🗓️ Core Appointment Settings</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="time_slot_interval" class="block text-sm font-medium text-gray-700 mb-1">Time Slot Interval (minutes)</label>
                        <input type="number" name="time_slot_interval" id="time_slot_interval" value="{{ old('time_slot_interval', 30) }}" min="5" max="120" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">How appointments are spaced (15, 30, 60 minutes)</p>
                        @error('time_slot_interval')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="booking_lead_time" class="block text-sm font-medium text-gray-700 mb-1">Booking Lead Time (minutes)</label>
                        <input type="number" name="booking_lead_time" id="booking_lead_time" value="{{ old('booking_lead_time', 60) }}" min="0" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Minimum notice required before booking</p>
                        @error('booking_lead_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="cancellation_notice" class="block text-sm font-medium text-gray-700 mb-1">Cancellation Notice (hours)</label>
                        <input type="number" name="cancellation_notice" id="cancellation_notice" value="{{ old('cancellation_notice', 24) }}" min="0" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Hours of notice required for cancellation</p>
                        @error('cancellation_notice')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <div class="flex items-start mb-2">
                            <div class="flex items-center h-5">
                                <input id="enforce_cancellation_fee" name="enforce_cancellation_fee" type="checkbox" value="1" {{ old('enforce_cancellation_fee') ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="enforce_cancellation_fee" class="text-sm font-medium text-gray-700">Enforce Cancellation Fee</label>
                            </div>
                        </div>
                        
                        <div id="cancellation_fee_container" class="{{ old('enforce_cancellation_fee') ? '' : 'hidden' }}">
                            <label for="cancellation_fee" class="block text-sm font-medium text-gray-700 mb-1">Cancellation Fee ($)</label>
                            <input type="number" name="cancellation_fee" id="cancellation_fee" value="{{ old('cancellation_fee', 0) }}" min="0" step="0.01" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('cancellation_fee')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">🧖 Service Configuration</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="default_padding_time" class="block text-sm font-medium text-gray-700 mb-1">Default Padding Time (minutes)</label>
                        <input type="number" name="default_padding_time" id="default_padding_time" value="{{ old('default_padding_time', 0) }}" min="0" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Buffer time between appointments</p>
                        @error('default_padding_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="allow_sequential_booking" name="allow_sequential_booking" type="checkbox" value="1" {{ old('allow_sequential_booking', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="allow_sequential_booking" class="text-sm font-medium text-gray-700">Allow Sequential Booking</label>
                            <p class="text-xs text-gray-500">Enable booking multiple services in sequence</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="allow_time_based_pricing" name="allow_time_based_pricing" type="checkbox" value="1" {{ old('allow_time_based_pricing') ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="allow_time_based_pricing" class="text-sm font-medium text-gray-700">Allow Time-Based Pricing</label>
                            <p class="text-xs text-gray-500">Different prices for peak vs. off-peak times</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">💬 Notifications & Reminders</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="auto_confirm_appointments" name="auto_confirm_appointments" type="checkbox" value="1" {{ old('auto_confirm_appointments', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="auto_confirm_appointments" class="text-sm font-medium text-gray-700">Auto-Confirm Appointments</label>
                            <p class="text-xs text-gray-500">Automatically confirm new appointments</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="send_customer_reminders" name="send_customer_reminders" type="checkbox" value="1" {{ old('send_customer_reminders', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="send_customer_reminders" class="text-sm font-medium text-gray-700">Send Customer Reminders</label>
                            <p class="text-xs text-gray-500">Email/SMS reminders for upcoming appointments</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="reminder_hours_before" class="block text-sm font-medium text-gray-700 mb-1">Reminder Hours Before</label>
                        <input type="number" name="reminder_hours_before" id="reminder_hours_before" value="{{ old('reminder_hours_before', 24) }}" min="1" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Hours before appointment to send reminder</p>
                        @error('reminder_hours_before')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="send_staff_notifications" name="send_staff_notifications" type="checkbox" value="1" {{ old('send_staff_notifications', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="send_staff_notifications" class="text-sm font-medium text-gray-700">Send Staff Notifications</label>
                            <p class="text-xs text-gray-500">Notify staff of new or changed appointments</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">🌐 Booking Experience</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="max_future_booking_days" class="block text-sm font-medium text-gray-700 mb-1">Max Future Booking Days</label>
                        <input type="number" name="max_future_booking_days" id="max_future_booking_days" value="{{ old('max_future_booking_days', 60) }}" min="1" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">How far in advance customers can book</p>
                        @error('max_future_booking_days')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="require_customer_login" name="require_customer_login" type="checkbox" value="1" {{ old('require_customer_login') ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="require_customer_login" class="text-sm font-medium text-gray-700">Require Customer Login</label>
                            <p class="text-xs text-gray-500">Customers must login to book appointments</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="allow_customer_reschedule" name="allow_customer_reschedule" type="checkbox" value="1" {{ old('allow_customer_reschedule', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="allow_customer_reschedule" class="text-sm font-medium text-gray-700">Allow Customer Reschedule</label>
                            <p class="text-xs text-gray-500">Let customers reschedule their appointments</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="reschedule_notice_hours" class="block text-sm font-medium text-gray-700 mb-1">Reschedule Notice Hours</label>
                        <input type="number" name="reschedule_notice_hours" id="reschedule_notice_hours" value="{{ old('reschedule_notice_hours', 24) }}" min="0" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Hours of notice required for rescheduling</p>
                        @error('reschedule_notice_hours')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">📊 Analytics & Safeguards</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="enable_waitlist" name="enable_waitlist" type="checkbox" value="1" {{ old('enable_waitlist') ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="enable_waitlist" class="text-sm font-medium text-gray-700">Enable Waitlist</label>
                            <p class="text-xs text-gray-500">Allow customers to join waitlist for full slots</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="prevent_double_booking" name="prevent_double_booking" type="checkbox" value="1" {{ old('prevent_double_booking', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="prevent_double_booking" class="text-sm font-medium text-gray-700">Prevent Double Booking</label>
                            <p class="text-xs text-gray-500">Prevent overlapping appointments</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="track_no_shows" name="track_no_shows" type="checkbox" value="1" {{ old('track_no_shows', true) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="track_no_shows" class="text-sm font-medium text-gray-700">Track No-Shows</label>
                            <p class="text-xs text-gray-500">Monitor and track customer no-shows</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="max_no_shows_before_warning" class="block text-sm font-medium text-gray-700 mb-1">Max No-Shows Before Warning</label>
                        <input type="number" name="max_no_shows_before_warning" id="max_no_shows_before_warning" value="{{ old('max_no_shows_before_warning', 2) }}" min="1" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Number of no-shows before warning</p>
                        @error('max_no_shows_before_warning')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <a href="{{ route('admin.appointments.settings') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const enforceCancellationFee = document.getElementById('enforce_cancellation_fee');
        const cancellationFeeContainer = document.getElementById('cancellation_fee_container');
        
        enforceCancellationFee.addEventListener('change', function() {
            if (this.checked) {
                cancellationFeeContainer.classList.remove('hidden');
            } else {
                cancellationFeeContainer.classList.add('hidden');
            }
        });
    });
</script>
@endpush
@endsection
