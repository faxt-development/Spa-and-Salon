@extends('layouts.admin')

@section('title', 'Staff Services Assignment')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Staff Services Assignment</h1>
        <a href="{{ route('admin.staff.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded">
            Back to Staff List
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">Select Staff Member</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($staff as $staffMember)
                    <div class="border rounded-lg p-4 hover:bg-primary-50 cursor-pointer staff-card {{ $loop->first ? 'bg-primary-50 border-blue-500' : '' }}"
                         data-staff-id="{{ $staffMember->id }}">
                        <div class="flex items-center">
                            @if ($staffMember->profile_image)
                                <img src="{{ Storage::url($staffMember->profile_image) }}" alt="{{ $staffMember->full_name }}"
                                     class="w-12 h-12 rounded-full object-cover mr-4">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                                    <span class="text-gray-600 text-lg">{{ substr($staffMember->first_name, 0, 1) }}{{ substr($staffMember->last_name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-semibold">{{ $staffMember->full_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $staffMember->position }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach ($staff as $staffMember)
        <div id="staff-services-{{ $staffMember->id }}" class="staff-services-container {{ $loop->first ? '' : 'hidden' }}">
            <form action="{{ route('admin.staff.update-services') }}" method="POST">
                @csrf
                <input type="hidden" name="staff_id" value="{{ $staffMember->id }}">

                <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Services for {{ $staffMember->full_name }}</h2>
                        <p class="text-gray-600 mb-4">Select the services this staff member can perform. You can also set custom prices or durations for specific services.</p>

                        @if ($services->isEmpty())
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">No services have been created yet. <a href="{{ route('admin.services.create') }}" class="underline">Create services</a> first.</span>
                            </div>
                        @else
                            <div class="mb-4">
                                <button type="button" class="bg-primary-100 hover:bg-primary-200 text-blue-700 font-semibold py-1 px-3 rounded text-sm mb-2 select-all-btn">
                                    Select All
                                </button>
                                <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-1 px-3 rounded text-sm mb-2 ml-2 deselect-all-btn">
                                    Deselect All
                                </button>
                            </div>

                            @foreach ($servicesByCategory as $category => $categoryServices)
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-800 mb-3">{{ $category }}</h3>
                                    <div class="border rounded-lg overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Assign
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Service
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Standard Price
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Price Override
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Standard Duration
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Duration Override
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($categoryServices as $service)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 service-checkbox"
                                                                {{ in_array($service->id, $staffServiceAssignments[$staffMember->id] ?? []) ? 'checked' : '' }}>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="text-sm font-medium text-gray-900">{{ $service->name }}</div>
                                                            @if ($service->description)
                                                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $service->description }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="text-sm text-gray-900">${{ number_format($service->price, 2) }}</div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="number" name="price_overrides[{{ $service->id }}]" step="0.01" min="0"
                                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                                                                placeholder="Custom price">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="text-sm text-gray-900">{{ $service->formatted_duration }}</div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="number" name="duration_overrides[{{ $service->id }}]" min="1"
                                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                                                                placeholder="Minutes">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            <div class="flex justify-end mt-6">
                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                                    Save Service Assignments
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Staff selection
        const staffCards = document.querySelectorAll('.staff-card');
        const serviceContainers = document.querySelectorAll('.staff-services-container');

        staffCards.forEach(card => {
            card.addEventListener('click', function() {
                const staffId = this.dataset.staffId;

                // Update active staff card
                staffCards.forEach(c => c.classList.remove('bg-primary-50', 'border-blue-500'));
                this.classList.add('bg-primary-50', 'border-blue-500');

                // Show corresponding services container
                serviceContainers.forEach(container => {
                    container.classList.add('hidden');
                });
                document.getElementById(`staff-services-${staffId}`).classList.remove('hidden');
            });
        });

        // Select/Deselect all buttons
        document.querySelectorAll('.select-all-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                form.querySelectorAll('.service-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                });
            });
        });

        document.querySelectorAll('.deselect-all-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                form.querySelectorAll('.service-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        });
    });
</script>
@endsection
