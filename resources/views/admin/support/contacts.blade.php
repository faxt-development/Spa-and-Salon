@extends('layouts.admin')

@section('title', 'Emergency Contacts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Emergency Contacts</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-8">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <h2 class="text-lg font-semibold mb-2 text-gray-800">Why Set Up Emergency Contacts?</h2>
                <p class="text-gray-600">Emergency contacts are essential for business continuity. These contacts will be used in case of system outages, data emergencies, or other critical situations that require immediate attention.</p>
            </div>

            <form action="{{ route('admin.support.contacts.update') }}" method="POST" class="space-y-6">
                @csrf

                <div class="bg-primary-50 p-5 rounded-lg border border-blue-100">
                    <h3 class="text-lg font-semibold mb-4 text-blue-700">Primary Contact</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="primary_contact_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="primary_contact_name" id="primary_contact_name"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                value="{{ old('primary_contact_name', $company->settings()->where('key', 'emergency_contacts')->first() ? json_decode($company->settings()->where('key', 'emergency_contacts')->first()->value)->primary_contact_name ?? '' : '') }}"
                                required>
                            @error('primary_contact_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="primary_contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="primary_contact_phone" id="primary_contact_phone"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                value="{{ old('primary_contact_phone', $company->settings()->where('key', 'emergency_contacts')->first() ? json_decode($company->settings()->where('key', 'emergency_contacts')->first()->value)->primary_contact_phone ?? '' : '') }}"
                                required>
                            @error('primary_contact_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="primary_contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="primary_contact_email" id="primary_contact_email"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                value="{{ old('primary_contact_email', $company->settings()->where('key', 'emergency_contacts')->first() ? json_decode($company->settings()->where('key', 'emergency_contacts')->first()->value)->primary_contact_email ?? '' : '') }}"
                                required>
                            @error('primary_contact_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 p-5 rounded-lg border border-purple-100">
                    <h3 class="text-lg font-semibold mb-4 text-purple-700">Secondary Contact (Optional)</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="secondary_contact_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="secondary_contact_name" id="secondary_contact_name"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50"
                                value="{{ old('secondary_contact_name', $company->settings()->where('key', 'emergency_contacts')->first() ? json_decode($company->settings()->where('key', 'emergency_contacts')->first()->value)->secondary_contact_name ?? '' : '') }}">
                            @error('secondary_contact_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="secondary_contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="secondary_contact_phone" id="secondary_contact_phone"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50"
                                value="{{ old('secondary_contact_phone', $company->settings()->where('key', 'emergency_contacts')->first() ? json_decode($company->settings()->where('key', 'emergency_contacts')->first()->value)->secondary_contact_phone ?? '' : '') }}">
                            @error('secondary_contact_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="secondary_contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="secondary_contact_email" id="secondary_contact_email"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50"
                                value="{{ old('secondary_contact_email', $company->settings()->where('key', 'emergency_contacts')->first() ? json_decode($company->settings()->where('key', 'emergency_contacts')->first()->value)->secondary_contact_email ?? '' : '') }}">
                            @error('secondary_contact_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save Emergency Contacts
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h2 class="text-lg font-semibold mb-3 text-gray-800">Faxtina Support Contacts</h2>
            <p class="mb-4">For system-related emergencies, you can also contact Faxtina support directly:</p>
            <div class="space-y-2">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-gray-700">Email: <a href="mailto:emergency@faxtina.com" class="text-blue-600 hover:underline">emergency@faxtina.com</a></span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span class="text-gray-700">Emergency Hotline: <a href="tel:+18005559876" class="text-blue-600 hover:underline">1-800-555-9876</a></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
