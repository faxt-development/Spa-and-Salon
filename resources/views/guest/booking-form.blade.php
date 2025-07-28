@extends('layouts.app-content')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-primary-100 to-secondary-100">
    <div class="bg-primary-700 text-white text-center py-3">
        <div class="container mx-auto">
            <p class="font-medium">Book an appointment without creating an account. <span class="font-bold">Already have an account?</span> <a href="{{ route('login') }}" class="underline text-accent-200 hover:text-accent-600">Login here</a>.</p>
        </div>
    </div>

    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <div class="mb-6">
                    <a href="{{ route('guest.booking.index') }}" class="inline-flex items-center text-primary-600 hover:text-primary-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Back to Location Search
                    </a>
                </div>

                <div class="mb-6 bg-gray-50 rounded-lg p-4">
                    <h2 class="text-xl font-bold text-gray-900">{{ $location->name }}</h2>
                    @if($location->company)
                        <p class="text-gray-600">{{ $location->company->name }}</p>
                    @else
                        <p class="text-gray-600">Independent</p>
                    @endif
                    <p class="text-gray-600 mt-1">{{ $location->address_line_1 }}{{ $location->address_line_2 ? ', ' . $location->address_line_2 : '' }}</p>
                    <p class="text-gray-600">{{ $location->city }}, {{ $location->state }} {{ $location->postal_code }}</p>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center">Book Your Appointment</h1>

                <div id="booking-app" class="guest-booking-form">
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">1. Select Service</h2>
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach($services as $service)
                            <div class="service-option border rounded-lg p-4 cursor-pointer hover:border-primary-500 hover:bg-primary-50 transition-colors"
                                 data-service-id="{{ $service->id }}"
                                 data-service-name="{{ $service->name }}"
                                 data-service-duration="{{ $service->duration }}"
                                 data-service-price="{{ $service->price }}">
                                <div class="flex justify-between">
                                    <h3 class="font-medium">{{ $service->name }}</h3>
                                    <span class="text-primary-700">${{ number_format($service->price, 2) }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $service->duration }} minutes</p>
                                <p class="text-sm text-gray-500 mt-2">{{ $service->description }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">2. Select Date & Time</h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" id="appointment-date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
                                <div id="time-slots" class="grid grid-cols-3 gap-2">
                                    <p class="col-span-3 text-gray-500 text-sm">Please select a service and date first</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">3. Your Information</h2>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Special Requests (optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="flex items-center">
                                    <input type="checkbox" id="marketing_consent" name="marketing_consent" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-600">I consent to receive marketing emails and promotions</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-semibold">Appointment Summary</h3>
                                <p class="text-sm text-gray-600" id="summary-service">No service selected</p>
                                <p class="text-sm text-gray-600" id="summary-datetime">No date/time selected</p>
                                <p class="text-sm text-gray-600">Location: {{ $location->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total</p>
                                <p class="text-xl font-bold text-primary-700" id="summary-price">$0.00</p>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" id="book-button" class="bg-primary-600 text-white py-3 px-8 rounded-lg font-semibold hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Book Appointment
                            </button>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div id="success-message" style="display: none;" class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Appointment Booked!</h2>
                        <p class="text-lg text-gray-600 mb-6">Your appointment has been successfully scheduled.</p>
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-md mx-auto">
                            <p class="font-medium" id="success-service-name">Service Name</p>
                            <p id="success-date-time">Date and Time</p>
                            <p>Location: {{ $location->name }}</p>
                            <p class="mt-2">A confirmation email has been sent to your email address.</p>
                        </div>
                        <a href="{{ route('guest.booking.index') }}" class="inline-block bg-primary-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-primary-700 transition-colors">Book Another Appointment</a>
                    </div>

                    <!-- Duplicate Appointment Message -->
                    <div id="duplicate-message" style="display: none;" class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Appointment Already Exists</h2>
                        <p class="text-lg text-gray-600 mb-6">We found an existing appointment for you with the same service.</p>

                        <div id="duplicate-details" class="mb-6">
                            <!-- Duplicate appointment details will be inserted here -->
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 max-w-md mx-auto">
                            <p class="text-green-800 font-medium">✓ A confirmation email has been resent to your email address</p>
                            <p class="text-green-700 text-sm mt-1">Please check your inbox (and spam folder) for your appointment details.</p>
                        </div>

                        <div class="flex gap-4 justify-center">
                            <a href="{{ route('guest.booking.index') }}" class="inline-block bg-primary-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-primary-700 transition-colors">Book Another Service</a>
                            <button onclick="window.location.reload()" class="inline-block bg-gray-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-gray-700 transition-colors">Try Different Time</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables to store selected values
    let selectedService = null;
    let selectedDate = null;
    let selectedTime = null;
    let selectedStaffId = null;
    const locationId = {{ $location->id }};

    // Elements
    const serviceOptions = document.querySelectorAll('.service-option');
    const dateInput = document.getElementById('appointment-date');
    const timeSlots = document.getElementById('time-slots');
    const summaryService = document.getElementById('summary-service');
    const summaryDateTime = document.getElementById('summary-datetime');
    const summaryPrice = document.getElementById('summary-price');
    const bookButton = document.getElementById('book-button');

    // Set minimum date to today
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    dateInput.min = `${yyyy}-${mm}-${dd}`;

    // Service selection
    serviceOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all services
            serviceOptions.forEach(s => s.classList.remove('border-primary-500', 'bg-primary-50'));

            // Add active class to selected service
            this.classList.add('border-primary-500', 'bg-primary-50');

            // Store selected service data
            selectedService = {
                id: this.dataset.serviceId,
                name: this.dataset.serviceName,
                duration: this.dataset.serviceDuration,
                price: this.dataset.servicePrice
            };

            // Update summary
            summaryService.textContent = `${selectedService.name} (${selectedService.duration} min)`;
            summaryPrice.textContent = `$${parseFloat(selectedService.price).toFixed(2)}`;

            // Check if we can load time slots
            checkAndLoadTimeSlots();
        });
    });

    // Date selection
    dateInput.addEventListener('change', function() {
        selectedDate = this.value;
        summaryDateTime.textContent = selectedDate ? `${selectedDate}` : 'No date selected';

        // Check if we can load time slots
        checkAndLoadTimeSlots();
    });

    // Function to check if we can load time slots
    function checkAndLoadTimeSlots() {
        if (selectedService && selectedDate) {
            loadTimeSlots();
        }
    }

    // Function to load available time slots
    function loadTimeSlots() {
        timeSlots.innerHTML = '<p class="col-span-3 text-center">Loading available times...</p>';

        // Make API call to check availability
        fetch('/api/guest/check-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                service_id: selectedService.id,
                date: selectedDate,
                location_id: locationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.availability && data.data.availability.length > 0) {
                displayTimeSlots(data.data.availability);
            } else {
                timeSlots.innerHTML = '<p class="col-span-3 text-center text-red-500">No available time slots for this date. Please select another date.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching time slots:', error);
            timeSlots.innerHTML = '<p class="col-span-3 text-center text-red-500">Error loading time slots. Please try again.</p>';
        });
    }

    // Function to display time slots with staff information
    function displayTimeSlots(availability) {
        timeSlots.innerHTML = '';

        if (availability.length === 0) {
            timeSlots.innerHTML = '<p class="col-span-3 text-center text-red-500">No availability found for the selected date.</p>';
            return;
        }

        availability.forEach(staff => {
            // Create staff section
            const staffSection = document.createElement('div');
            staffSection.className = 'col-span-3 mb-4';

            // Staff header with name and photo
            const staffHeader = document.createElement('div');
            staffHeader.className = 'flex items-center mb-2';

            if (staff.staff_photo) {
                const staffPhoto = document.createElement('img');
                staffPhoto.src = staff.staff_photo;
                staffPhoto.alt = staff.staff_name;
                staffPhoto.className = 'w-8 h-8 rounded-full mr-2';
                staffHeader.appendChild(staffPhoto);
            }

            const staffName = document.createElement('h4');
            staffName.className = 'font-medium text-gray-900';
            staffName.textContent = staff.staff_name;
            staffHeader.appendChild(staffName);

            staffSection.appendChild(staffHeader);

            // Time slots container
            const slotsContainer = document.createElement('div');
            slotsContainer.className = 'grid grid-cols-3 gap-2';

            // Add available time slots
            staff.slots.forEach(slot => {
                if (!slot.is_available) return;

                const timeButton = document.createElement('button');
                timeButton.type = 'button';
                timeButton.className = 'time-slot py-2 px-3 border rounded text-sm hover:bg-primary-50 hover:border-primary-500';
                timeButton.textContent = slot.formatted_time.split(' - ')[0]; // Show just the start time
                timeButton.dataset.time = slot.start_time;
                timeButton.dataset.staffId = staff.staff_id;
                timeButton.title = `Book with ${staff.staff_name} at ${slot.formatted_time}`;

                timeButton.addEventListener('click', function() {
                    // Remove active class from all time slots
                    document.querySelectorAll('.time-slot').forEach(btn => {
                        btn.classList.remove('bg-primary-500', 'text-white');
                    });

                    // Add active class to selected time slot
                    this.classList.add('bg-primary-500', 'text-white');

                    // Store selected time and staff
                    selectedTime = this.dataset.time;
                    selectedStaffId = this.dataset.staffId;

                    // Update summary with full time range
                    const timeRange = this.title.split(' at ')[1];
                    summaryDateTime.textContent = `${selectedDate} at ${timeRange}`;

                    // Enable book button if all required fields are filled
                    checkFormCompletion();
                });

                slotsContainer.appendChild(timeButton);
            });

            staffSection.appendChild(slotsContainer);
            timeSlots.appendChild(staffSection);

            // Add a divider between staff members
            if (staff !== availability[availability.length - 1]) {
                const divider = document.createElement('hr');
                divider.className = 'my-4 border-gray-200';
                timeSlots.appendChild(divider);
            }
        });
    }

    // Function to check if all required fields are filled
    function checkFormCompletion() {
        const firstName = document.getElementById('first_name').value;
        const lastName = document.getElementById('last_name').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;

        if (selectedService && selectedDate && selectedTime && firstName && lastName && email && phone) {
            bookButton.disabled = false;
        } else {
            bookButton.disabled = true;
        }
    }

    // Add event listeners to form fields
    document.getElementById('first_name').addEventListener('input', checkFormCompletion);
    document.getElementById('last_name').addEventListener('input', checkFormCompletion);
    document.getElementById('email').addEventListener('input', checkFormCompletion);
    document.getElementById('phone').addEventListener('input', checkFormCompletion);

    // Book appointment button
    bookButton.addEventListener('click', function() {
        // Disable button to prevent double submission
        bookButton.disabled = true;
        bookButton.textContent = 'Processing...';

        const formData = {
            service_id: selectedService.id,
            staff_id: selectedStaffId,
            location_id: {{ $location->id }},
            date: selectedDate,
            time: selectedTime,
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            notes: document.getElementById('notes').value,
            marketing_consent: document.getElementById('marketing_consent').checked
        };

        fetch('/api/guest/book', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (response.status === 409) {
                // Handle duplicate appointment
                return response.json().then(data => {
                    if (data.duplicate) {
                        // Show duplicate appointment message
                        document.getElementById('guest-booking-form').style.display = 'none';
                        document.getElementById('duplicate-message').style.display = 'block';
                        document.getElementById('duplicate-details').innerHTML = `
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h3 class="font-semibold text-blue-900 mb-2">Your Existing Appointment</h3>
                                <p class="text-blue-800"><strong>Service:</strong> ${data.appointment.services[0].name}</p>
                                <p class="text-blue-800"><strong>Date & Time:</strong> ${new Date(data.appointment.start_time).toLocaleString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit'
                                })}</p>
                                <p class="text-blue-800"><strong>Staff:</strong> ${data.appointment.staff_name}</p>
                                <p class="text-blue-800"><strong>Duration:</strong> ${data.appointment.services[0].duration} minutes</p>
                            </div>
                        `;

                        // Scroll to top
                        window.scrollTo(0, 0);
                        return null;
                    }
                    return response.json();
                });
            } else if (response.status === 422) {
                // Handle time slot unavailable
                return response.json().then(data => {
                    // Show error message to user
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-4';
                    errorDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Time Slot Unavailable</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>${data.message}</p>
                                </div>
                            </div>
                        </div>
                    `;

                    // Insert error message at the top of the form
                    const bookingForm = document.getElementById('guest-booking-form');
                    bookingForm.insertBefore(errorDiv, bookingForm.firstChild);

                    // Re-enable the booking button
                    bookButton.disabled = false;
                    bookButton.textContent = 'Book Appointment';

                    // Scroll to top to show the error
                    window.scrollTo(0, 0);

                    // Refresh available time slots to show current availability
                    checkAndLoadTimeSlots();

                    return null;
                });
            }
            return response.json();
        })
        .then(data => {
            if (data === null) {
                // Already handled duplicate case above
                return;
            }

            if (data.success) {
                // Redirect to guest appointment confirmation page
                if (data.data && data.data.guest_link) {
                    window.location.href = data.data.guest_link;
                } else {
                    // Fallback to generic success
                    alert('Appointment booked successfully!');
                    window.location.href = '{{ route("guest.booking.index") }}';
                }
            } else {
                // Show error message
                alert('Error booking appointment: ' + (data.message || 'Please try again.'));
                bookButton.disabled = false;
                bookButton.textContent = 'Book Appointment';
            }
        })
        .catch(error => {
            console.error('Error booking appointment:', error);
            alert('Error booking appointment. Please try again.');
            bookButton.disabled = false;
            bookButton.textContent = 'Book Appointment';
        });
    });
});
</script>
@endpush
