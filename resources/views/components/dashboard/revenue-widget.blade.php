<div class="bg-white overflow-hidden shadow rounded-lg" x-data="{
    todayRevenue: 0,
    targetPercentage: 0,
    targetReached: true,
    loading: true,
    error: null,

    init() {
        this.fetchRevenueStats();

        // Refresh every 5 minutes
        setInterval(() => {
            this.fetchRevenueStats();
        }, 300000);
    },

    fetchRevenueStats() {
        this.loading = true;
        fetch('/api/dashboard/revenue/stats', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Authorization': 'Bearer {{ session()->get('api_token') }}'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            this.todayRevenue = data.today_revenue;
            this.targetPercentage = data.target_percentage;
            this.targetReached = data.target_reached;
            this.error = null;
        })
        .catch(error => {
            console.error('Error fetching revenue stats:', error);
            this.error = 'Failed to load revenue data';
        })
        .finally(() => {
            this.loading = false;
        });
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }
}" x-cloak>
    <div class="p-5">
        <div class="flex items-center">
            <!-- Icon -->
            <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3 mr-4">
                <div class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-20 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Revenue Info -->
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-500 truncate">Today's Revenue</p>
                <div class="flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900" x-text="formatCurrency(todayRevenue)">$0.00</p>
                </div>
            </div>

            <!-- Target Percentage -->
            <div class="ml-4 text-right">
                <p class="text-sm font-medium text-green-600" x-text="targetPercentage + '%'">0%</p>
                <p class="text-xs text-gray-500">of target</p>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Error State -->
    <div x-show="error" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center p-4">
        <p class="text-sm text-red-600" x-text="error"></p>
    </div>
</div>
