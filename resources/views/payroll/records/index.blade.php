@extends('layouts.app')

@section('content')
<div class="py-6" x-data="payrollData()" x-init="fetchPayrollRecords()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Payroll Records</h2>
                
                <!-- Filters -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Employee Filter -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                            <select 
                                id="employee_id" 
                                x-model="filters.employee_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                @change="fetchPayrollRecords()"
                            >
                                <option value="">All Employees</option>
                                <template x-for="employee in employees" :key="employee.id">
                                    <option :value="employee.id" x-text="employee.staff.first_name + ' ' + employee.staff.last_name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select 
                                id="status" 
                                x-model="filters.status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                @change="fetchPayrollRecords()"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="processed">Processed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input 
                                type="date" 
                                id="start_date" 
                                x-model="filters.start_date"
                                @change="fetchPayrollRecords()"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                            >
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input 
                                type="date" 
                                id="end_date" 
                                x-model="filters.end_date"
                                @change="fetchPayrollRecords()"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                            >
                        </div>
                    </div>
                </div>


                <!-- Payroll Records Table -->
                <div class="overflow-x-auto">
                    <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="loading">
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center">
                                            <div class="flex justify-center">
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>Loading payroll records...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!loading && records.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No payroll records found
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="record in records" :key="record.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <span class="text-indigo-700 font-medium" x-text="record.employee.staff.first_name.charAt(0) + record.employee.staff.last_name.charAt(0)"></span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900" x-text="record.employee.staff.first_name + ' ' + record.employee.staff.last_name"></div>
                                                    <div class="text-sm text-gray-500" x-text="record.employee.employee_id"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="formatDate(record.pay_period_start) + ' - ' + formatDate(record.pay_period_end)"></div>
                                            <div class="text-sm text-gray-500" x-text="'Paid: ' + formatDate(record.payment_date)"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="record.hours_worked + ' hrs'"></div>
                                            <div class="text-sm text-gray-500" x-text="record.overtime_hours > 0 ? record.overtime_hours + ' OT' : ''"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" x-text="'$' + formatCurrency(record.gross_amount)"></div>
                                            <div class="text-sm text-gray-500" x-text="'Net: $' + formatCurrency(record.net_amount)"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                x-bind:class="{
                                                    'bg-yellow-100 text-yellow-800': record.payment_status === 'pending',
                                                    'bg-green-100 text-green-800': record.payment_status === 'processed',
                                                    'bg-red-100 text-red-800': record.payment_status === 'cancelled'
                                                }" 
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                                x-text="record.payment_status"
                                            ></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a 
                                                :href="'{{ route('payroll.records.show', '') }}/' + record.id" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >View</a>
                                            <template x-if="record.payment_status === 'pending'">
                                                <button 
                                                    @click="processPayroll(record.id)" 
                                                    class="text-green-600 hover:text-green-900 mr-3"
                                                >
                                                    Process
                                                </button>
                                            </template>
                                            <template x-if="record.payment_status === 'pending'">
                                                <button 
                                                    @click="confirmCancel(record.id)" 
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Cancel
                                                </button>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex items-center justify-between" x-show="!loading && records.length > 0">
                    <div class="text-sm text-gray-500">
                        Showing <span x-text="meta.from"></span> to <span x-text="meta.to"></span> of <span x-text="meta.total"></span> results
                    </div>
                    <div class="flex-1 flex justify-between sm:justify-end">
                        <button 
                            @click="previousPage()" 
                            :disabled="!meta.prev_page_url"
                            :class="{'opacity-50 cursor-not-allowed': !meta.prev_page_url}"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Previous
                        </button>
                        <button 
                            @click="nextPage()" 
                            :disabled="!meta.next_page_url"
                            :class="{'opacity-50 cursor-not-allowed': !meta.next_page_url}"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div 
        x-show="showCancelModal" 
        class="fixed z-10 inset-0 overflow-y-auto" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
        style="display: none;"
        :style="{ display: showCancelModal ? 'block' : 'none' }"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div 
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                aria-hidden="true"
                @click="showCancelModal = false"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Cancel Payroll Record
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to cancel this payroll record? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="cancelPayroll()"
                    >
                        Yes, cancel it
                    </button>
                    <button 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                        @click="showCancelModal = false"
                    >
                        No, keep it
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function payrollData() {
        return {
            loading: true,
            showCancelModal: false,
            selectedRecordId: null,
            records: [],
            employees: [],
            meta: {},
            filters: {
                employee_id: '',
                status: '',
                start_date: '',
                end_date: '',
                page: 1
            },
            
            async fetchPayrollRecords() {
                this.loading = true;
                try {
                    // Build query string from filters
                    const query = new URLSearchParams();
                    
                    if (this.filters.employee_id) {
                        query.append('employee_id', this.filters.employee_id);
                    }
                    
                    if (this.filters.status) {
                        query.append('payment_status', this.filters.status);
                    }
                    
                    if (this.filters.start_date) {
                        query.append('start_date', this.filters.start_date);
                    }
                    
                    if (this.filters.end_date) {
                        query.append('end_date', this.filters.end_date);
                    }
                    
                    query.append('page', this.filters.page);
                    
                    // Fetch data from API
                    const response = await fetch(`/api/payroll?${query.toString()}`);
                    const data = await response.json();
                    
                    this.records = data.data;
                    this.meta = {
                        current_page: data.current_page,
                        from: data.from,
                        last_page: data.last_page,
                        next_page_url: data.next_page_url,
                        path: data.path,
                        per_page: data.per_page,
                        prev_page_url: data.prev_page_url,
                        to: data.to,
                        total: data.total
                    };
                    
                } catch (error) {
                    console.error('Error fetching payroll records:', error);
                    // Show error message
                } finally {
                    this.loading = false;
                }
            },
            
            async fetchEmployees() {
                try {
                    const response = await fetch('/api/employees?per_page=100'); // Adjust per_page as needed
                    const data = await response.json();
                    this.employees = data.data;
                } catch (error) {
                    console.error('Error fetching employees:', error);
                }
            },
            
            async processPayroll(recordId) {
                if (!confirm('Are you sure you want to process this payroll record?')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/api/payroll/${recordId}/process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (response.ok) {
                        this.fetchPayrollRecords(); // Refresh the list
                        // Show success message
                        alert('Payroll record processed successfully');
                    } else {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to process payroll record');
                    }
                } catch (error) {
                    console.error('Error processing payroll record:', error);
                    alert(`Error: ${error.message}`);
                }
            },
            
            confirmCancel(recordId) {
                this.selectedRecordId = recordId;
                this.showCancelModal = true;
            },
            
            async cancelPayroll() {
                if (!this.selectedRecordId) return;
                
                this.showCancelModal = false;
                
                try {
                    const response = await fetch(`/api/payroll/${this.selectedRecordId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (response.ok) {
                        this.fetchPayrollRecords(); // Refresh the list
                        // Show success message
                        alert('Payroll record cancelled successfully');
                    } else {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to cancel payroll record');
                    }
                } catch (error) {
                    console.error('Error cancelling payroll record:', error);
                    alert(`Error: ${error.message}`);
                } finally {
                    this.selectedRecordId = null;
                }
            },
            
            nextPage() {
                if (this.meta.next_page_url) {
                    this.filters.page++;
                    this.fetchPayrollRecords();
                }
            },
            
            previousPage() {
                if (this.filters.page > 1) {
                    this.filters.page--;
                    this.fetchPayrollRecords();
                }
            },
            
            formatDate(dateString) {
                if (!dateString) return '';
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            },
            
            formatCurrency(amount) {
                if (amount === null || amount === undefined) return '0.00';
                return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }
        };
    }
</script>
@endpush
@endsection
