@php
    // Get necessary data for the booking form
    $clients = \App\Models\Client::orderBy('last_name')->get();
    $staff = \App\Models\User::role('staff')->orderBy('name')->get();
    $services = \App\Models\Service::orderBy('name')->get();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div x-data="{}" class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">
                {{ __('Welcome back, ') }} {{ Auth::user()->name }}!
            </h2>
        </div>
    </x-slot>

    <div x-data="dashboard()" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Status Messages -->
            <div x-show="message && message.text"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 p-4 rounded-md cursor-pointer"
                 :class="message && message.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'"
                 x-text="message?.text || ''"
                 @click="message = null"
                 role="alert"
                 x-cloak>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Upcoming Appointments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Appointments</h3>

                            <!-- Loading State -->
                            <div x-show="loading" class="text-center py-8">
                                <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="sr-only">Loading appointments...</span>
                            </div>

                            <!-- Empty State -->
                            <div x-show="!loading && upcomingAppointments.length === 0" class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming appointments</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by booking your next appointment</p>
                                <div class="mt-6">
                                    <button @click="$store.bookingModal.open()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        New Booking
                                    </button>
                                </div>
                            </div>

                            <!-- Appointments List -->
                            <div x-show="!loading && upcomingAppointments.length > 0" class="space-y-4">
                                <template x-for="appointment in upcomingAppointments" :key="appointment.id">
                                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium text-gray-900" x-text="appointment.service_name"></h4>
                                                <p class="text-sm text-gray-500" x-text="formatDateTime(appointment.appointment_date, appointment.appointment_time)"></p>
                                                <p class="text-sm text-gray-600 mt-1" x-text="'With ' + appointment.staff_name"></p>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button @click="rescheduleAppointment(appointment.id)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    Reschedule
                                                </button>
                                                <button @click="confirmCancel(appointment.id)" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                        <div x-show="appointment.notes" class="mt-2 text-sm text-gray-600" x-text="'Notes: ' + appointment.notes"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button @click="$store.bookingModal.open()" class="w-full flex items-center justify-between px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span>Book New Appointment</span>
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button @click="showGiftCardModal = true" class="w-full flex items-center justify-between px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span>Buy Gift Card</span>
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{
        flatpickrInstance: null,
        initFlatpickr() {
            // Clean up any existing instance
            if (this.flatpickrInstance) {
                this.flatpickrInstance.destroy();
                this.flatpickrInstance = null;
            }

            // Initialize date picker
            this.flatpickrInstance = flatpickr('.datepicker', {
                dateFormat: 'Y-m-d',
                minDate: 'today'
            });

            // Initialize time pickers
            flatpickr('.timepicker', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                minuteIncrement: 15
            });
        },
        destroyFlatpickr() {
            if (this.flatpickrInstance) {
                this.flatpickrInstance.destroy();
                this.flatpickrInstance = null;
            }
        }
    }" x-init="$watch('$store.bookingModal.open', value => {
        if (value) {
            setTimeout(() => initFlatpickr(), 50);
        } else {
            destroyFlatpickr();
        }
    });">

        <!-- Modal Overlay -->
        <div x-show="$store.bookingModal.open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true"
             @click.self="$store.bookingModal.close()">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="$store.bookingModal.open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="$store.bookingModal.close()"
                     aria-hidden="true">
                </div>

                <!-- Modal panel -->
                <div x-show="$store.bookingModal.open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="modal-panel inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6"
                     style="position: relative;">

                    <!-- Close button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button @click="$store.bookingModal.close()" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal content -->
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                New Appointment
                            </h3>

                            <!-- Form with date input -->
                            <div class="date-picker-wrapper relative">
                                @include('appointments.partials.form', [
                                    'staff' => $staff ?? [],
                                    'services' => $services ?? []
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            // Initialize bookingModal store
            Alpine.store('bookingModal', {
                open: false,
                open() {
                    this.open = true;
                },
                close() {
                    this.open = false;
                }
            });

            // Appointment Form Component
            Alpine.data('appointmentForm', () => ({
                loading: false,
                validationError: '',
                formData: {
                    client_name: '{{ auth()->user()->name }}',
                    client_email: '{{ auth()->user()->email }}',
                    client_phone: ''
                },

                init() {
                    // Initialize flatpickr for date picker when modal opens
                    this.$watch('$store.bookingModal.open', (isOpen) => {
                        if (isOpen) {
                            this.$nextTick(() => {
                                const dateInput = document.querySelector('.datepicker');
                                if (dateInput) {
                                    // Clean up any existing instances
                                    if (dateInput._flatpickr) {
                                        dateInput._flatpickr.destroy();
                                    }

                                    // Initialize flatpickr
                                    flatpickr(dateInput, {
                                        dateFormat: 'Y-m-d',
                                        minDate: 'today',
                                        disableMobile: true,
                                        static: true,
                                        appendTo: dateInput.closest('.modal-panel'),
                                        position: 'auto',
                                        onOpen: (selectedDates, dateStr, instance) => {
                                            const calendar = instance.calendarContainer;
                                            if (calendar) {
                                                calendar.style.position = 'absolute';
                                                calendar.style.zIndex = '9999';
                                            }
                                        }
                                    });
                                }

                                // Initialize flatpickr for time picker
                                const timeInput = document.querySelector('.timepicker');
                                if (timeInput) {
                                    // Clean up any existing instances
                                    if (timeInput._flatpickr) {
                                        timeInput._flatpickr.destroy();
                                    }

                                    flatpickr(timeInput, {
                                        enableTime: true,
                                        noCalendar: true,
                                        dateFormat: 'H:i',
                                        time_24hr: true,
                                        minuteIncrement: 15,
                                        disableMobile: true,
                                        appendTo: timeInput.closest('.modal-panel'),
                                        position: 'auto',
                                        onOpen: (selectedDates, dateStr, instance) => {
                                            const calendar = instance.calendarContainer;
                                            if (calendar) {
                                                calendar.style.position = 'absolute';
                                                calendar.style.zIndex = '9999';
                                            }
                                        }
                                    });
                                }
                            });
                        } else {
                            // Clean up flatpickr instances when modal closes
                            const dateInput = document.querySelector('.datepicker');
                            if (dateInput && dateInput._flatpickr) {
                                dateInput._flatpickr.destroy();
                            }

                            const timeInput = document.querySelector('.timepicker');
                            if (timeInput && timeInput._flatpickr) {
                                timeInput._flatpickr.destroy();
                            }
                        }
                    });

                    // Initialize form data from inputs if they exist
                    const clientNameInput = document.getElementById('client_name');
                    const clientEmailInput = document.getElementById('client_email');
                    const clientPhoneInput = document.getElementById('client_phone');

                    if (clientNameInput) this.formData.client_name = clientNameInput.value;
                    if (clientEmailInput) this.formData.client_email = clientEmailInput.value;
                    if (clientPhoneInput) this.formData.client_phone = clientPhoneInput.value;


                    // Calculate total duration and price when services change
                    const servicesSelect = document.getElementById('services');
                    const totalDurationEl = document.getElementById('total-duration');
                    const totalPriceEl = document.getElementById('total-price');
                    const endTimeInput = document.getElementById('end_time');
                    const startTimeInput = document.getElementById('start_time');

                    const updateTotals = () => {
                        let totalDuration = 0;
                        let totalPrice = 0;

                        if (servicesSelect) {
                            Array.from(servicesSelect.selectedOptions).forEach(option => {
                                totalDuration += parseInt(option.dataset.duration || 0);
                                totalPrice += parseFloat(option.dataset.price || 0);
                            });
                        }


                        if (totalDurationEl) totalDurationEl.textContent = `${totalDuration} minutes`;
                        if (totalPriceEl) totalPriceEl.textContent = `$${totalPrice.toFixed(2)}`;

                        // Update end time based on start time and duration
                        if (startTimeInput && startTimeInput.value && endTimeInput) {
                            const startTime = flatpickr.parseDate(startTimeInput.value, 'H:i');
                            if (startTime) {
                                const endTime = new Date(startTime.getTime() + totalDuration * 60000);
                                const formattedEndTime = flatpickr.formatDate(endTime, 'H:i');
                                endTimeInput._flatpickr.setDate(formattedEndTime);
                            }
                        }
                    };

                    if (servicesSelect) servicesSelect.addEventListener('change', updateTotals);
                    if (startTimeInput) startTimeInput.addEventListener('change', updateTotals);

                    // Handle form submission
                    const form = document.getElementById('appointmentForm');
                    if (form) {
                        form.addEventListener('submit', async (e) => {
                            e.preventDefault();
                            this.loading = true;
                            this.validationError = '';

                            try {
                                const response = await fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Authorization': 'Bearer {{ session()->get('api_token') }}',
                                    },
                                    body: JSON.stringify(this.formData)
                                });

                                const data = await response.json();

                                if (response.ok) {
                                    // Close the modal on success
                                    window.showBookingModal.value = false;

                                    // Show success message
                                    this.showMessage('Appointment booked successfully!');

                                    // Reset the form
                                    form.reset();
                                    this.formData = {
                                        client_name: '{{ auth()->user()->name }}',
                                        client_email: '{{ auth()->user()->email }}',
                                        client_phone: ''
                                    };

                                    // Refresh the appointments list
                                    this.fetchUpcomingAppointments();
                                } else {
                                    // Handle validation errors
                                    if (data.errors) {
                                        this.validationError = Object.values(data.errors).join(' ');
                                    } else {
                                        this.validationError = data.message || 'An error occurred while booking the appointment.';
                                    }
                                    // Scroll to top to show error
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                this.validationError = 'An unexpected error occurred. Please try again.';
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } finally {
                                this.loading = false;
                            }

                            try {
                                // Create FormData from the form
                                const formData = new FormData(form);

                                // Add client information from the Alpine.js data
                                formData.set('client_name', this.formData.client_name);
                                formData.set('client_email', this.formData.client_email);
                                formData.set('client_phone', this.formData.client_phone);

                                const response = await fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Authorization': 'Bearer {{ session()->get('api_token') }}'
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (!response.ok) {
                                    console.log(data.message || 'Something went wrong while saving the appointment');
                                  //  throw new Error(data.message || 'Something went wrong while saving the appointment');
                               return;
                                }

                                // Success - show success message and close modal
                                window.showBookingModal.value = false;

                                // Show success message
                                const messageContainer = document.querySelector('[x-data="dashboard()"]');
                                if (messageContainer) {
                                    messageContainer.__x.$data.message = {
                                        text: 'Appointment booked successfully!',
                                        type: 'success'
                                    };

                                    // Clear the message after 5 seconds
                                    setTimeout(() => {
                                        messageContainer.__x.$data.message = '';
                                    }, 5000);
                                }

                                // Reload the page to show the new appointment
                                window.location.reload();
                            } catch (error) {
                                this.validationError = error.message || 'An error occurred while saving the appointment';
                                console.error('Error:', error);

                                // Scroll to the top of the form to show the error
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } finally {
                                this.loading = false;
                            }
                        });
                    }
                },

                async checkAvailability() {
                    this.loading = true;
                    this.validationError = '';

                    const date = document.getElementById('date')?.value;
                    const staffId = document.getElementById('staff_id')?.value;
                    const servicesSelect = document.getElementById('services');
                    const serviceIds = servicesSelect ? Array.from(servicesSelect.selectedOptions).map(option => option.value) : [];

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


                    const availabilityResults = document.getElementById('availability-results');
                    if (availabilityResults) {
                        availabilityResults.innerHTML = '<p class="text-center">Loading available time slots...</p>';
                    }

                    // Show loading state
                    const loadingToast = document.createElement('div');
                    loadingToast.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg';
                    loadingToast.textContent = 'Checking availability...';
                    document.body.appendChild(loadingToast);

                    try {
                        // Fetch available time slots from API
                        const response = await fetch('/api/booking/availability', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                date: date,
                                staff_id: staffId || null,
                                service_ids: serviceIds
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to check availability');
                        }

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

                            if (availabilityResults) {
                                availabilityResults.innerHTML = html;

                                // Add event listeners to time slot buttons
                                document.querySelectorAll('.time-slot-btn').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const startTimeInput = document.getElementById('start_time');
                                        const endTimeInput = document.getElementById('end_time');
                                        if (startTimeInput && startTimeInput._flatpickr) {
                                            startTimeInput._flatpickr.setDate(this.dataset.time);
                                        }
                                        if (endTimeInput && endTimeInput._flatpickr) {
                                            endTimeInput._flatpickr.setDate(this.dataset.endTime);
                                        }

                                        // Hide the availability results
                                        const availabilityModal = document.querySelector('[x-data]');
                                        if (availabilityModal) {
                                            availabilityModal._x_dataStack[0].open = false;
                                        }
                                    });
                                });
                            }
                        } else {
                            if (availabilityResults) {
                                availabilityResults.innerHTML = '<p class="text-center text-red-500">No available time slots found for the selected date and services.</p>';
                            }
                        }
                    } catch (error) {
                        console.error('Error checking availability:', error);
                        if (availabilityResults) {
                            availabilityResults.innerHTML = `
                                <div class="text-center text-red-500">
                                    <p class="font-bold">Error checking availability</p>
                                    <p class="text-sm">${error.message || 'Please try again'}</p>
                                </div>`;
                        }
                    } finally {
                        this.loading = false;
                        if (loadingToast && loadingToast.parentNode) {
                            document.body.removeChild(loadingToast);
                        }
                    }
                }
            }));

            // Dashboard Component
            Alpine.data('dashboard', () => ({
                loading: true,
                message: null,
                upcomingAppointments: [],

                init() {
                    this.fetchUpcomingAppointments();
                },

                async fetchUpcomingAppointments() {
                    try {
                        this.loading = true;
                        this.message = null;

                        // Fetch appointments from the API with proper headers for Sanctum
                        const response = await fetch('/api/client/appointments', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Authorization': 'Bearer {{ session()->get('api_token') }}'
                            },
                            credentials: 'include' // Required for Sanctum session-based auth
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            console.error('API Error Response:', data);
                            throw new Error(data.message || 'Failed to fetch appointments');
                        }

                        this.upcomingAppointments = data.appointments || [];

                        if (this.upcomingAppointments.length === 0) {
                            this.message = 'No upcoming appointments found.';
                        }

                        if (this.upcomingAppointments.length === 0) {
                            this.showMessage('You have no upcoming appointments.', 'info');
                        }
                    } catch (error) {
                        console.error('Error fetching appointments:', error);
                        this.showMessage('Error loading appointments. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                formatDateTime(dateStr, timeStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    const options = { weekday: 'short', month: 'short', day: 'numeric' };
                    return `${date.toLocaleDateString('en-US', options)} at ${timeStr || ''}`;
                },

                rescheduleAppointment(appointmentId) {
                    // Implement reschedule functionality
                    this.showMessage('Reschedule functionality coming soon!', 'info');
                },

                confirmCancel(appointmentId) {
                    if (confirm('Are you sure you want to cancel this appointment?')) {
                        this.cancelAppointment(appointmentId);
                    }
                },

                async cancelAppointment(appointmentId) {
                    try {
                        // Simulate API call
                        await new Promise(resolve => setTimeout(resolve, 500));

                        // Remove from local state
                        this.upcomingAppointments = this.upcomingAppointments.filter(
                            appt => appt.id !== appointmentId
                        );

                        this.showMessage('Appointment cancelled successfully', 'success');
                    } catch (error) {
                        this.showMessage('Error cancelling appointment', 'error');
                        console.error('Error cancelling appointment:', error);
                    }
                },

                showMessage(text, type = 'info') {
                    this.message = { text, type };
                    setTimeout(() => {
                        this.message = null;
                    }, 5000);
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
