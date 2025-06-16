<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Appointments') }}
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Appointments Calendar</h2>
                    <a href="{{ route('web.appointments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Appointment
                    </a>
                </div>

                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                    <!-- Calendar Filters -->
                    <div class="w-full md:w-1/4 bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Filters</h3>

                        <form id="calendar-filters">
                            <div class="mb-4">
                                <label for="staff" class="block text-sm font-medium text-gray-700 mb-1">Staff</label>
                                <select id="staff" name="staff_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">All Staff</option>
                                    @foreach($staff ?? [] as $staffMember)
                                        <option value="{{ $staffMember->id }}">{{ $staffMember->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Apply Filters
                            </button>
                        </form>

                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Legend</h4>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 mr-2 bg-yellow-400 rounded-full"></span>
                                    <span class="text-sm text-gray-600">Scheduled</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 mr-2 bg-green-400 rounded-full"></span>
                                    <span class="text-sm text-gray-600">Confirmed</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 mr-2 bg-blue-400 rounded-full"></span>
                                    <span class="text-sm text-gray-600">Completed</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 mr-2 bg-red-400 rounded-full"></span>
                                    <span class="text-sm text-gray-600">Cancelled</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar -->
                    <div class="w-full md:w-3/4">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div id="appointment-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Appointment Details</h3>
            <div class="mt-2 px-7 py-3">
                <div class="text-left">
                    <p class="text-sm text-gray-500 mb-1">Client: <span id="modal-client" class="font-medium text-gray-900"></span></p>
                    <p class="text-sm text-gray-500 mb-1">Staff: <span id="modal-staff" class="font-medium text-gray-900"></span></p>
                    <p class="text-sm text-gray-500 mb-1">Date & Time: <span id="modal-datetime" class="font-medium text-gray-900"></span></p>
                    <p class="text-sm text-gray-500 mb-1">Services: <span id="modal-services" class="font-medium text-gray-900"></span></p>
                    <p class="text-sm text-gray-500 mb-1">Status: <span id="modal-status" class="font-medium text-gray-900"></span></p>
                    <p class="text-sm text-gray-500 mb-1">Total: <span id="modal-total" class="font-medium text-gray-900"></span></p>
                    <p class="text-sm text-gray-500 mb-1">Notes: <span id="modal-notes" class="font-medium text-gray-900"></span></p>
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <a id="modal-view-link" href="#" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    View Details
                </a>
                <button id="modal-close" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-md hover:bg-gray-300 focus:outline-none">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize calendar
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'timeGrid', 'interaction', 'list'],
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '20:00:00',
            slotDuration: '00:15:00',
            allDaySlot: false,
            height: 'auto',
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            events: function(info, successCallback, failureCallback) {
                // Get filter values
                const staffId = document.getElementById('staff').value;
                const status = document.getElementById('status').value;

                // Build query parameters
                let params = new URLSearchParams();
                params.append('start', info.startStr);
                params.append('end', info.endStr);
                if (staffId) params.append('staff_id', staffId);
                if (status) params.append('status', status);

                // Fetch appointments from API
                fetch(`/api/appointments/calendar?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data.map(appointment => ({
                            id: appointment.id,
                            title: appointment.client.full_name,
                            start: appointment.start_time,
                            end: appointment.end_time,
                            backgroundColor: getStatusColor(appointment.status),
                            borderColor: getStatusColor(appointment.status),
                            extendedProps: {
                                client: appointment.client.full_name,
                                staff: appointment.staff.full_name,
                                services: appointment.services.map(service => service.name).join(', '),
                                status: appointment.status,
                                total: appointment.total_price,
                                notes: appointment.notes
                            }
                        })));
                    })
                    .catch(error => {
                        console.error('Error fetching appointments:', error);
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                // Show appointment details in modal
                document.getElementById('modal-title').textContent = `Appointment #${info.event.id}`;
                document.getElementById('modal-client').textContent = info.event.extendedProps.client;
                document.getElementById('modal-staff').textContent = info.event.extendedProps.staff;
                document.getElementById('modal-datetime').textContent = `${info.event.start.toLocaleDateString()} ${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}`;
                document.getElementById('modal-services').textContent = info.event.extendedProps.services;
                document.getElementById('modal-status').textContent = capitalizeFirstLetter(info.event.extendedProps.status);
                document.getElementById('modal-total').textContent = `$${info.event.extendedProps.total}`;
                document.getElementById('modal-notes').textContent = info.event.extendedProps.notes || 'None';

                // Set view link
                document.getElementById('modal-view-link').href = `/appointments/${info.event.id}`;

                // Show modal
                document.getElementById('appointment-modal').classList.remove('hidden');
            },
            dateClick: function(info) {
                // Redirect to new appointment form with selected date
                window.location.href = `/appointments/create?date=${info.dateStr}`;
            }
        });

        calendar.render();

        // Handle filter form submission
        document.getElementById('calendar-filters').addEventListener('submit', function(e) {
            e.preventDefault();
            calendar.refetchEvents();
        });

        // Handle modal close button
        document.getElementById('modal-close').addEventListener('click', function() {
            document.getElementById('appointment-modal').classList.add('hidden');
        });

        // Helper function to get color based on appointment status
        function getStatusColor(status) {
            switch (status) {
                case 'scheduled': return '#FBBF24'; // yellow-400
                case 'confirmed': return '#34D399'; // green-400
                case 'completed': return '#60A5FA'; // blue-400
                case 'cancelled': return '#F87171'; // red-400
                default: return '#9CA3AF'; // gray-400
            }
        }

        // Helper function to capitalize first letter
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
</script>
@endpush
