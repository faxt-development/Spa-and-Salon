<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">
                {{ __('Welcome back, ') }} {{ Auth::user()->name }}!
            </h2>
            <button @click="showBookingModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('New Booking') }}
            </button>
        </div>
    </x-slot>

    <div x-data="dashboard()" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Status Messages -->
            <div x-show="message" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 p-4 rounded-md" 
                 :class="message.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'"
                 x-text="message.text"
                 @click="message = ''"
                 role="alert">
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
                                    <button @click="showBookingModal = true" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                                <button @click="showBookingModal = true" class="w-full flex items-center justify-between px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                loading: true,
                showBookingModal: false,
                showGiftCardModal: false,
                message: null,
                upcomingAppointments: [],
                
                init() {
                    this.fetchUpcomingAppointments();
                },
                
                async fetchUpcomingAppointments() {
                    try {
                        // Simulate API call - replace with actual API endpoint
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        
                        // Mock data - replace with actual API call
                        this.upcomingAppointments = [
                            {
                                id: 1,
                                service_name: 'Haircut & Styling',
                                appointment_date: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                                appointment_time: '14:30',
                                staff_name: 'Alex Johnson',
                                notes: 'Please arrive 10 minutes early for a consultation.'
                            },
                            {
                                id: 2,
                                service_name: 'Hair Color',
                                appointment_date: new Date(Date.now() + 5 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                                appointment_time: '11:00',
                                staff_name: 'Taylor Smith',
                                notes: 'Bring reference photos if you have any.'
                            }
                        ];
                    } catch (error) {
                        this.showMessage('Error loading appointments. Please try again.', 'error');
                        console.error('Error fetching appointments:', error);
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
