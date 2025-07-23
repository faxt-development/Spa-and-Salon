@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="payrollRecord()" x-init="init()">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Payroll Record #{{ $payrollRecord->id }}</h1>
        <div class="space-x-2">
            <a href="{{ route('payroll.records.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Back to Records
            </a>
            <a href="{{ route('payroll.records.generate', ['employee' => $payrollRecord->employee_id, 'start' => $payrollRecord->pay_period_start, 'end' => $payrollRecord->pay_period_end]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Generate PDF
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h2 class="text-lg leading-6 font-medium text-gray-900">
                Payroll Information
            </h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Details for payroll record #{{ $payrollRecord->id }}
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Employee</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $payrollRecord->employee->staff->first_name }} {{ $payrollRecord->employee->staff->last_name }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Pay Period</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($payrollRecord->pay_period_start)->format('M j, Y') }} -
                        {{ \Carbon\Carbon::parse($payrollRecord->pay_period_end)->format('M j, Y') }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($payrollRecord->payment_date)->format('F j, Y') }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span :class="{
                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                            'bg-yellow-100 text-yellow-800': '{{ $payrollRecord->payment_status }}' === 'pending',
                            'bg-green-100 text-green-800': '{{ $payrollRecord->payment_status }}' === 'processed',
                            'bg-red-100 text-red-800': '{{ $payrollRecord->payment_status }}' === 'cancelled'
                        }">
                            {{ ucfirst($payrollRecord->payment_status) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h2 class="text-lg leading-6 font-medium text-gray-900">
                Earnings
            </h2>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Regular Hours</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ number_format($payrollRecord->hours_worked, 2) }} hours
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Overtime Hours</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ number_format($payrollRecord->overtime_hours, 2) }} hours
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Hourly Rate</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->employee->hourly_rate, 2) }}/hour
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Overtime Rate</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->employee->hourly_rate * 1.5, 2) }}/hour
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Regular Pay</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->hours_worked * $payrollRecord->employee->hourly_rate, 2) }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Overtime Pay</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->overtime_hours * ($payrollRecord->employee->hourly_rate * 1.5), 2) }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t-2 border-gray-200">
                    <dt class="text-base font-medium text-gray-900">Gross Pay</dt>
                    <dd class="mt-1 text-base font-medium text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->gross_amount, 2) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h2 class="text-lg leading-6 font-medium text-gray-900">
                Deductions
            </h2>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Taxes</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->tax_amount, 2) }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Other Deductions</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->deductions, 2) }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t-2 border-gray-200">
                    <dt class="text-base font-medium text-gray-900">Total Deductions</dt>
                    <dd class="mt-1 text-base font-medium text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->tax_amount + $payrollRecord->deductions, 2) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h2 class="text-lg leading-6 font-medium text-gray-900">
                Payment Details
            </h2>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Net Pay</dt>
                    <dd class="mt-1 text-lg font-bold text-gray-900 sm:mt-0 sm:col-span-2">
                        ${{ number_format($payrollRecord->net_amount, 2) }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ ucwords(str_replace('_', ' ', $payrollRecord->payment_method)) }}
                    </dd>
                </div>
                @if($payrollRecord->reference_number)
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $payrollRecord->reference_number }}
                    </dd>
                </div>
                @endif
                @if($payrollRecord->notes)
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-line">
                        {{ $payrollRecord->notes }}
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function payrollRecord() {
        return {
            init() {
                // Any initialization code can go here
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                }).format(amount);
            },

            formatDate(dateString) {
                if (!dateString) return '';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            }
        };
    }
</script>
@endpush
@endsection
