@extends('layouts.app')

@section('content')
<div class="py-6" x-data="payrollGeneration()" x-init="init()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Generate Payroll</h2>

                <!-- Payroll Generation Form -->
                <form @submit.prevent="generatePayroll" class="space-y-6">
                    <!-- Pay Period Selection -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Pay Period</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Pay Period Type -->
                            <div>
                                <label for="pay_period_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pay Period Type
                                </label>
                                <select
                                    id="pay_period_type"
                                    x-model="form.payPeriodType"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    @change="loadPayPeriods"
                                >
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-weekly</option>
                                    <option value="semimonthly">Semi-monthly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>

                            <!-- Pay Period -->
                            <div>
                                <label for="pay_period" class="block text-sm font-medium text-gray-700 mb-1">
                                    Select Pay Period
                                </label>
                                <select
                                    id="pay_period"
                                    x-model="form.payPeriodId"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    :disabled="!form.payPeriodType"
                                    required
                                >
                                    <option value="">Select a pay period</option>
                                    <template x-for="period in payPeriods" :key="period.id">
                                        <option :value="period.id" x-text="period.label"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Pay Date -->
                            <div>
                                <label for="pay_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pay Date
                                </label>
                                <input
                                    type="date"
                                    id="pay_date"
                                    x-model="form.payDate"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Employee Selection -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Employees</h3>
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="select_all"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    @change="toggleSelectAll"
                                >
                                <label for="select_all" class="ml-2 block text-sm text-gray-700">
                                    Select All
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2 max-h-96 overflow-y-auto p-2 border rounded-md">
                            <template x-if="loadingEmployees">
                                <div class="text-center py-4">
                                    <div class="flex justify-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Loading employees...</span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!loadingEmployees && employees.length === 0">
                                <div class="text-center py-4 text-gray-500">
                                    No employees found.
                                </div>
                            </template>
                            <template x-for="employee in employees" :key="employee.id">
                                <div class="flex items-center p-2 hover:bg-gray-100 rounded">
                                    <input
                                        type="checkbox"
                                        :id="'employee_' + employee.id"
                                        :value="employee.id"
                                        x-model="form.selectedEmployeeIds"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                    <label
                                        :for="'employee_' + employee.id"
                                        class="ml-3 block text-sm font-medium text-gray-700"
                                    >
                                        <span x-text="employee.staff.first_name + ' ' + employee.staff.last_name"></span>
                                        <span class="text-gray-500 text-xs block" x-text="employee.employee_id"></span>
                                    </label>
                                    <div class="ml-auto text-sm text-gray-500">
                                        <span x-text="formatCurrency(employee.hourly_rate || 0)"></span>/hr
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Options</h3>

                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="include_taxes"
                                        type="checkbox"
                                        x-model="form.includeTaxes"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="include_taxes" class="font-medium text-gray-700">Calculate Taxes</label>
                                    <p class="text-gray-500">Automatically calculate and deduct taxes based on employee tax settings.</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="include_deductions"
                                        type="checkbox"
                                        x-model="form.includeDeductions"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="include_deductions" class="font-medium text-gray-700">Include Deductions</label>
                                    <p class="text-gray-500">Include employee benefits and other deductions in the payroll calculation.</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="process_payments"
                                        type="checkbox"
                                        x-model="form.processPayments"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="process_payments" class="font-medium text-gray-700">Process Payments</label>
                                    <p class="text-gray-500">Automatically process payments for employees with direct deposit setup.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            id="notes"
                            x-model="form.notes"
                            rows="3"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="Add any notes about this payroll run..."
                        ></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <a
                            href="{{ route('payroll.records.index') }}"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :disabled="processing"
                        >
                            <span x-show="!processing">Generate Payroll</span>
                            <span x-show="processing">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function payrollGeneration() {
        return {
            loadingEmployees: false,
            loadingPayPeriods: false,
            processing: false,
            employees: [],
            payPeriods: [],
            form: {
                payPeriodType: 'biweekly',
                payPeriodId: '',
                payDate: '',
                selectedEmployeeIds: [],
                includeTaxes: true,
                includeDeductions: true,
                processPayments: false,
                notes: ''
            },

            init() {
                // Set default pay date to next Friday
                const today = new Date();
                const nextFriday = new Date(today);
                nextFriday.setDate(today.getDate() + ((5 - today.getDay() + 7) % 7 || 7));
                this.form.payDate = nextFriday.toISOString().split('T')[0];

                // Load initial data
                this.loadEmployees();
                this.loadPayPeriods();
            },

            async loadEmployees() {
                this.loadingEmployees = true;
                try {
                    const response = await fetch('/api/employees?per_page=1000&status=active');
                    const data = await response.json();
                    this.employees = data.data || [];
                } catch (error) {
                    console.error('Error loading employees:', error);
                    this.showError('Failed to load employees. Please try again.');
                } finally {
                    this.loadingEmployees = false;
                }
            },

            async loadPayPeriods() {
                if (!this.form.payPeriodType) return;

                this.loadingPayPeriods = true;
                this.form.payPeriodId = '';

                try {
                    // This would be an API call to get pay periods based on type
                    // For now, we'll simulate it with client-side logic
                    const periods = this.generatePayPeriods(this.form.payPeriodType);
                    this.payPeriods = periods;

                    // Auto-select the current/latest pay period
                    if (periods.length > 0) {
                        this.form.payPeriodId = periods[0].id;
                    }
                } catch (error) {
                    console.error('Error loading pay periods:', error);
                    this.showError('Failed to load pay periods. Please try again.');
                } finally {
                    this.loadingPayPeriods = false;
                }
            },

            generatePayPeriods(type) {
                const periods = [];
                const now = new Date();

                // Generate pay periods for the last 4 cycles
                for (let i = 0; i < 4; i++) {
                    let startDate, endDate, label;

                    if (type === 'weekly') {
                        // Start of current week (Sunday)
                        const weekStart = new Date(now);
                        weekStart.setDate(now.getDate() - now.getDay() - (i * 7));
                        startDate = new Date(weekStart);
                        endDate = new Date(weekStart);
                        endDate.setDate(weekStart.getDate() + 6);

                        label = `Week of ${this.formatDate(startDate)} to ${this.formatDate(endDate)}`;
                    }
                    else if (type === 'biweekly') {
                        // Start of current bi-weekly period (assuming pay periods start on Sunday)
                        const weeksAgo = i * 2;
                        const periodStart = new Date(now);
                        periodStart.setDate(now.getDate() - now.getDay() - (weeksAgo * 7));

                        startDate = new Date(periodStart);
                        endDate = new Date(periodStart);
                        endDate.setDate(periodStart.getDate() + 13); // 2 weeks

                        label = `Bi-weekly: ${this.formatDate(startDate)} to ${this.formatDate(endDate)}`;
                    }
                    else if (type === 'semimonthly') {
                        // 1st-15th and 16th-end of month
                        const month = now.getMonth() - Math.floor((i + (now.getDate() > 15 ? 0 : 1)) / 2);
                        const year = now.getFullYear() - (month < 0 ? 1 : 0);
                        const adjustedMonth = ((month % 12) + 12) % 12;

                        if (i % 2 === 0) {
                            // Second half of previous month
                            startDate = new Date(year, adjustedMonth, 16);
                            endDate = new Date(year, adjustedMonth + 1, 0); // Last day of month
                        } else {
                            // First half of current month
                            startDate = new Date(year, adjustedMonth, 1);
                            endDate = new Date(year, adjustedMonth, 15);
                        }

                        label = `Semi-monthly: ${this.formatDate(startDate)} to ${this.formatDate(endDate)}`;
                    }
                    else if (type === 'monthly') {
                        // Whole month
                        const month = now.getMonth() - i;
                        const year = now.getFullYear() - (month < 0 ? 1 : 0);
                        const adjustedMonth = ((month % 12) + 12) % 12;

                        startDate = new Date(year, adjustedMonth, 1);
                        endDate = new Date(year, adjustedMonth + 1, 0); // Last day of month

                        label = `Monthly: ${startDate.toLocaleString('default', { month: 'long' })} ${year}`;
                    }

                    periods.push({
                        id: `period_${i}`,
                        startDate: startDate.toISOString().split('T')[0],
                        endDate: endDate.toISOString().split('T')[0],
                        label: label
                    });
                }

                return periods;
            },

            toggleSelectAll() {
                const selectAllCheckbox = document.getElementById('select_all');
                if (selectAllCheckbox.checked) {
                    this.form.selectedEmployeeIds = this.employees.map(emp => emp.id);
                } else {
                    this.form.selectedEmployeeIds = [];
                }
            },

            async generatePayroll() {
                if (this.form.selectedEmployeeIds.length === 0) {
                    this.showError('Please select at least one employee.');
                    return;
                }

                if (!this.form.payPeriodId) {
                    this.showError('Please select a pay period.');
                    return;
                }

                this.processing = true;

                try {
                    // Get the selected pay period details
                    const selectedPeriod = this.payPeriods.find(p => p.id === this.form.payPeriodId);

                    // Prepare the payload
                    const payload = {
                        employee_ids: this.form.selectedEmployeeIds,
                        pay_period_type: this.form.payPeriodType,
                        start_date: selectedPeriod.startDate,
                        end_date: selectedPeriod.endDate,
                        pay_date: this.form.payDate,
                        include_taxes: this.form.includeTaxes,
                        include_deductions: this.form.includeDeductions,
                        process_payments: this.form.processPayments,
                        notes: this.form.notes
                    };

                    // Call the API to generate payroll
                    const response = await fetch('/api/payroll/generate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to generate payroll');
                    }

                    const result = await response.json();

                    // Redirect to the generated payroll record
                    if (result.data && result.data.id) {
                        window.location.href = `/payroll/records/${result.data.id}`;
                    } else {
                        window.location.href = '/payroll/records';
                    }

                } catch (error) {
                    console.error('Error generating payroll:', error);
                    this.showError(error.message || 'Failed to generate payroll. Please try again.');
                } finally {
                    this.processing = false;
                }
            },

            formatDate(date) {
                return new Date(date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                }).format(amount);
            },

            showError(message) {
                // You can replace this with a more sophisticated notification system
                alert(`Error: ${message}`);
            }
        };
    }
</script>
@endpush
@endsection
