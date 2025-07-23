@extends('layouts.app')

@section('content')
<div class="py-6" x-data="timeClock()" x-init="init()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Time Clock</h2>

                <!-- Current Status -->
                <div class="bg-gray-50 p-6 rounded-lg mb-8 text-center">
                    <div class="text-4xl font-bold mb-2" x-text="currentTime"></div>
                    <div class="text-xl text-gray-600 mb-6" x-text="currentDate"></div>

                    <template x-if="!currentEntry">
                        <button
                            @click="clockIn()"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            :disabled="isClockingIn"
                        >
                            <svg x-show="isClockingIn" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isClockingIn ? 'Clocking In...' : 'Clock In'"></span>
                        </button>
                    </template>

                    <template x-if="currentEntry">
                        <div class="space-y-4">
                            <div class="text-lg font-medium text-gray-700">
                                You are currently clocked in since
                                <span class="font-bold" x-text="formatTime(currentEntry.clock_in)"></span>
                                <div class="mt-1 text-2xl font-bold text-green-600" x-text="formatDuration(currentEntry.clock_in)"></div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                    <textarea
                                        id="notes"
                                        x-model="notes"
                                        rows="2"
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"
                                        placeholder="Add any notes about your shift..."
                                    ></textarea>
                                </div>

                                <button
                                    @click="clockOut()"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    :disabled="isClockingOut"
                                >
                                    <svg x-show="isClockingOut" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isClockingOut ? 'Clocking Out...' : 'Clock Out'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Recent Time Entries -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Time Entries</h3>

                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Time In</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Time Out</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duration</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template x-if="loadingEntries">
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                                            <div class="flex justify-center">
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>Loading time entries...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!loadingEntries && timeEntries.length === 0">
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                                            No time entries found
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="entry in timeEntries" :key="entry.id">
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6" x-text="formatDate(entry.clock_in)"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="formatTime(entry.clock_in)"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="entry.clock_out ? formatTime(entry.clock_out) : '--:--:-- --'"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="formatDuration(entry.clock_in, entry.clock_out)"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span
                                                x-bind:class="{
                                                    'bg-yellow-100 text-yellow-800': !entry.clock_out,
                                                    'bg-green-100 text-green-800': entry.clock_out && entry.is_approved,
                                                    'bg-primary-100 text-blue-800': entry.clock_out && !entry.is_approved
                                                }"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                x-text="!entry.clock_out ? 'Clocked In' : (entry.is_approved ? 'Approved' : 'Pending Approval')"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function timeClock() {
        return {
            currentTime: '',
            currentDate: '',
            currentEntry: null,
            timeEntries: [],
            loadingEntries: false,
            isClockingIn: false,
            isClockingOut: false,
            notes: '',

            init() {
                this.updateDateTime();
                setInterval(() => this.updateDateTime(), 1000);
                this.fetchCurrentEntry();
                this.fetchTimeEntries();
            },

            updateDateTime() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.currentDate = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            async fetchCurrentEntry() {
                try {
                    const response = await fetch('/api/time-clock/status');
                    const data = await response.json();
                    if (data.data && data.data.active_entry) {
                        this.currentEntry = data.data.active_entry;
                    }
                } catch (error) {
                    console.error('Error fetching current time entry:', error);
                }
            },

            async fetchTimeEntries() {
                this.loadingEntries = true;
                try {
                    const response = await fetch('/api/time-clock?per_page=5');
                    const data = await response.json();
                    this.timeEntries = data.data || [];
                } catch (error) {
                    console.error('Error fetching time entries:', error);
                } finally {
                    this.loadingEntries = false;
                }
            },

            async clockIn() {
                if (this.isClockingIn) return;

                this.isClockingIn = true;

                try {
                    const response = await fetch('/api/time-clock/clock-in', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            notes: this.notes
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.currentEntry = data.data;
                        this.notes = '';
                        await this.fetchTimeEntries();
                        this.showNotification('Clocked in successfully', 'success');
                    } else {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to clock in');
                    }
                } catch (error) {
                    console.error('Error clocking in:', error);
                    this.showNotification(error.message || 'Failed to clock in', 'error');
                } finally {
                    this.isClockingIn = false;
                }
            },

            async clockOut() {
                if (this.isClockingOut || !this.currentEntry) return;

                this.isClockingOut = true;

                try {
                    const response = await fetch(`/api/time-clock/clock-out`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            notes: this.notes
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.currentEntry = null;
                        this.notes = '';
                        await this.fetchTimeEntries();
                        this.showNotification('Clocked out successfully', 'success');
                    } else {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to clock out');
                    }
                } catch (error) {
                    console.error('Error clocking out:', error);
                    this.showNotification(error.message || 'Failed to clock out', 'error');
                } finally {
                    this.isClockingOut = false;
                }
            },

            formatTime(timestamp) {
                if (!timestamp) return '--:--:-- --';
                const date = new Date(timestamp);
                return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            },

            formatDate(timestamp) {
                if (!timestamp) return '--/--/----';
                const date = new Date(timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            },

            formatDuration(startTime, endTime = null) {
                if (!startTime) return '--:--:--';

                const start = new Date(startTime);
                const end = endTime ? new Date(endTime) : new Date();

                let diff = Math.floor((end - start) / 1000); // Difference in seconds

                const hours = Math.floor(diff / 3600);
                diff -= hours * 3600;

                const minutes = Math.floor(diff / 60);
                diff -= minutes * 60;

                const seconds = diff;

                return [
                    hours.toString().padStart(2, '0'),
                    minutes.toString().padStart(2, '0'),
                    seconds.toString().padStart(2, '0')
                ].join(':');
            },

            showNotification(message, type = 'success') {
                // You can implement a toast notification system here
                // For now, we'll just use a simple alert
                alert(`[${type.toUpperCase()}] ${message}`);
            }
        };
    }
</script>
@endpush
@endsection
