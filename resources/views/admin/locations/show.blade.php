@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.locations.index') }}" class="mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 hover:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold">{{ $location->name }}</h1>

        @if($location->is_primary)
            <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-blue-800">
                Primary Location
            </span>
        @endif

        @if(!$location->is_active)
            <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                Inactive
            </span>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold">Location Details</h2>
                </div>

                <div class="p-6">
                    @if($location->description)
                        <div class="mb-6">
                            <p class="text-gray-700">{{ $location->description }}</p>
                        </div>
                        <hr class="my-4">
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-md font-semibold mb-2">Address</h3>
                            <p class="text-gray-700">{{ $location->address_line_1 }}</p>
                            @if($location->address_line_2)
                                <p class="text-gray-700">{{ $location->address_line_2 }}</p>
                            @endif
                            <p class="text-gray-700">{{ $location->city }}, {{ $location->state }} {{ $location->postal_code }}</p>
                            <p class="text-gray-700">{{ $location->country }}</p>
                        </div>

                        <div>
                            <h3 class="text-md font-semibold mb-2">Contact Information</h3>
                            @if($location->contact_name)
                                <p class="text-gray-700"><span class="font-medium">Contact:</span> {{ $location->contact_name }}</p>
                            @endif
                            @if($location->contact_email)
                                <p class="text-gray-700"><span class="font-medium">Email:</span> {{ $location->contact_email }}</p>
                            @endif
                            @if($location->contact_phone)
                                <p class="text-gray-700"><span class="font-medium">Phone:</span> {{ $location->contact_phone }}</p>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-md font-semibold mb-2">Settings</h3>
                            <p class="text-gray-700"><span class="font-medium">Location Code:</span> {{ $location->code }}</p>
                            <p class="text-gray-700"><span class="font-medium">Timezone:</span> {{ $location->timezone }}</p>
                            <p class="text-gray-700"><span class="font-medium">Currency:</span> {{ $location->currency }}</p>
                        </div>

                        @if($location->notes)
                            <div>
                                <h3 class="text-md font-semibold mb-2">Notes</h3>
                                <p class="text-gray-700">{{ $location->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Staff Section -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Staff at this Location</h2>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Manage Staff</a>
                </div>

                <div class="p-6">
                    @if($location->staff->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($location->staff as $staff)
                                <div class="flex items-center p-3 border rounded-md">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-bold">
                                        {{ substr($staff->name, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium">{{ $staff->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $staff->email }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No staff assigned to this location yet.</p>
                    @endif
                </div>
            </div>

            <!-- Services Section -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Services at this Location</h2>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Manage Services</a>
                </div>

                <div class="p-6">
                    @if($location->services->count() > 0)
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($location->services as $service)
                                <div class="flex justify-between items-center p-3 border rounded-md">
                                    <div>
                                        <p class="font-medium">{{ $service->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $service->duration }} minutes</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">{{ $service->price_formatted }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No services available at this location yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold">Actions</h2>
                </div>

                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.locations.edit', $location) }}" class="block w-full bg-primary-500 hover:bg-primary-600 text-white text-center font-bold py-2 px-4 rounded">
                            Edit Location
                        </a>

                        @if(!$location->is_primary)
                            <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="block w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white text-center font-bold py-2 px-4 rounded"
                                        onclick="return confirm('Are you sure you want to delete this location?')">
                                    Delete Location
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="mt-6">
                        <h3 class="text-md font-semibold mb-2">Quick Stats</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Staff Members:</span>
                                <span class="font-medium">{{ $location->staff->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Services:</span>
                                <span class="font-medium">{{ $location->services->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Appointments (Today):</span>
                                <span class="font-medium">{{ $location->appointments->where('date', now()->toDateString())->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold">Business Hours</h2>
                </div>

                <div class="p-6">
                    @if($location->business_hours)
                        <div class="space-y-2">
                            @foreach(json_decode($location->business_hours, true) ?? [] as $day => $hours)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ ucfirst($day) }}:</span>
                                    <span class="font-medium">
                                        @if(isset($hours['open']) && isset($hours['close']))
                                            {{ $hours['open'] }} - {{ $hours['close'] }}
                                        @else
                                            Closed
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Business hours not set.</p>
                        <a href="{{ route('admin.locations.edit', $location) }}" class="text-blue-600 hover:text-blue-800 text-sm">Set business hours</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
