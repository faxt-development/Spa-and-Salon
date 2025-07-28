@extends('layouts.app-content')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Appointment Reminders</h1>
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
                        Configure how and when appointment reminders are sent to customers and staff.
                        Effective reminders can significantly reduce no-shows and help your business run smoothly.
                    </p>
                </div>

                <form action="{{ route('admin.appointments.reminders.update') }}" method="POST">
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
                                        <!-- Customer Reminders -->
                                        <div class="bg-white p-4 rounded border border-gray-200">
                                            <h4 class="font-medium text-gray-800 mb-3">Customer Reminders</h4>

                                            <div class="mb-4">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="settings[{{ $index }}][send_customer_reminders]" value="1" class="rounded border-gray-300 text-accent-500 shadow-sm focus:border-accent-300 focus:ring focus:ring-accent-200 focus:ring-opacity-50" {{ $setting->send_customer_reminders ? 'checked' : '' }}>
                                                    <span class="ml-2">Send appointment reminders to customers</span>
                                                </label>
                                            </div>

                                            <div class="mb-2">
                                                <label for="reminder_hours_before_{{ $index }}" class="block text-sm font-medium text-gray-700">Hours before appointment</label>
                                                <select id="reminder_hours_before_{{ $index }}" name="settings[{{ $index }}][reminder_hours_before]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                                    @foreach([1, 2, 3, 6, 12, 24, 48, 72] as $hours)
                                                        <option value="{{ $hours }}" {{ $setting->reminder_hours_before == $hours ? 'selected' : '' }}>
                                                            {{ $hours }} {{ Str::plural('hour', $hours) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <p class="text-sm text-gray-500 mt-2">
                                                Customers will receive an email reminder before their scheduled appointment.
                                            </p>
                                        </div>

                                        <!-- Staff Notifications -->
                                        <div class="bg-white p-4 rounded border border-gray-200">
                                            <h4 class="font-medium text-gray-800 mb-3">Staff Notifications</h4>

                                            <div class="mb-4">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="settings[{{ $index }}][send_staff_notifications]" value="1" class="rounded border-gray-300 text-accent-500 shadow-sm focus:border-accent-300 focus:ring focus:ring-accent-200 focus:ring-opacity-50" {{ $setting->send_staff_notifications ? 'checked' : '' }}>
                                                    <span class="ml-2">Send appointment notifications to staff</span>
                                                </label>
                                            </div>

                                            <p class="text-sm text-gray-500 mt-2">
                                                Staff members will receive notifications about new bookings, cancellations, and changes to their schedule.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="flex justify-end mt-6">
                                <button type="submit" class="px-4 py-2 bg-accent-500 text-white rounded-md hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-colors duration-200">
                                    Save Reminder Settings
                                </button>
                            </div>
                        @endif
                    </div>
                </form>

                <div class="mt-10 border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">About Appointment Reminders</h2>

                    <div class="prose max-w-none text-gray-600">
                        <p>
                            Appointment reminders are an effective way to reduce no-shows and last-minute cancellations.
                            Here are some best practices for using appointment reminders:
                        </p>

                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Send reminders 24-48 hours before the appointment to give clients enough time to reschedule if needed</li>
                            <li>Include essential information in reminders: date, time, location, service, and cancellation policy</li>
                            <li>Make it easy for clients to confirm, reschedule, or cancel their appointments</li>
                            <li>Consider sending multiple reminders (e.g., 48 hours and 2 hours before the appointment)</li>
                            <li>Personalize reminders with the client's name and specific appointment details</li>
                        </ul>

                        <p class="mt-4">
                            Reminder emails are automatically formatted with your company branding and include all necessary appointment details.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
