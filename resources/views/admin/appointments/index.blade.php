<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Appointments') }}
            </h2>
            <a href="{{ route('web.appointments.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Appointment
            </a>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Date Picker -->
                    <div class="mb-6">
                        <form action="{{ route('web.appointments.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center">
                            <div class="w-full sm:w-64">
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                                <input type="date"
                                       name="date"
                                       id="date"
                                       value="{{ $selectedDate->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                       onchange="this.form.submit()">
                            </div>
                            <div class="w-full sm:w-48 mt-2 sm:mt-6">
                                <label for="staff" class="block text-sm font-medium text-gray-700 mb-1">Filter by Staff</label>
                                <select id="staff"
                                        name="staff"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                        onchange="this.form.submit()">
                                    <option value="">All Staff</option>
                                    @foreach($staff as $staffMember)
                                        <option value="{{ $staffMember->id }}" {{ request('staff') == $staffMember->id ? 'selected' : '' }}>
                                            {{ $staffMember->first_name }} {{ $staffMember->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <a href="#"
                               class="tab-link border-primary-500 text-primary-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                               data-tab="available">
                                Available Appointments
                            </a>
                            <a href="#"
                               class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                               data-tab="booked">
                                Booked Appointments
                            </a>
                        </nav>
                    </div>

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Available Appointments Tab Content -->
                    <div id="available-tab" class="tab-content">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Available Time Slots for {{ $selectedDate->format('F j, Y') }}</h3>

                        @if(empty($availableSlots[$selectedDate->format('Y-m-d')]))
                            <p class="text-gray-500">No available time slots found for this date.</p>
                        @else
                            <div class="space-y-6">
                                @foreach($availableSlots[$selectedDate->format('Y-m-d')] as $staffId => $slots)
                                    @php
                                        $staffMember = $staff->firstWhere('id', $staffId);
                                    @endphp
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ $staffMember->first_name }} {{ $staffMember->last_name }}</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($slots as $slot)
                                                @if($slot['is_available'])
                                                    <a href="{{ route('web.appointments.create', [
                                                        'staff_id' => $staffId,
                                                        'date' => $slot['date'],
                                                        'start_time' => $slot['start']
                                                    ]) }}"
                                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500">
                                                        {{ $slot['start'] }} - {{ $slot['end'] }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Booked Appointments Tab Content -->
                    <div id="booked-tab" class="tab-content hidden">
                        <div class="overflow-x-auto">
                            @if($appointments->isEmpty())
                                <p class="text-gray-500">No appointments found for this date.</p>
                            @else
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Client
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Staff
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Time
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Services
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($appointments as $appointment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $appointment->client->first_name }} {{ $appointment->client->last_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $appointment->client->email }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $appointment->staff->first_name }} {{ $appointment->staff->last_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $appointment->staff->position }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $appointment->start_time->format('g:i A') }} - {{ $appointment->end_time->format('g:i A') }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $appointment->start_time->format('M j, Y') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($appointment->services->take(2) as $service)
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                                                {{ $service->name }}
                                                            </span>
                                                        @endforeach
                                                        @if($appointment->services->count() > 2)
                                                            <span class="text-xs text-gray-500">+{{ $appointment->services->count() - 2 }} more</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $statusClasses = [
                                                            'scheduled' => 'bg-primary-100 text-blue-800',
                                                            'confirmed' => 'bg-green-100 text-green-800',
                                                            'completed' => 'bg-gray-100 text-gray-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'no_show' => 'bg-yellow-100 text-yellow-800',
                                                        ][$appointment->status] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses }}">
                                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('web.appointments.edit', $appointment->id) }}" class="text-primary-600 hover:text-primary-900 mr-3">Edit</a>
                                                    <a href="{{ route('web.appointments.show', $appointment->id) }}" class="text-gray-600 hover:text-gray-900">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                    No appointments found for this date.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                    @if($appointments->hasPages())
                        <div class="mt-4">
                            {{ $appointments->appends(['date' => $selectedDate->format('Y-m-d')])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabs = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Remove active class from all tabs and hide all content
                    tabs.forEach(t => t.classList.remove('border-primary-500', 'text-primary-600', 'hover:text-gray-700', 'hover:border-gray-300'));
                    tabContents.forEach(content => content.classList.add('hidden'));

                    // Add active class to clicked tab and show corresponding content
                    tab.classList.add('border-primary-500', 'text-primary-600');
                    tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    document.getElementById(`${tab.dataset.tab}-tab`).classList.remove('hidden');
                });
            });

            // Set default tab based on URL hash or default to 'available'
            const defaultTab = window.location.hash ? window.location.hash.substring(1) : 'available';
            const activeTab = document.querySelector(`.tab-link[data-tab="${defaultTab}"]`) || document.querySelector('.tab-link');
            activeTab.click();
        });
    </script>
    @endpush
</x-app-layout>
