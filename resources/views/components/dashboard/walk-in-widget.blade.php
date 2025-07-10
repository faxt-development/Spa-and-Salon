<div class="bg-white overflow-hidden shadow rounded-lg" x-data="walkInWidget()" x-init="fetchWalkInStats()">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center">
            <!-- Icon -->
            <div class="flex-shrink-0 bg-primary-500 rounded-md p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Content -->
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Walk-in Queue</dt>
                    <dd class="flex items-baseline">
                        <!-- Loading State -->
                        <template x-if="isLoading">
                            <div class="animate-pulse flex space-x-4">
                                <div class="h-7 w-12 bg-gray-200 rounded"></div>
                                <div class="h-5 w-24 bg-gray-200 rounded ml-2"></div>
                            </div>
                        </template>

                        <!-- Data State -->
                        <template x-if="!isLoading">
                            <div class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="waitingCount"></div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold"
                                     :class="{
                                        'text-yellow-600': waitingCount > 0,
                                        'text-green-600': waitingCount === 0
                                     }">
                                    <span x-show="waitingCount > 0" class="sr-only">Wait time</span>
                                    <span x-text="waitTimeFormatted"></span>
                                </div>
                            </div>
                        </template>
                    </dd>
                </dl>
            </div>

            <!-- Refresh Button -->
            <button @click="fetchWalkInStats()"
                    class="ml-5 flex-shrink-0 bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    :class="{'animate-spin': isLoading}"
                    :disabled="isLoading">
                <span class="sr-only">Refresh</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('walkInWidget', () => ({
            waitingCount: 0,
            waitTimeFormatted: '~0 min',
            isLoading: true,
            error: null,

            init() {
                // Refresh data every 60 seconds
                this.interval = setInterval(() => {
                    this.fetchWalkInStats();
                }, 60000);
            },

            fetchWalkInStats() {
                this.isLoading = true;
                this.error = null;

                // Use the configured axios instance from the window object
                const api = window.axios || axios;

                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // Make the request with proper headers and credentials
                api.get('/dashboard/walk-ins/queue-stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    withCredentials: true
                })
                .then(response => {
                    if (response.data && typeof response.data === 'object') {
                        this.waitingCount = response.data.waiting_count || 0;
                        this.waitTimeFormatted = response.data.wait_time_formatted || '~0 min';
                    } else {
                        throw new Error('Invalid response format');
                    }
                })
                .catch(error => {
                    console.error('Error fetching walk-in stats:', error);
                    this.error = 'Failed to load walk-in queue data';
                    this.waitingCount = 0;
                    this.waitTimeFormatted = '~0 min';
                })
                .finally(() => {
                    this.isLoading = false;
                });
            },

            // Clean up interval when component is removed
            destroy() {
                if (this.interval) {
                    clearInterval(this.interval);
                }
            }
        }));
    });
</script>
@endpush
