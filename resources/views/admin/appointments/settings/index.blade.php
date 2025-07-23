@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Appointment Settings</h1>
        <a href="{{ route('admin.appointments.settings.create') }}" class="bg-accent-500 hover:bg-accent-600 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
            Add New Settings
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($settings->isEmpty())
            <div class="p-6 text-center">
                <p class="text-gray-600 mb-4">No appointment settings have been configured yet.</p>
                <p class="text-gray-600">Start by adding company-wide settings or location-specific settings.</p>
                <a href="{{ route('admin.appointments.settings.create') }}" class="mt-4 inline-block bg-accent-500 hover:bg-accent-600 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                    Configure Settings
                </a>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Time Slot Interval
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Booking Lead Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Auto-Confirm
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($settings as $setting)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $setting->location ? $setting->location->name : 'Company-wide' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $setting->time_slot_interval }} minutes</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $setting->formatted_booking_lead_time }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $setting->auto_confirm_appointments ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $setting->auto_confirm_appointments ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.appointments.settings.edit', $setting) }}" class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.appointments.settings.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete these settings?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">About Appointment Settings</h2>
        <p class="text-gray-600 mb-4">
            Appointment settings control how your booking system works for both your staff and customers. You can configure:
        </p>
        <ul class="list-disc pl-5 space-y-2 text-gray-600">
            <li>Time slot intervals and booking lead times</li>
            <li>Cancellation policies and fees</li>
            <li>Automatic confirmations and reminders</li>
            <li>Customer booking restrictions and waitlists</li>
            <li>Double-booking prevention and no-show tracking</li>
        </ul>
        <p class="text-gray-600 mt-4">
            You can set company-wide defaults or create specific settings for each location.
        </p>
    </div>
</div>
@endsection
