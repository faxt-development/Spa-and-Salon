@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Business Settings</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.company.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                <input id="name" type="text" class="form-input w-full rounded-md shadow-sm @error('name') border-red-500 @enderror"
                    name="name" value="{{ old('name', $company->name) }}" required autocomplete="organization" autofocus>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input id="address" type="text" class="form-input w-full rounded-md shadow-sm @error('address') border-red-500 @enderror"
                    name="address" value="{{ old('address', $company->address) }}" required autocomplete="street-address">
                @error('address')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input id="city" type="text" class="form-input w-full rounded-md shadow-sm @error('city') border-red-500 @enderror"
                        name="city" value="{{ old('city', $company->city) }}" required autocomplete="address-level2">
                    @error('city')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                    <input id="state" type="text" class="form-input w-full rounded-md shadow-sm @error('state') border-red-500 @enderror"
                        name="state" value="{{ old('state', $company->state) }}" required autocomplete="address-level1">
                    @error('state')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="zip" class="block text-sm font-medium text-gray-700 mb-1">ZIP/Postal Code</label>
                    <input id="zip" type="text" class="form-input w-full rounded-md shadow-sm @error('zip') border-red-500 @enderror"
                        name="zip" value="{{ old('zip', $company->zip) }}" required autocomplete="postal-code">
                    @error('zip')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input id="phone" type="text" class="form-input w-full rounded-md shadow-sm @error('phone') border-red-500 @enderror"
                        name="phone" value="{{ old('phone', $company->phone) }}" required autocomplete="tel">
                    @error('phone')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website (Optional)</label>
                <input id="website" type="url" class="form-input w-full rounded-md shadow-sm @error('website') border-red-500 @enderror"
                    name="website" value="{{ old('website', $company->website) }}" autocomplete="url">
                @error('website')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
