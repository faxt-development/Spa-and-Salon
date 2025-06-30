@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .summary-card {
            transition: all 0.3s ease;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .loading-overlay.active {
            display: flex;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Tax Reports</h1>
        <div class="mt-4">
            <x-export-buttons 
                type="tax" 
                label="Export Report" 
                class="btn btn-primary" 
                :showIcon="true"
                size="md"
            />
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div x-data="{ isLoading: false }" 
         x-init="() => {
             $watch('isLoading', value => {
                 if (value) {
                     document.body.style.cursor = 'wait';
                 } else {
                     document.body.style.cursor = 'default';
                 }
             });
         }"
         class="loading-overlay" 
         :class="{ 'active': isLoading }">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filters
        </div>
        <div class="card-body">
            <form x-data="{
                reportType: 'summary',
                startDate: '{{ $defaultStartDate }}',
                endDate: '{{ $defaultEndDate }}',
                taxRateId: '',
                groupBy: 'month',
                
                init() {
                    // Initialize date pickers
                    flatpickr('#startDate', {
                        dateFormat: 'Y-m-d',
                        defaultDate: this.startDate,
                        onChange: (selectedDates, dateStr) => {
                            this.startDate = dateStr;
                            this.loadReport();
                        }
                    });
                    
                    flatpickr('#endDate', {
                        dateFormat: 'Y-m-d',
                        defaultDate: this.endDate,
                        onChange: (selectedDates, dateStr) => {
                            this.endDate = dateStr;
                            this.loadReport();
                        }
                    });
                    
                    // Load initial report
                    this.loadReport();
                },
                
                async loadReport() {
                    if (!this.startDate || !this.endDate) return;
                    
                    this.isLoading = true;
                    
                    try {
                        const url = this.reportType === 'summary' 
                            ? `/api/reports/tax/summary?start_date=${this.startDate}&end_date=${this.endDate}&group_by=${this.groupBy}${this.taxRateId ? '&tax_rate_id=' + this.taxRateId : ''}`
                            : `/api/reports/tax/detailed?start_date=${this.startDate}&end_date=${this.endDate}${this.taxRateId ? '&tax_rate_id=' + this.taxRateId : ''}`;
                        
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (!response.ok) {
                            throw new Error('Failed to load report data');
                        }
                        
                        const data = await response.json();
                        
                        if (this.reportType === 'summary') {
                            this.$dispatch('summary-data-loaded', data.data);
                        } else {
                            this.$dispatch('detailed-data-loaded', data.data);
                        }
                    } catch (error) {
                        console.error('Error loading report:', error);
                        alert('Failed to load report data. Please try again.');
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                exportReport(format) {
                    let url = `/admin/reports/tax/export?format=${format}&start_date=${this.startDate}&end_date=${this.endDate}&type=${this.reportType}`;
                    if (this.taxRateId) {
                        url += `&tax_rate_id=${this.taxRateId}`;
                    }
                    if (this.reportType === 'summary' && this.groupBy) {
                        url += `&group_by=${this.groupBy}`;
                    }
                    window.location.href = url;
                }
            }"
            @summary-data-loaded.window="(event) => { window.reportData = event.detail; renderChart(event.detail); }"
            @detailed-data-loaded.window="(event) => { window.reportData = event.detail; if (window.dataTable) { window.dataTable.destroy(); } initDataTable(event.detail); }"
            x-init="
                // Chart initialization will be handled by the renderChart function
                window.renderChart = function(data) {
                    const ctx = document.getElementById('taxChart');
                    if (window.taxChart) {
                        window.taxChart.destroy();
                    }
                    
                    if (!data || !data.results || data.results.length === 0) {
                        ctx.closest('.card').classList.add('d-none');
                        return;
                    }
                    
                    ctx.closest('.card').classList.remove('d-none');
                    
                    const labels = data.results.map(item => item.period);
                    const taxData = data.results.map(item => item.total_tax_amount);
                    
                    window.taxChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Tax Collected',
                                data: taxData,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toFixed(2);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Tax: $' + context.raw.toFixed(2);
                                        }
                                    }
                                }
                            }
                        }
                    });
                };
                
                // DataTable initialization function
                window.initDataTable = function(data) {
                    const table = $('#taxDetailedTable').DataTable({
                        data: data.data,
                        columns: [
                            { data: 'id', title: 'ID' },
                            { data: 'date', title: 'Date', render: function(data) { return new Date(data).toLocaleDateString(); } },
                            { data: 'client.name', title: 'Client' },
                            { data: 'taxable_amount', title: 'Taxable Amount', render: function(data) { return '$' + parseFloat(data).toFixed(2); } },
                            { data: 'tax_amount', title: 'Tax Amount', render: function(data) { return '$' + parseFloat(data).toFixed(2); } },
                            { data: 'tax_rate.name', title: 'Tax Rate' },
                            { data: 'type', title: 'Type' }
                        ],
                        order: [[1, 'desc']],
                        responsive: true,
                        pageLength: 25,
                        dom: '<&quot;top&quot;f>rt<&quot;bottom&quot;lip><&quot;clear&quot;>',
                        language: {
                            search: '_INPUT_',
                            searchPlaceholder: 'Search...'
                        },
                        initComplete: function() {
                            $('.dataTables_filter input').addClass('form-control');
                            $('.dataTables_length select').addClass('form-select');
                        }
                    });
                    
                    window.dataTable = table;
                };
            ">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="reportType" class="form-label">Report Type</label>
                        <select class="form-select" id="reportType" x-model="reportType" @change="loadReport()">
                            <option value="summary">Summary</option>
                            <option value="detailed">Detailed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="text" class="form-control" id="startDate" x-model="startDate" placeholder="Start Date">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="text" class="form-control" id="endDate" x-model="endDate" placeholder="End Date">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="taxRate" class="form-label">Tax Rate</label>
                        <select class="form-select" id="taxRate" x-model="taxRateId" @change="loadReport()">
                            <option value="">All Tax Rates</option>
                            @foreach($taxRates as $rate)
                                <option value="{{ $rate->id }}">{{ $rate->name }} ({{ $rate->rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3" x-show="reportType === 'summary'">
                        <label for="groupBy" class="form-label">Group By</label>
                        <select class="form-select" id="groupBy" x-model="groupBy" @change="loadReport()">
                            <option value="day">Daily</option>
                            <option value="week">Weekly</option>
                            <option value="month" selected>Monthly</option>
                            <option value="quarter">Quarterly</option>
                            <option value="year">Yearly</option>
                            <option value="tax_rate">Tax Rate</option>
                        </select>
                    </div>
                    
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-primary me-2" @click="loadReport()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" @click="exportReport('csv')">
                                <i class="fas fa-file-csv me-1"></i> Export CSV
                            </button>
                            <button type="button" class="btn btn-danger" @click="exportReport('pdf')">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4" x-show="reportType === 'summary'" x-data="{ 
        totalTaxableAmount: 0,
        totalTaxAmount: 0,
        averageTaxRate: 0,
        totalTransactions: 0,
        
        init() {
            this.$watch('$store.reportData', (data) => {
                if (data) {
                    this.totalTaxableAmount = data.total_taxable_amount || 0;
                    this.totalTaxAmount = data.total_tax_amount || 0;
                    this.averageTaxRate = data.total_taxable_amount > 0 
                        ? (data.total_tax_amount / data.total_taxable_amount * 100).toFixed(2) 
                        : 0;
                    this.totalTransactions = data.total_transactions || 0;
                }
            });
        }
    }">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white summary-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Taxable Amount</h6>
                            <h2 class="mb-0">$<span x-text="new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(totalTaxableAmount)"></span></h2>
                        </div>
                        <div class="icon-shape bg-white text-primary rounded-circle p-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white summary-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Tax Collected</h6>
                            <h2 class="mb-0">$<span x-text="new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(totalTaxAmount)"></span></h2>
                        </div>
                        <div class="icon-shape bg-white text-success rounded-circle p-3">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white summary-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Avg. Tax Rate</h6>
                            <h2 class="mb-0"><span x-text="averageTaxRate"></span>%</h2>
                        </div>
                        <div class="icon-shape bg-white text-warning rounded-circle p-3">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white summary-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Transactions</h6>
                            <h2 class="mb-0"><span x-text="totalTransactions"></span></h2>
                        </div>
                        <div class="icon-shape bg-white text-info rounded-circle p-3">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mb-4" x-show="reportType === 'summary'">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Tax Collection Trend
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="taxChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="card mb-4" x-show="reportType === 'detailed'">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detailed Tax Records
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="taxDetailedTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Taxable Amount</th>
                            <th>Tax Amount</th>
                            <th>Tax Rate</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize date range picker
            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: ["{{ $defaultStartDate }}", "{{ $defaultEndDate }}"],
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const [start, end] = selectedDates;
                        Alpine.store('filters').startDate = start.toISOString().split('T')[0];
                        Alpine.store('filters').endDate = end.toISOString().split('T')[0];
                        
                        // Trigger report reload
                        const event = new CustomEvent('filters-updated');
                        document.dispatchEvent(event);
                    }
                }
            });
        });
        
        // Store for global state management
        document.addEventListener('alpine:init', () => {
            Alpine.store('filters', {
                startDate: '{{ $defaultStartDate }}',
                endDate: '{{ $defaultEndDate }}',
                taxRateId: '',
                reportType: 'summary',
                groupBy: 'month',
                
                get queryString() {
                    const params = new URLSearchParams();
                    params.append('start_date', this.startDate);
                    params.append('end_date', this.endDate);
                    if (this.taxRateId) params.append('tax_rate_id', this.taxRateId);
                    if (this.reportType === 'summary') {
                        params.append('group_by', this.groupBy);
                    }
                    return params.toString();
                }
            });
        });
    </script>
@endpush