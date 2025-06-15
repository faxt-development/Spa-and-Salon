<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\TimeClockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = PayrollRecord::with('employee');

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        // Sort payroll records
        $sortField = $request->input('sort_field', 'payment_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $payrollRecords = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payrollRecords,
        ]);
    }

    /**
     * Store a newly created payroll record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'payment_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'gross_amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:direct_deposit,check,cash',
            'payment_status' => 'required|in:pending,processed,cancelled',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payrollRecord = PayrollRecord::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Payroll record created successfully',
                'data' => $payrollRecord->load('employee'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payroll record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payroll record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payrollRecord = PayrollRecord::with(['employee', 'employee.staff'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payrollRecord,
        ]);
    }

    /**
     * Update the specified payroll record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $payrollRecord = PayrollRecord::findOrFail($id);

        // Don't allow updating processed payroll records
        if ($payrollRecord->payment_status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update a processed payroll record',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'pay_period_start' => 'sometimes|required|date',
            'pay_period_end' => 'sometimes|required|date|after_or_equal:pay_period_start',
            'payment_date' => 'sometimes|required|date',
            'hours_worked' => 'sometimes|required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'gross_amount' => 'sometimes|required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'sometimes|required|in:direct_deposit,check,cash',
            'payment_status' => 'sometimes|required|in:pending,processed,cancelled',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payrollRecord->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Payroll record updated successfully',
                'data' => $payrollRecord->fresh()->load('employee'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payroll record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process the specified payroll record (mark as processed).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function process($id)
    {
        $payrollRecord = PayrollRecord::findOrFail($id);

        if ($payrollRecord->payment_status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record is already processed',
            ], 422);
        }

        if ($payrollRecord->payment_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process a cancelled payroll record',
            ], 422);
        }

        try {
            $payrollRecord->update([
                'payment_status' => 'processed',
                'payment_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll record processed successfully',
                'data' => $payrollRecord->fresh()->load('employee'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payroll record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel the specified payroll record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $payrollRecord = PayrollRecord::findOrFail($id);

        if ($payrollRecord->payment_status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a processed payroll record',
            ], 422);
        }

        try {
            $payrollRecord->update([
                'payment_status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll record cancelled successfully',
                'data' => $payrollRecord->fresh()->load('employee'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payroll record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate payroll records for all active employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generatePayroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:direct_deposit,check,cash',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $employees = Employee::where('is_active', true)->get();
            $payrollRecords = [];

            foreach ($employees as $employee) {
                // Calculate hours worked from time clock entries
                $timeEntries = TimeClockEntry::where('employee_id', $employee->id)
                    ->whereDate('clock_in', '>=', $request->pay_period_start)
                    ->whereDate('clock_in', '<=', $request->pay_period_end)
                    ->whereNotNull('clock_out')
                    ->get();

                $hoursWorked = $timeEntries->sum('hours') ?? 0;
                $overtimeHours = 0;

                // Calculate overtime (hours over 40 per week)
                if ($hoursWorked > 40) {
                    $overtimeHours = $hoursWorked - 40;
                    $hoursWorked = 40;
                }

                // Calculate gross amount based on hourly rate or salary
                $grossAmount = 0;
                if ($employee->hourly_rate) {
                    $grossAmount = ($hoursWorked * $employee->hourly_rate) + ($overtimeHours * $employee->hourly_rate * 1.5);
                } elseif ($employee->salary) {
                    // Calculate prorated salary for the pay period
                    $annualSalary = $employee->salary;
                    switch ($employee->payment_frequency) {
                        case 'weekly':
                            $grossAmount = $annualSalary / 52;
                            break;
                        case 'bi-weekly':
                            $grossAmount = $annualSalary / 26;
                            break;
                        case 'monthly':
                            $grossAmount = $annualSalary / 12;
                            break;
                        default:
                            $grossAmount = $annualSalary / 26; // Default to bi-weekly
                    }
                }

                // Calculate tax (simplified - in a real app, this would be more complex)
                $taxRate = 0.2; // 20% tax rate
                $taxAmount = $grossAmount * $taxRate;
                $netAmount = $grossAmount - $taxAmount;

                // Create payroll record
                $payrollRecord = PayrollRecord::create([
                    'employee_id' => $employee->id,
                    'pay_period_start' => $request->pay_period_start,
                    'pay_period_end' => $request->pay_period_end,
                    'payment_date' => $request->payment_date,
                    'hours_worked' => $hoursWorked,
                    'overtime_hours' => $overtimeHours,
                    'gross_amount' => $grossAmount,
                    'tax_amount' => $taxAmount,
                    'deductions' => 0, // No additional deductions in this simplified example
                    'net_amount' => $netAmount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'pending',
                    'notes' => $request->notes,
                ]);

                $payrollRecords[] = $payrollRecord;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll generated successfully for ' . count($payrollRecords) . ' employees',
                'data' => $payrollRecords,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payroll',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
