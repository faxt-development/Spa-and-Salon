@props(['columns' => 3])

<div class="configurable-dashboard-area mb-8" id="configurableDashboardArea" data-columns="{{ $columns }}">
    <!-- This div will be the mount point for the React component -->
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the configurable dashboard area
        if (typeof window.initConfigurableDashboard === 'function') {
            const columns = parseInt(document.getElementById('configurableDashboardArea').dataset.columns) || 3;
            window.initConfigurableDashboard('configurableDashboardArea', { columns: columns });
        }
    });
</script>
@endpush
