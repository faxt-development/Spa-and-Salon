<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Appointment') }}
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Create New Appointment</h2>
                    <a href="#" onclick="window.history.back(); return false;" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Calendar
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong>Error!</strong> Please check the form for errors.
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="appointmentForm" method="POST" action="/appointments" class="space-y-6" x-data="appointmentForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Client Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">Client Information</h3>

                            <div>
                                <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Select Existing Client</label>
                                <select id="client_id" name="client_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients ?? [] as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->full_name }} ({{ $client->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center">
                                    <input id="new_client" name="new_client" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="new_client" class="ml-2 block text-sm text-gray-900">
                                        Create New Client
                                    </label>
                                </div>
                            </div>

                            <div id="new-client-fields" class="space-y-4 hidden">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <!-- Appointment Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">Appointment Details</h3>

                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" id="date" value="{{ old('date', request('date', date('Y-m-d'))) }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datepicker">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md timepicker">
                                </div>

                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md timepicker" readonly>
                                </div>
                            </div>

                            <div>
                                <label for="staff_id" class="block text-sm font-medium text-gray-700">Staff Member</label>
                                <select id="staff_id" name="staff_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($staff ?? [] as $staffMember)
                                        <option value="{{ $staffMember->id }}" {{ old('staff_id') == $staffMember->id ? 'selected' : '' }}>
                                            {{ $staffMember->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="services" class="block text-sm font-medium text-gray-700">Services</label>
                                <select id="services" name="service_ids[]" multiple class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    @foreach($services ?? [] as $service)
                                        <option value="{{ $service->id }}" data-duration="{{ $service->duration }}" data-price="{{ $service->price }}">
                                            {{ $service->name }} - ${{ number_format($service->price, 2) }} ({{ $service->duration }} min)
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple services</p>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-lg font-medium mb-4">Appointment Summary</h3>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Total Duration:</p>
                                    <p id="total-duration" class="font-medium">0 minutes</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Price:</p>
                                    <p id="total-price" class="font-medium">$0.00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mt-4">
                        <button type="button"
                            @click="checkAvailability"
                            :class="{'bg-blue-700': loading, 'bg-blue-600': !loading}"
                            :disabled="loading"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <span x-text="loading ? 'Checking...' : 'Check Availability'"></span>
                        </button>
                    </div>
                    <div x-show="validationError" x-text="validationError" class="mt-2 text-red-600 text-sm"></div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Create Appointment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Availability Modal -->
<div x-data="{ open: false, availabilityResults: '' }" x-cloak>
    <div x-show="open" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-transition>
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Available Time Slots</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-4" x-html="availabilityResults">
                <!-- Results will be inserted here -->
            </div>
        </div>
    </div>
</div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('appointmentForm', () => ({
            loading: false,
            validationError: '',

            checkAvailability() {
                this.loading = true;
                this.validationError = '';

                const date = document.getElementById('date').value;
                const staffId = document.getElementById('staff_id').value;
                const servicesSelect = document.getElementById('services');
                const serviceIds = Array.from(servicesSelect.selectedOptions).map(option => option.value);

                if (!date) {
                    this.validationError = 'Please select a date';
                    this.loading = false;
                    return;
                }

                if (serviceIds.length === 0) {
                    this.validationError = 'Please select at least one service';
                    this.loading = false;
                    return;
                }

                const availabilityModal = document.getElementById('availability-modal');
                const availabilityResults = document.getElementById('availability-results');

                availabilityResults.innerHTML = '<p class="text-center">Loading available time slots...</p>';
                availabilityModal.classList.remove('hidden');

                // Create a status message element to show network activity
                const statusMessage = document.createElement('div');
                statusMessage.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg';
                statusMessage.textContent = 'Checking availability...';
                document.body.appendChild(statusMessage);

                // Fetch available time slots from API
                fetch('/api/booking/availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        date: date,
                        staff_id: staffId || null,
                        service_ids: serviceIds
                    })
                })
                .then(response => {
                    // Remove status message
                    document.body.removeChild(statusMessage);

                    // Reset loading state
                    this.loading = false;

                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.available_slots && data.available_slots.length > 0) {
                        let html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">';

                        data.available_slots.forEach(slot => {
                            html += `
                                <button type="button" class="time-slot-btn p-2 border rounded text-center hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    data-time="${slot.time}" data-end-time="${slot.end_time}">
                                    ${slot.formatted_time}
                                </button>
                            `;
                        });

                        html += '</div>';
                        availabilityResults.innerHTML = html;

                        // Add event listeners to time slot buttons
                        document.querySelectorAll('.time-slot-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const startTimeInput = document.getElementById('start_time');
                                const endTimeInput = document.getElementById('end_time');
                                startTimeInput._flatpickr.setDate(this.dataset.time);
                                endTimeInput._flatpickr.setDate(this.dataset.endTime);
                                availabilityModal.classList.add('hidden');
                            });
                        });
                    } else {
                        availabilityResults.innerHTML = '<p class="text-center text-red-500">No available time slots found for the selected date and services.</p>';
                    }
                })
                .catch(error => {
                    // Reset loading state if not already done
                    this.loading = false;

                    // Remove status message if still present
                    if (document.body.contains(statusMessage)) {
                        document.body.removeChild(statusMessage);
                    }

                    console.error('Error checking availability:', error);
                    availabilityResults.innerHTML = `
                        <div class="text-center text-red-500">
                            <p class="font-bold">Error checking availability</p>
                            <p class="text-sm">${error.message || 'Please try again'}</p>
                            <pre class="mt-2 text-xs text-left bg-gray-100 p-2 rounded overflow-auto max-h-40">${JSON.stringify(error, null, 2)}</pre>
                        </div>`;
                });
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disableMobile: true
        });

        // Initialize time picker
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            minuteIncrement: 15,
            disableMobile: true
        });

        // Toggle new client fields
        const newClientCheckbox = document.getElementById('new_client');
        const newClientFields = document.getElementById('new-client-fields');
        const clientSelect = document.getElementById('client_id');

        newClientCheckbox.addEventListener('change', function() {
            if (this.checked) {
                newClientFields.classList.remove('hidden');
                clientSelect.disabled = true;
            } else {
                newClientFields.classList.add('hidden');
                clientSelect.disabled = false;
            }
        });

        // Calculate total duration and price when services change
        const servicesSelect = document.getElementById('services');
        const totalDurationEl = document.getElementById('total-duration');
        const totalPriceEl = document.getElementById('total-price');
        const endTimeInput = document.getElementById('end_time');
        const startTimeInput = document.getElementById('start_time');

        function updateTotals() {
            let totalDuration = 0;
            let totalPrice = 0;

            Array.from(servicesSelect.selectedOptions).forEach(option => {
                totalDuration += parseInt(option.dataset.duration);
                totalPrice += parseFloat(option.dataset.price);
            });

            totalDurationEl.textContent = `${totalDuration} minutes`;
            totalPriceEl.textContent = `$${totalPrice.toFixed(2)}`;

            // Update end time based on start time and duration
            if (startTimeInput.value) {
                const startTime = flatpickr.parseDate(startTimeInput.value, 'H:i');
                if (startTime) {
                    const endTime = new Date(startTime.getTime() + totalDuration * 60000);
                    const formattedEndTime = flatpickr.formatDate(endTime, 'H:i');
                    endTimeInput._flatpickr.setDate(formattedEndTime);
                }
            }
        }

        servicesSelect.addEventListener('change', updateTotals);
        startTimeInput.addEventListener('change', updateTotals);

        // Availability modal setup
        const availabilityModal = document.getElementById('availability-modal');
        const availabilityResults = document.getElementById('availability-results');
        const availabilityModalClose = document.getElementById('availability-modal-close');

        availabilityModalClose.addEventListener('click', function() {
            availabilityModal.classList.add('hidden');
        });
    });
</script>
@endpush
