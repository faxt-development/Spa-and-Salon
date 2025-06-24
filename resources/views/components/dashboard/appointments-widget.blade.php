<div
    x-data="appointmentsWidget()"
    x-init="fetchAppointmentStats()"
    class="bg-white rounded-lg shadow p-6 flex items-start"
>
    <!-- Calendar Icon -->
    <div class="bg-blue-100 p-3 rounded-lg mr-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>

    <!-- Content -->
    <div class="flex-1">
        <h3 class="text-gray-500 text-sm font-medium">Today's Appointments</h3>
        <div class="flex items-baseline mt-1">
            <span x-text="todayCount || '--'" class="text-3xl font-bold"></span>
            <span
                x-show="difference !== null"
                :class="{
                    'text-green-600': isPositive,
                    'text-red-600': !isPositive
                }"
                class="ml-2 text-sm font-medium"
            >
                <span x-text="isPositive ? '+' + difference : difference"></span>
                <span x-text="isPositive ? ' from yesterday' : ' from yesterday'"></span>
            </span>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div x-show="isLoading" class="ml-4">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Error Message -->
    <div x-show="error" class="text-red-500 text-sm mt-2">
        <span x-text="error"></span>
    </div>
</div>

@push('scripts')
<script>
function appointmentsWidget() {
    return {
        todayCount: null,
        difference: null,
        isPositive: true,
        isLoading: true,
        error: null,

        fetchAppointmentStats() {
            this.isLoading = true;
            this.error = null;

            fetch('/api/dashboard/appointments/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': 'Bearer {{ session()->get('api_token') }}',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch appointment statistics');
                }
                return response.json();
            })
            .then(data => {
                this.todayCount = data.today_count;
                this.difference = data.difference;
                this.isPositive = data.is_positive;
            })
            .catch(error => {
                console.error('Error fetching appointment stats:', error);
                this.error = 'Failed to load appointment data';
            })
            .finally(() => {
                this.isLoading = false;
            });
        }
    };
}
</script>
@endpush
