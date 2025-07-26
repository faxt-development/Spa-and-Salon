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
                    <p class="text-gray-600">{{ $location->company->name }}</p>
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
                        
                        <button id="book-appointment" class="w-full bg-primary-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Book Appointment
                        </button>
                        
                        <p class="text-sm text-gray-500 mt-4 text-center">
                            By booking an appointment, you agree to our 
                            <a href="{{ route('terms') }}" class="text-primary-600 hover:underline">Terms of Service</a> and 
                            <a href="{{ route('privacy') }}" class="text-primary-600 hover:underline">Privacy Policy</a>.
                        </p>
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
    const bookButton = document.getElementById('book-appointment');
    
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
            if (data.success && data.data.available_slots && data.data.available_slots.length > 0) {
                displayTimeSlots(data.data.available_slots);
            } else {
                timeSlots.innerHTML = '<p class="col-span-3 text-center text-red-500">No available time slots for this date. Please select another date.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching time slots:', error);
            timeSlots.innerHTML = '<p class="col-span-3 text-center text-red-500">Error loading time slots. Please try again.</p>';
        });
    }
    
    // Function to display time slots
    function displayTimeSlots(slots) {
        timeSlots.innerHTML = '';
        
        slots.forEach(slot => {
            const timeButton = document.createElement('button');
            timeButton.type = 'button';
            timeButton.className = 'time-slot py-2 px-3 border rounded text-sm hover:bg-primary-50 hover:border-primary-500';
            timeButton.textContent = slot.time;
            timeButton.dataset.time = slot.time;
            timeButton.dataset.staffId = slot.staff_id;
            
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
                
                // Update summary
                summaryDateTime.textContent = `${selectedDate} at ${selectedTime}`;
                
                // Enable book button if all required fields are filled
                checkFormCompletion();
            });
            
            timeSlots.appendChild(timeButton);
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
        
        // Collect form data
        const formData = {
            service_id: selectedService.id,
            staff_id: selectedStaffId,
            location_id: locationId,
            date: selectedDate,
            time: selectedTime,
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            notes: document.getElementById('notes').value
        };
        
        // Make API call to book appointment
        fetch('/api/guest/book', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const bookingApp = document.getElementById('booking-app');
                bookingApp.innerHTML = `
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Appointment Booked!</h2>
                        <p class="text-lg text-gray-600 mb-6">Your appointment has been successfully scheduled.</p>
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-md mx-auto">
                            <p class="font-medium">${selectedService.name}</p>
                            <p>${selectedDate} at ${selectedTime}</p>
                            <p>Location: {{ $location->name }}</p>
                            <p class="mt-2">A confirmation email has been sent to ${formData.email}</p>
                        </div>
                        <a href="/" class="inline-block bg-primary-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-primary-700 transition-colors">Return to Home</a>
                    </div>
                `;
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
