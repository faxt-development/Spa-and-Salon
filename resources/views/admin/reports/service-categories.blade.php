@extends('layouts.admin')

@section('title', 'Service Category Reports')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/datatables.min.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Service Category Reports</h1>
            <div>
                <x-export-buttons
                    type="service-categories"
                    label="Export Report"
                    class="btn btn-primary"
                    :showIcon="true"
                    size="md"
                />
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
            </div>
            <div class="card-body">
                <form id="report-filters">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date-range">Date Range</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="date-range" name="date_range">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category_id">
                                    <option value="">All Categories</option>
                                    @foreach($serviceCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="apply-filters" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <button type="button" id="export-pdf" class="btn btn-outline-secondary mr-2">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button type="button" id="export-excel" class="btn btn-outline-secondary">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Revenue by Service Category</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="categoryChartMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="categoryChartMenu">
                                <a class="dropdown-item" href="#" data-chart-type="bar">Bar Chart</a>
                                <a class="dropdown-item" href="#" data-chart-type="pie">Pie Chart</a>
                                <a class="dropdown-item" href="#" data-chart-type="table">Table View</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div class="table-responsive d-none">
                            <table class="table table-bordered" id="categoryTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Revenue</th>
                                        <th>% of Total</th>
                                        <th># of Services</th>
                                        <th>Avg. Price</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryTableBody">
                                    <!-- Filled by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Service Performance by Category</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="servicePerformanceTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Service</th>
                                        <th># Sold</th>
                                        <th>Revenue</th>
                                        <th>Avg. Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('vendor/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize date range picker
            $('input[name="date_range"]').daterangepicker({
                startDate: '{{ $defaultStartDate }}',
                endDate: '{{ $defaultEndDate }}',
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                alwaysShowCalendars: true,
                autoUpdateInput: true,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            // Initialize DataTables
            const servicePerformanceTable = $('#servicePerformanceTable').DataTable({
                processing: true,
                serverSide: false,
                searching: true,
                ordering: true,
                order: [[3, 'desc']], // Sort by revenue by default
                columns: [
                    { data: 'category_name' },
                    { data: 'service_name' },
                    { data: 'service_count', className: 'text-right' },
                    {
                        data: 'total_revenue',
                        className: 'text-right',
                        render: function(data) {
                            return '$' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'average_price',
                        className: 'text-right',
                        render: function(data) {
                            return data ? '$' + parseFloat(data).toFixed(2) : '-';
                        }
                    }
                ]
            });

            // Chart variables
            let categoryChart;
            let chartType = 'bar';

            // Load report data
            function loadReportData() {
                const formData = $('#report-filters').serializeArray();
                const params = {};

                // Convert form data to object
                $.each(formData, function(i, field) {
                    if (field.name === 'date_range') {
                        const dates = field.value.split(' - ');
                        params.start_date = dates[0];
                        params.end_date = dates[1];
                    } else if (field.value) {
                        params[field.name] = field.value;
                    }
                });

                // Show loading state
                const $applyBtn = $('#apply-filters');
                const originalBtnText = $applyBtn.html();
                $applyBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

                // Load category data
                $.get('{{ route("admin.reports.service.categories.data") }}', params)
                    .done(function(response) {
                        if (response.success) {
                            updateCategoryChart(response.data);
                            updateCategoryTable(response.data);
                        }
                    })
                    .always(function() {
                        // Load service performance data
                        $.get('{{ route("admin.reports.service.performance.data") }}', params)
                            .done(function(response) {
                                if (response.success) {
                                    servicePerformanceTable.clear().rows.add(response.data).draw();
                                }
                            })
                            .always(function() {
                                // Re-enable button
                                $applyBtn.prop('disabled', false).html(originalBtnText);
                            });
                    });
            }

            // Update category chart
            function updateCategoryChart(data) {
                const ctx = document.getElementById('categoryChart').getContext('2d');
                const labels = data.map(item => item.category_name);
                const revenueData = data.map(item => parseFloat(item.revenue));

                // Calculate percentages for tooltips
                const totalRevenue = revenueData.reduce((sum, value) => sum + value, 0);
                const percentages = revenueData.map(value => {
                    return totalRevenue > 0 ? ((value / totalRevenue) * 100).toFixed(1) + '%' : '0%';
                });

                // Destroy existing chart if it exists
                if (categoryChart) {
                    categoryChart.destroy();
                }

                // Create new chart
                categoryChart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData,
                            backgroundColor: [
                                'rgba(78, 115, 223, 0.8)',
                                'rgba(54, 185, 204, 0.8)',
                                'rgba(28, 200, 138, 0.8)',
                                'rgba(246, 194, 62, 0.8)',
                                'rgba(231, 74, 59, 0.8)',
                                'rgba(155, 89, 182, 0.8)',
                                'rgba(52, 73, 94, 0.8)'
                            ],
                            borderColor: [
                                'rgba(78, 115, 223, 1)',
                                'rgba(54, 185, 204, 1)',
                                'rgba(28, 200, 138, 1)',
                                'rgba(246, 194, 62, 1)',
                                'rgba(231, 74, 59, 1)',
                                'rgba(155, 89, 182, 1)',
                                'rgba(52, 73, 94, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: chartType === 'bar' ? {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        } : {},
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed.y || context.raw;
                                        const percentage = percentages[context.dataIndex];
                                        return `${label}: $${value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${percentage})`;
                                    }
                                }
                            },
                            legend: {
                                display: chartType === 'pie',
                                position: 'right'
                            }
                        }
                    }
                });
            }

            // Update category table
            function updateCategoryTable(data) {
                const $tableBody = $('#categoryTableBody');
                $tableBody.empty();

                // Calculate total revenue for percentages
                const totalRevenue = data.reduce((sum, item) => sum + parseFloat(item.revenue), 0);

                // Add rows for each category
                data.forEach(item => {
                    const revenue = parseFloat(item.revenue);
                    const percentage = totalRevenue > 0 ? (revenue / totalRevenue * 100).toFixed(1) : 0;

                    $tableBody.append(`
                        <tr>
                            <td>${item.category_name}</td>
                            <td class="text-right">$${revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-right">${percentage}%</td>
                            <td class="text-right">${item.service_count}</td>
                            <td class="text-right">${item.average_service_price ? '$' + parseFloat(item.average_service_price).toFixed(2) : '-'}</td>
                        </tr>
                    `);
                });

                // Add total row
                if (data.length > 0) {
                    $tableBody.append(`
                        <tr class="font-weight-bold">
                            <td>Total</td>
                            <td class="text-right">$${totalRevenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-right">100%</td>
                            <td class="text-right">${data.reduce((sum, item) => sum + parseInt(item.service_count), 0).toLocaleString()}</td>
                            <td></td>
                        </tr>
                    `);
                }
            }

            // Chart type toggle
            $(document).on('click', '[data-chart-type]', function(e) {
                e.preventDefault();
                chartType = $(this).data('chart-type');

                // Update active state
                $('[data-chart-type]').removeClass('active');
                $(this).addClass('active');

                // Toggle table view
                if (chartType === 'table') {
                    $('.chart-area').addClass('d-none');
                    $('.table-responsive').removeClass('d-none');
                } else {
                    $('.chart-area').removeClass('d-none');
                    $('.table-responsive').addClass('d-none');

                    // Reload chart with new type
                    const currentData = categoryChart ? categoryChart.data : null;
                    if (currentData) {
                        updateCategoryChart(currentData);
                    }
                }
            });

            // Apply filters
            $('#apply-filters').on('click', function() {
                loadReportData();
            });

            // Export to PDF
            $('#export-pdf').on('click', function() {
                // Implement PDF export
                alert('PDF export will be implemented here');
            });

            // Export to Excel
            $('#export-excel').on('click', function() {
                // Implement Excel export
                alert('Excel export will be implemented here');
            });

            // Load initial data
            loadReportData();
        });
    </script>
@endpush
