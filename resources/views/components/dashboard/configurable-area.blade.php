<div class="configurable-dashboard-area mb-8" id="configurableDashboardArea">
    <!-- This div will be the mount point for the React component -->
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the configurable dashboard area
        if (typeof window.initConfigurableDashboard === 'function') {
            window.initConfigurableDashboard('configurableDashboardArea');
        }
    });
</script>
@endpush
