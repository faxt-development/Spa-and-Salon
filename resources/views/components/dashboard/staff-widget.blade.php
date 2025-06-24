<div class="bg-white overflow-hidden shadow rounded-lg" x-data="{
    totalStaff: 0,
    activeStaff: 0,
    availableStaff: 0,
    loading: true,
    error: null,

    init() {
        this.fetchStaffStats();

        // Refresh every 5 minutes
        setInterval(() => {
            this.fetchStaffStats();
        }, 300000);
    },

    fetchStaffStats() {
        this.loading = true;
        fetch('/api/dashboard/staff/stats', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Authorization': 'Bearer {{ session()->get('api_token') }}'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log(response);
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            this.totalStaff = data.total_staff || 0;
            this.activeStaff = data.active_staff || 0;
            this.availableStaff = data.available_staff || 0;
            this.error = null;
        })
        .catch(error => {
            console.error('Error fetching staff stats:', error);
            this.error = 'Failed to load staff statistics';
        })
        .finally(() => {
            this.loading = false;
        });
    }
}">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center">
            <!-- Icon -->
            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>

            <!-- Content -->
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        <span x-show="loading">Loading staff data...</span>
                        <span x-show="!loading && !error">Active Staff</span>
                        <span x-show="error" class="text-red-500" x-text="error"></span>
                    </dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900" x-text="`${activeStaff}/${totalStaff}`"></div>
                        <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                            <span x-show="!loading && availableStaff > 0">
                                <span class="sr-only">Available</span>
                                <span x-text="`${availableStaff} available`"></span>
                            </span>
                            <span x-show="!loading && availableStaff === 0" class="text-yellow-600">
                                <span class="sr-only">None available</span>
                                None available
                            </span>
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
