<form id="appointmentForm" method="POST" action="{{ route('appointments.store') }}" class="space-y-4" x-data="appointmentForm()">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Client Information -->
        <div class="space-y-4">
            <h4 class="text-md font-medium">Client Information</h4>

            <div>
                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                <input type="text"
                    id="client_name"
                    name="client_name"
                    x-model="formData.client_name"
                    value="{{ auth()->user()->name }}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md"
                    placeholder="Enter client name">
            </div>

            <div>
                <label for="client_email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email"
                    name="client_email"
                    id="client_email"
                    x-model="formData.client_email"
                    value="{{ auth()->user()->email }}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md"
                    placeholder="Enter client email (optional)">
            </div>

            <div>
                <label for="client_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel"
                    name="client_phone"
                    id="client_phone"
                    x-model="formData.client_phone"
                    value="{{ old('client_phone') }}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md"
                    placeholder="Enter client phone"
                    required
                    pattern="[0-9\-\+\(\)\s]+">
            </div>
        </div>


        <!-- Appointment Details -->
        <div class="space-y-4">
            <h4 class="text-md font-medium">Appointment Details</h4>
            <div>
                <label for="services" class="block text-sm font-medium text-gray-700">Services</label>
                <select id="services" name="service_ids[]" multiple x-model="selectedServices" @change="updateTotals()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    @forelse($services ?? [] as $service)
                        <option value="{{ $service->id }}" data-duration="{{ $service->duration }}" data-price="{{ $service->price }}">
                            {{ $service->name }} - ${{ number_format($service->price, 2) }} ({{ $service->duration }} min)
                        </option>
                    @empty
                        <option disabled>No services available</option>
                    @endforelse
                </select>
                <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple services</p>
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" name="date" id="date" value="{{ old('date', request('date', date('Y-m-d'))) }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md datepicker">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="time" name="start_time" id="start_time" x-model="startTime" @change="updateEndTime()" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md timepicker">
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md" readonly>
                </div>
            </div>

            <div>
                <label for="staff_id" class="block text-sm font-medium text-gray-700">Staff Member</label>
                <select id="staff_id" name="staff_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">-- Select Staff --</option>
                    @foreach($staff as $staffMember)
                        <option value="{{ $staffMember->id }}" {{ old('staff_id') == $staffMember->id ? 'selected' : '' }}>
                            {{ $staffMember->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="notes" name="notes" rows="2" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="border-t border-gray-200 pt-4 mt-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Total Duration:</p>
                    <p x-text="totalDuration + ' minutes'" class="font-medium">0 minutes</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Price:</p>
                    <p x-text="'$' + (typeof totalPrice !== 'undefined' ? totalPrice.toFixed(2) : '0.00')" class="font-medium">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('appointmentForm', () => ({
            // Form data model
            formData: {
                client_name: '{{ auth()->user()->name }}',
                client_email: '{{ auth()->user()->email }}',
                client_phone: '{{ old('client_phone') }}',
                start_time: '',
                end_time: ''
            },
            // Initialize selectedServices as an array
            selectedServices: {{ json_encode(old('service_ids', [])) }},
            startTime: '{{ old('start_time', '09:00') }}',
            endTime: '{{ old('end_time', '10:00') }}',
            totalDuration: 0,
            totalPrice: 0,

            // Initialize the component
            init() {
                // Initialize reactive properties
                this.totalDuration = 0;
                this.totalPrice = 0;

                // Set initial form data
                this.formData.start_time = this.startTime;
                this.formData.end_time = this.endTime;

                // Set initial values from old input if available
                @if(old('start_time'))
                    this.startTime = '{{ old('start_time') }}';
                    this.formData.start_time = '{{ old('start_time') }}';
                @endif

                @if(old('end_time'))
                    this.endTime = '{{ old('end_time') }}';
                    this.formData.end_time = '{{ old('end_time') }}';
                @endif

                // Initialize time pickers
                this.initTimePickers();

                // Initialize selected services and update totals
                if (this.selectedServices && this.selectedServices.length > 0) {
                    this.$nextTick(() => {
                        this.updateTotals();
                    });
                }

                // Debug: Log initial state
                console.log('Appointment form initialized', {
                    startTime: this.startTime,
                    endTime: this.endTime,
                    selectedServices: this.selectedServices,
                    totalDuration: this.totalDuration,
                    totalPrice: this.totalPrice
                });
            },

            initTimePickers() {
                // Start time picker
                flatpickr('#start_time', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                    minuteIncrement: 15,
                    defaultHour: 9,
                    defaultMinute: 0,
                    onChange: (selectedDates, dateStr) => {
                        this.startTime = dateStr;
                        this.formData.start_time = dateStr;
                        this.updateEndTime();
                    }
                });

                // End time picker (readonly, updated programmatically)
                flatpickr('#end_time', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                    minuteIncrement: 15,
                    defaultHour: 10,
                    defaultMinute: 0,
                    clickOpens: false
                });
            },

            updateEndTime() {
                if (!this.startTime) return;

                // Parse start time
                const [hours, minutes] = this.startTime.split(':').map(Number);
                const startDate = new Date();
                startDate.setHours(hours, minutes, 0, 0);

                // Add total duration to start time (default to 60 minutes if no services selected)
                const duration = this.totalDuration > 0 ? this.totalDuration : 60;
                const endDate = new Date(startDate.getTime() + duration * 60000);

                // Format end time as HH:MM
                this.endTime = endDate.toTimeString().substring(0, 5);
                this.formData.end_time = this.endTime;

                // Update end time input
                const endTimeInput = document.querySelector('#end_time');
                if (endTimeInput && endTimeInput._flatpickr) {
                    endTimeInput._flatpickr.setDate(this.endTime);
                }
            },

            updateTotals() {
                this.totalDuration = 0;
                this.totalPrice = 0;

                // Get all service options
                const serviceSelect = document.getElementById('services');
                if (!serviceSelect) return;

                // Calculate totals based on selected services
                this.selectedServices.forEach(serviceId => {
                    const option = serviceSelect.querySelector(`option[value="${serviceId}"]`);
                    if (option) {
                        const duration = parseInt(option.dataset.duration) || 0;
                        const price = parseFloat(option.dataset.price) || 0;

                        this.totalDuration += duration;
                        this.totalPrice += price;
                    }
                });

                // Update end time when services change
                this.updateEndTime();

                // Update the select element to reflect the model
                this.$nextTick(() => {
                    const options = serviceSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        const option = options[i];
                        option.selected = this.selectedServices.includes(option.value);
                    }
                });
            }
        }));
    });
    </script>

    <!-- Form Actions -->
    <div class="flex justify-between pt-4 border-t border-gray-200 mt-6">
        <button type="button" @click="$store.bookingModal.close()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            {{ __('Cancel') }}
        </button>
        <div class="flex space-x-3">
            <button type="button"
                    @click="checkAvailability"
                    :class="{'bg-blue-700': loading, 'bg-blue-600': !loading}"
                    :disabled="loading"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <span x-text="loading ? 'Checking...' : 'Check Availability'"></span>
            </button>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create Appointment
            </button>
        </div>
    </div>

    <div x-show="validationError" x-text="validationError" class="mt-2 text-red-600 text-sm"></div>
</form>
