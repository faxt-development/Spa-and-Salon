@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.locations.index') }}" class="mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 hover:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold">Edit Location: {{ $location->name }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.locations.update', $location) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="col-span-2">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Basic Information</h2>
                </div>

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Location Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $location->name) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Location Code</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $location->code) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">A unique code for this location (max 10 characters).</p>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4 col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $location->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Information -->
                <div class="col-span-2">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2 mt-6">Contact Information</h2>
                </div>

                <div class="mb-4">
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                    <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $location->contact_name) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $location->contact_email) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $location->contact_phone) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('contact_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="col-span-2">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2 mt-6">Address</h2>
                </div>

                <div class="mb-4 col-span-2">
                    <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1">Address Line 1 *</label>
                    <input type="text" name="address_line_1" id="address_line_1" value="{{ old('address_line_1', $location->address_line_1) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('address_line_1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4 col-span-2">
                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                    <input type="text" name="address_line_2" id="address_line_2" value="{{ old('address_line_2', $location->address_line_2) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('address_line_2')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $location->city) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province *</label>
                    <input type="text" name="state" id="state" value="{{ old('state', $location->state) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('state')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code *</label>
                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $location->postal_code) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country *</label>
                    <select name="country" id="country" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="US" {{ old('country', $location->country) == 'US' ? 'selected' : '' }}>United States</option>
                        <option value="CA" {{ old('country', $location->country) == 'CA' ? 'selected' : '' }}>Canada</option>
                        <option value="GB" {{ old('country', $location->country) == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="AU" {{ old('country', $location->country) == 'AU' ? 'selected' : '' }}>Australia</option>
                    </select>
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Settings -->
                <div class="col-span-2">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2 mt-6">Settings</h2>
                </div>

                <div class="mb-4">
                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone *</label>
                    <select name="timezone" id="timezone" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($timezones as $tz => $tzName)
                            <option value="{{ $tz }}" {{ old('timezone', $location->timezone) == $tz ? 'selected' : '' }}>
                                {{ $tzName }}
                            </option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
                    <select name="currency" id="currency" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ old('currency', $location->currency) == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Inactive locations won't be available for booking.</p>
                </div>

                <div class="mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_primary" id="is_primary" value="1" {{ old('is_primary', $location->is_primary) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_primary" class="ml-2 block text-sm text-gray-900">
                            Set as Primary Location
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">The primary location is used as the default for new staff and services.</p>
                </div>

                <div class="mb-4 col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $location->notes) }}</textarea>
                </div>
                
                <!-- Business Hours Section -->
                <div class="col-span-2" id="business-hours">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2 mt-6">Business Hours</h2>
                </div>
                
                <div class="col-span-2 space-y-4">
                    @php
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        $businessHours = old('business_hours', $location->business_hours) ?? [];
                    @endphp
                    
                    @foreach($days as $day)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <div class="font-medium capitalize">{{ $day }}</div>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                        id="{{ $day }}_open" 
                                        name="business_hours[{{ $day }}][is_open]" 
                                        value="1" 
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        {{ isset($businessHours[$day]) && isset($businessHours[$day]['open']) ? 'checked' : '' }}
                                        onchange="toggleHoursFields('{{ $day }}')">
                                    <label for="{{ $day }}_open" class="ml-2 block text-sm text-gray-900">Open</label>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2" id="{{ $day }}_hours_container">
                                <div>
                                    <label for="{{ $day }}_open_time" class="block text-xs text-gray-700">Open Time</label>
                                    <input type="time" 
                                        id="{{ $day }}_open_time" 
                                        name="business_hours[{{ $day }}][open]" 
                                        value="{{ $businessHours[$day]['open'] ?? '09:00' }}" 
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="{{ $day }}_close_time" class="block text-xs text-gray-700">Close Time</label>
                                    <input type="time" 
                                        id="{{ $day }}_close_time" 
                                        name="business_hours[{{ $day }}][close]" 
                                        value="{{ $businessHours[$day]['close'] ?? '17:00' }}" 
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.locations.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Update Location
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
