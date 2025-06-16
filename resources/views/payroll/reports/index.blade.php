@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="payrollReports()" x-init="init()">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Payroll Reports</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-8 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Filter Reports</h2>
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Employee Filter -->
            <div>
                <label for="employee" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                <select id="employee" x-model="filters.employee_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">All Employees</option>
                    <template x-for="employee in employees" :key="employee.id">
                        <option :value="employee.id" x-text="employee.staff.first_name + ' ' + employee.staff.last_name"></option>
                    </template>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" id="start_date" x-model="filters.start_date" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" id="end_date" x-model="filters.end_date" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            </div>

            <!-- Report Type -->
            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                <select id="report_type" x-model="filters.report_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="summary">Summary Report</option>
                    <option value="detailed">Detailed Report</option>
                    <option value="tax">Tax Report</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Apply Filters
                </button>
                <button type="button" @click="resetFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Reset
                </button>
                <button type="button" @click="exportToPDF" class="ml-auto inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Export PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Payroll Cost</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="formatCurrency(reportData.summary?.total_payroll || 0)"></dd>
                <div class="mt-2 text-sm text-gray-500" x-show="reportData.summary">
                    <span :class="{
                        'text-green-600': reportData.summary?.change_percentage >= 0,
                        'text-red-600': reportData.summary?.change_percentage < 0
                    }">
                        <span x-text="reportData.summary?.change_percentage >= 0 ? '↑' : '↓'"></span>
                        <span x-text="Math.abs(reportData.summary?.change_percentage || 0) + '%'" x-show="reportData.summary?.change_percentage !== 0"></span>
                        <span x-show="reportData.summary?.change_percentage === 0">No change</span>
                        vs previous period
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Average Hours per Employee</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="(reportData.summary?.avg_hours || 0).toFixed(2) + ' hrs'"></dd>
                <div class="mt-2 text-sm text-gray-500">
                    <span x-text="reportData.summary?.total_employees || 0"></span> employees
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Tax Withheld</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="formatCurrency(reportData.summary?.total_tax || 0)"></dd>
                <div class="mt-2 text-sm text-gray-500">
                    <span x-text="reportData.summary?.total_payroll ? Math.round((reportData.summary.total_tax / reportData.summary.total_payroll) * 100) : 0"></span>% of payroll
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">
                <span x-text="getReportTitle()"></span>
                <span class="text-sm text-gray-500 ml-2" x-show="reportData.records && reportData.records.length > 0">
                    (<span x-text="reportData.records.length"></span> records)
                </span>
            </h2>
            <div class="flex items-center space-x-2">
                <button @click="toggleViewMode" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <span x-show="!tableView" class="flex items-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span class="ml-1 text-sm">Table View</span>
                    </span>
                    <span x-show="tableView" class="flex items-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="ml-1 text-sm">Card View</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div class="p-8 text-center" x-show="loading">
            <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-blue-500">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading report data...
            </div>
        </div>

        <!-- Empty State -->
        <div class="p-8 text-center" x-show="!loading && (!reportData.records || reportData.records.length === 0)">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No reports</h3>
            <p class="mt-1 text-sm text-gray-500">
                No payroll data found for the selected filters.
            </p>
            <div class="mt-6">
                <button @click="resetFilters" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Reset filters
                </button>
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto" x-show="!loading && tableView && reportData.records && reportData.records.length > 0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="record in reportData.records" :key="record.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                            <span x-text="record.employee.staff.first_name[0] + record.employee.staff.last_name[0]"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="record.employee.staff.first_name + ' ' + record.employee.staff.last_name"></div>
                                        <div class="text-sm text-gray-500" x-text="record.employee.position"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="formatDate(record.pay_period_start) + ' - ' + formatDate(record.pay_period_end)"></div>
                                <div class="text-sm text-gray-500" x-text="'Paid: ' + formatDate(record.payment_date)"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                <span x-text="record.hours_worked + ' hrs'"></span>
                                <span x-show="record.overtime_hours > 0" class="text-xs text-orange-500 block" x-text="'(+' + record.overtime_hours + ' OT)'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900" x-text="formatCurrency(record.gross_amount)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500" x-text="formatCurrency(record.tax_amount)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900" x-text="formatCurrency(record.net_amount)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                    'bg-yellow-100 text-yellow-800': record.payment_status === 'pending',
                                    'bg-green-100 text-green-800': record.payment_status === 'processed',
                                    'bg-red-100 text-red-800': record.payment_status === 'cancelled'
                                }" x-text="record.payment_status.charAt(0).toUpperCase() + record.payment_status.slice(1)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a :href="'{{ route('payroll.records.show', '') }}/' + record.id" class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot x-show="reportData.records && reportData.records.length > 0" class="bg-gray-50">
                    <tr>
                        <th colspan="2" class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Totals:</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-500" x-text="(reportData.summary?.total_hours || 0).toFixed(2) + ' hrs'"></th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-900" x-text="formatCurrency(reportData.summary?.total_gross || 0)"></th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-500" x-text="formatCurrency(reportData.summary?.total_tax || 0)"></th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-900" x-text="formatCurrency(reportData.summary?.total_net || 0)"></th>
                        <th colspan="2" class="px-6 py-3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Card View -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6" x-show="!loading && !tableView && reportData.records && reportData.records.length > 0">
            <template x-for="record in reportData.records" :key="record.id">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium" x-text="record.employee.staff.first_name[0] + record.employee.staff.last_name[0]">
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-gray-900" x-text="record.employee.staff.first_name + ' ' + record.employee.staff.last_name"></h3>
                                    <p class="text-sm text-gray-500" x-text="record.employee.position"></p>
                                </div>
                            </div>
                            <span :class="{
                                'px-2 py-1 text-xs font-semibold rounded-full': true,
                                'bg-yellow-100 text-yellow-800': record.payment_status === 'pending',
                                'bg-green-100 text-green-800': record.payment_status === 'processed',
                                'bg-red-100 text-red-800': record.payment_status === 'cancelled'
                            }" x-text="record.payment_status.charAt(0).toUpperCase() + record.payment_status.slice(1)"></span>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Pay Period</p>
                                <p class="text-sm text-gray-900" x-text="formatDate(record.pay_period_start) + ' - ' + formatDate(record.pay_period_end)"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Date</p>
                                <p class="text-sm text-gray-900" x-text="formatDate(record.payment_date)"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Hours Worked</p>
                                <p class="text-sm text-gray-900">
                                    <span x-text="record.hours_worked + ' hrs'"></span>
                                    <span x-show="record.overtime_hours > 0" class="text-xs text-orange-500 ml-1" x-text="'(+' + record.overtime_hours + ' OT)'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Net Pay</p>
                                <p class="text-sm font-medium text-gray-900" x-text="formatCurrency(record.net_amount)"></p>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a :href="'{{ route('payroll.records.show', '') }}/' + record.id" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Pagination -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg" x-show="reportData.records && reportData.records.length > 0">
        <div class="flex-1 flex justify-between sm:hidden">
            <button @click="changePage(reportData.current_page - 1)" :disabled="reportData.current_page <= 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed': reportData.current_page <= 1}">
                Previous
            </button>
            <button @click="changePage(reportData.current_page + 1)" :disabled="reportData.current_page >= reportData.last_page" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed': reportData.current_page >= reportData.last_page}">
                Next
            </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium" x-text="reportData.from || 0"></span> to <span class="font-medium" x-text="reportData.to || 0"></span> of <span class="font-medium" x-text="reportData.total || 0"></span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button @click="changePage(reportData.current_page - 1)" :disabled="reportData.current_page <= 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed': reportData.current_page <= 1}">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <template x-for="page in reportData.links" :key="page.label">
                        <template x-if="page.url">
                            <button @click="changePage(page.label)" :class="{
                                'bg-blue-50 border-blue-500 text-blue-600 z-10': page.active,
                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !page.active
                            }" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <span x-text="page.label"></span>
                            </button>
                        </template>
                        <span x-show="!page.url && page.label.includes('...')" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            <span x-text="page.label"></span>
                        </span>
                    </template>
                    <button @click="changePage(reportData.current_page + 1)" :disabled="reportData.current_page >= reportData.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed': reportData.current_page >= reportData.last_page}">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function payrollReports() {
        return {
            loading: true,
            tableView: true,
            employees: [],
            reportData: {
                records: [],
                summary: {},
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
                links: []
            },
            filters: {
                employee_id: '',
                start_date: '',
                end_date: '',
                report_type: 'summary',
                page: 1,
                per_page: 10
            },
            
            init() {
                // Set default date range to last 30 days
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - 30);
                
                this.filters.start_date = this.formatDateForInput(startDate);
                this.filters.end_date = this.formatDateForInput(endDate);
                
                // Load employees for the dropdown
                this.fetchEmployees();
                
                // Load initial report data
                this.fetchReportData();
            },
            
            fetchEmployees() {
                fetch('{{ route("api.employees.index") }}')
                    .then(response => response.json())
                    .then(data => {
                        this.employees = data.data || [];
                    })
                    .catch(error => {
                        console.error('Error fetching employees:', error);
                    });
            },
            
            fetchReportData() {
                this.loading = true;
                const queryParams = new URLSearchParams({
                    ...this.filters,
                    page: this.filters.page,
                    per_page: this.filters.per_page
                }).toString();
                
                fetch(`{{ route('api.payroll.reports') }}?${queryParams}`)
                    .then(response => response.json())
                    .then(data => {
                        this.reportData = {
                            ...data,
                            records: data.data || []
                        };
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching report data:', error);
                        this.loading = false;
                    });
            },
            
            applyFilters() {
                this.filters.page = 1; // Reset to first page when filters change
                this.fetchReportData();
            },
            
            resetFilters() {
                this.filters = {
                    employee_id: '',
                    start_date: this.formatDateForInput(new Date(new Date().setDate(new Date().getDate() - 30))),
                    end_date: this.formatDateForInput(new Date()),
                    report_type: 'summary',
                    page: 1,
                    per_page: 10
                };
                this.fetchReportData();
            },
            
            changePage(page) {
                const pageNumber = parseInt(page);
                if (pageNumber >= 1 && pageNumber <= this.reportData.last_page) {
                    this.filters.page = pageNumber;
                    this.fetchReportData();
                    // Scroll to top of the table
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            
            toggleViewMode() {
                this.tableView = !this.tableView;
            },
            
            exportToPDF() {
                // This would be implemented to generate a PDF report
                // For now, we'll just show an alert
                alert('Export to PDF functionality will be implemented here');
                
                // In a real implementation, you might do something like:
                // window.location.href = `/api/payroll/reports/export?${new URLSearchParams(this.filters).toString()}`;
            },
            
            getReportTitle() {
                const titles = {
                    'summary': 'Payroll Summary Report',
                    'detailed': 'Detailed Payroll Report',
                    'tax': 'Tax Report'
                };
                return titles[this.filters.report_type] || 'Payroll Report';
            },
            
            formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                }).format(amount || 0);
            },
            
            formatDate(dateString) {
                if (!dateString) return '';
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            },
            
            formatDateForInput(date) {
                if (!date) return '';
                const d = new Date(date);
                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();
                const year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            }
        };
    }
</script>
@endpush
@endsection
