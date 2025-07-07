@extends('layouts.admin')

@section('title', 'Payment Method Reports')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .table th {
            border-top: none;
        }
        .bg-total {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Payment Method Reports</h1>
            <div class="d-flex gap-2">
                <x-export-buttons
                    type="payment-methods"
                    label="Export Report"
                    class="btn btn-success"
                    :showIcon="true"
                    size="sm"
                />
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.reports.payment-methods') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="payment_method_id" class="form-label">Payment Method</label>
                        <select class="form-select select2" id="payment_method_id" name="payment_method_id">
                            <option value="">All Payment Methods</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ $selectedPaymentMethod == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Method Summary</h5>
                <span class="text-muted">{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</span>
            </div>
            <div class="card-body">
                @if($reportData->isEmpty())
                    <div class="alert alert-info">No data available for the selected filters.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover" id="reportTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="text-end">Transaction Count</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-end">Average Transaction</th>
                                    <th class="text-end">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalRevenue = $reportData->sum('total_revenue');
                                @endphp
                                @foreach($reportData as $item)
                                    <tr>
                                        <td>{{ $item->paymentMethod ? $item->paymentMethod->name : 'Unknown' }}</td>
                                        <td class="text-end">{{ number_format($item->transaction_count) }}</td>
                                        <td class="text-end">${{ number_format($item->total_revenue, 2) }}</td>
                                        <td class="text-end">${{ number_format($item->average_transaction_value, 2) }}</td>
                                        <td class="text-end">{{ $totalRevenue > 0 ? number_format(($item->total_revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-total">
                                    <th>Total</th>
                                    <th class="text-end">{{ number_format($reportData->sum('transaction_count')) }}</th>
                                    <th class="text-end">${{ number_format($totalRevenue, 2) }}</th>
                                    <th class="text-end">${{ $reportData->count() > 0 ? number_format($reportData->avg('average_transaction_value'), 2) : '0.00' }}</th>
                                    <th class="text-end">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        // Initialize Select2
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select a payment method',
                allowClear: true
            });

            // Set max date for end date to today
            const today = new Date().toISOString().split('T')[0];
            $('#end_date').attr('max', today);

            // Set min date for start date when end date changes
            $('#end_date').on('change', function() {
                $('#start_date').attr('max', $(this).val());
            });

            // Set max date for end date when start date changes
            $('#start_date').on('change', function() {
                $('#end_date').attr('min', $(this).val());
            });
        });

        // Export to Excel function
        function exportToExcel() {
            // Get the table
            const table = document.getElementById('reportTable');

            // Create a new workbook
            const wb = XLSX.utils.book_new();

            // Convert table to worksheet
            const ws = XLSX.utils.table_to_sheet(table);

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Payment Method Report');

            // Generate file name with date range
            const startDate = '{{ $startDate }}';
            const endDate = '{{ $endDate }}';
            const fileName = `payment-method-report-${startDate}-to-${endDate}.xlsx`;

            // Save the file
            XLSX.writeFile(wb, fileName);
        }
    </script>
@endpush
