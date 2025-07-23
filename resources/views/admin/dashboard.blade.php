<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="dashboard">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(isset($showOnboardingWidget) && $showOnboardingWidget)
            <!-- Onboarding Checklist Widget for New Admins - Positioned First -->
            <div class="mb-6">
                <x-dashboard.onboarding-checklist-widget />
            </div>
            @endif

            <!-- Always show the full checklist link, but with different text based on completion status -->
            @if(isset($onboardingCompleted) && $onboardingCompleted)
            <div class="mb-6">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Getting Started Resources</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            Review our guides and resources to make the most of your Faxtina experience.
                        </p>
                        <a href="{{ route('admin.onboarding-checklist') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-accent-500 hover:bg-accent-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 focus:text-white">
                            View Getting Started Guide
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Configurable Dashboard Area -->
            <x-dashboard.configurable-area />

            <div class="border-t border-gray-200 my-6"></div>

            <!-- Original Dashboard Content -->
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Today's Appointments -->
                <x-dashboard.appointments-widget />

                <!-- Active Staff -->
                <x-dashboard.staff-widget />

                <!-- Today's Revenue -->
                <x-dashboard.revenue-widget />

                <!-- Walk-in Queue -->
                <x-dashboard.walk-in-widget />
            </div>

            <!-- Main Content -->
            <div class="mt-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Today's Schedule -->
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow rounded-lg overflow-hidden">
                            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Today's Schedule</h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <!-- Appointment timeline -->
                                <div class="flow-root">
                                    <div x-show="isLoading" class="text-center py-4">
                                        <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-blue-500">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Loading schedule...
                                        </div>
                                    </div>

                                    <div x-show="error" class="rounded-md bg-red-50 p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">Error loading schedule</h3>
                                                <div class="mt-2 text-sm text-red-700">
                                                    <p x-text="error"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <ul class="-mb-8" x-show="!isLoading && !error">
                                        <template x-if="todaysSchedule.length === 0">
                                            <li class="text-center py-4 text-gray-500">
                                                No appointments scheduled for today.
                                            </li>
                                        </template>

                                        <template x-for="(appointment, index) in todaysSchedule" :key="appointment.id">
                                            <li>
                                                <div class="relative pb-8" :class="{ 'pt-4': index > 0 }">
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                          aria-hidden="true"
                                                          x-show="index < todaysSchedule.length - 1">
                                                    </span>
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                                  :class="{
                                                                      'bg-green-500': appointment.status === 'completed',
                                                                      'bg-blue-500': appointment.status === 'scheduled',
                                                                      'bg-yellow-500': appointment.status === 'in_progress',
                                                                      'bg-red-500': appointment.status === 'cancelled' || appointment.status === 'no_show',
                                                                      'bg-gray-400': !['completed', 'scheduled', 'in_progress', 'cancelled', 'no_show'].includes(appointment.status)
                                                                  }">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="appointment.status === 'completed'">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                <svg x-show="appointment.status === 'in_progress'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <svg x-show="['scheduled', 'cancelled', 'no_show'].includes(appointment.status)" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="flex min-w-0 flex-1 justify-between pt-1.5">
                                                            <div>
                                                                <p class="text-sm text-gray-800">
                                                                    <span class="font-medium" x-text="appointment.client_name"></span> -
                                                                    <span x-text="appointment.services"></span>
                                                                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full"
                                                                          :class="getStatusColor(appointment.status)"
                                                                          x-text="appointment.status_label">
                                                                    </span>
                                                                </p>
                                                                <p class="text-sm text-gray-500" x-text="`${appointment.start_time} - ${appointment.end_time} • ${appointment.staff_name}`"></p>
                                                            </div>
                                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                                <a :href="`/admin/appointments/${appointment.id}`" class="font-medium text-blue-600 hover:text-blue-500">View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <div class="mt-6">
                                    <a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500">
                                        View Full Schedule
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Alerts -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white shadow rounded-lg overflow-hidden">
                            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Quick Actions</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <a href="{{ route('web.appointments.index', ['date' => now()->format('Y-m-d')]) }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Booking
                                </a>
                                <a href="{{ route('pos.index') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
</svg>
                                    Point of Sale
                                </a>
                                <a href="{{ route('admin.clients.index') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
</svg>
                                    Manage Clients
                                </a>
                                <button type="button" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
</svg>
                                    Add Walk-in
                                </button>
                                <button type="button" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>
                                    Daily Report
                                </button>
                            </div>
                        </div>

                        <!-- Alerts Widget -->
                        <x-dashboard.alerts-widget :alerts="[
                            [
                                'type' => 'warning',
                                'title' => 'Low Stock Alert',
                                'message' => 'Shampoo is running low (3 remaining)'
                            ],
                            [
                                'type' => 'error',
                                'title' => '2 No-shows Today',
                                'message' => 'Consider following up with these clients'
                            ]
                        ]" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                isLoading: true,
                alertsLoading: true,
                error: null,
                alerts: [],
                todaysSchedule: [],

                fetchAlerts() {
                    this.alertsLoading = true;
                    fetch('{{ route("admin.dashboard.alerts") }}', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to load alerts');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.alerts = data;
                    })
                    .catch(error => {
                        console.error('Error fetching alerts:', error);
                        this.alerts = [{
                            type: 'error',
                            title: 'Error',
                            message: 'Failed to load alerts. Please refresh the page to try again.'
                        }];
                    })
                    .finally(() => {
                        this.alertsLoading = false;
                    });
                },

                init() {
                    this.fetchTodaysSchedule();
                    this.fetchAlerts();
                },

                fetchTodaysSchedule() {
                    this.isLoading = true;
                    this.error = null;

                    fetch('{{ route("admin.dashboard.todays-schedule") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        method: 'GET',
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.todaysSchedule = data;
                    })
                    .catch(error => {
                        console.error('Error fetching schedule:', error);
                        this.error = 'Failed to load schedule. Please try again later.';
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                },

                getStatusColor(status) {
                    const statusColors = {
                        'scheduled': 'bg-blue-100 text-blue-800',
                        'in_progress': 'bg-yellow-100 text-yellow-800',
                        'completed': 'bg-green-100 text-green-800',
                        'cancelled': 'bg-red-100 text-red-800',
                        'no_show': 'bg-gray-100 text-gray-800'
                    };
                    return statusColors[status] || 'bg-gray-100 text-gray-800';
                },

                handleQuickAction(action) {
                    console.log('Quick action:', action);
                    // Implement quick action logic
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
