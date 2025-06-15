<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeClockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TimeClockController extends Controller
{
    /**
     * Display a listing of time clock entries.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = TimeClockEntry::with(['employee', 'employee.staff', 'approvedByUser']);

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by approval status
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('clock_in', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('clock_in', '<=', $request->end_date);
        }

        // Filter by active/completed entries
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('clock_in')->whereNull('clock_out');
            } elseif ($request->status === 'completed') {
                $query->whereNotNull('clock_in')->whereNotNull('clock_out');
            }
        }

        // Sort time clock entries
        $sortField = $request->input('sort_field', 'clock_in');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $entries = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $entries,
        ]);
    }

    /**
     * Clock in an employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::findOrFail($request->employee_id);

        // Check if employee is already clocked in
        $activeEntry = $employee->getCurrentTimeClockEntry();
        if ($activeEntry) {
            return response()->json([
                'success' => false,
                'message' => 'Employee is already clocked in',
                'data' => $activeEntry,
            ], 422);
        }

        try {
            $entry = TimeClockEntry::create([
                'employee_id' => $employee->id,
                'clock_in' => now(),
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee clocked in successfully',
                'data' => $entry->load(['employee', 'employee.staff']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock in employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clock out an employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::findOrFail($request->employee_id);

        // Check if employee is clocked in
        $activeEntry = $employee->getCurrentTimeClockEntry();
        if (!$activeEntry) {
            return response()->json([
                'success' => false,
                'message' => 'Employee is not clocked in',
            ], 422);
        }

        try {
            // Update the clock out time
            $activeEntry->clock_out = now();
            
            // Calculate hours worked
            $activeEntry->hours = $activeEntry->calculateHours();
            
            // Update notes if provided
            if ($request->has('notes')) {
                $activeEntry->notes = $request->notes;
            }
            
            $activeEntry->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee clocked out successfully',
                'data' => [
                    'entry' => $activeEntry->fresh()->load(['employee', 'employee.staff']),
                    'hours_worked' => $activeEntry->hours,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock out employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a time clock entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $entry = TimeClockEntry::findOrFail($id);

        // Check if entry is already approved
        if ($entry->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Time clock entry is already approved',
            ], 422);
        }

        // Check if entry is completed (has clock out time)
        if (!$entry->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve an incomplete time clock entry',
            ], 422);
        }

        try {
            $entry->update([
                'is_approved' => true,
                'approved_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time clock entry approved successfully',
                'data' => $entry->fresh()->load(['employee', 'employee.staff', 'approvedByUser']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve time clock entry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a time clock entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $entry = TimeClockEntry::findOrFail($id);

        // Don't allow updating approved entries
        if ($entry->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update an approved time clock entry',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'clock_in' => 'sometimes|required|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $entry->update($request->only(['clock_in', 'clock_out', 'notes']));
            
            // Recalculate hours if both clock in and clock out are set
            if ($entry->clock_in && $entry->clock_out) {
                $entry->hours = $entry->calculateHours();
                $entry->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Time clock entry updated successfully',
                'data' => $entry->fresh()->load(['employee', 'employee.staff']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update time clock entry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current status for an employee.
     *
     * @param  int  $employeeId
     * @return \Illuminate\Http\Response
     */
    public function status($employeeId)
    {
        $employee = Employee::with('staff')->findOrFail($employeeId);
        $activeEntry = $employee->getCurrentTimeClockEntry();
        
        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee,
                'is_clocked_in' => $activeEntry !== null,
                'active_entry' => $activeEntry,
            ],
        ]);
    }

    /**
     * Get weekly report for an employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $employeeId
     * @return \Illuminate\Http\Response
     */
    public function weeklyReport(Request $request, $employeeId)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::with('staff')->findOrFail($employeeId);
        
        $entries = TimeClockEntry::where('employee_id', $employeeId)
            ->whereDate('clock_in', '>=', $request->start_date)
            ->whereDate('clock_in', '<=', $request->end_date)
            ->whereNotNull('clock_out')
            ->orderBy('clock_in')
            ->get();
            
        $totalHours = $entries->sum('hours');
        $approvedHours = $entries->where('is_approved', true)->sum('hours');
        
        // Group by date
        $dailyHours = [];
        foreach ($entries as $entry) {
            $date = $entry->clock_in->format('Y-m-d');
            if (!isset($dailyHours[$date])) {
                $dailyHours[$date] = 0;
            }
            $dailyHours[$date] += $entry->hours;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee,
                'period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
                'total_hours' => $totalHours,
                'approved_hours' => $approvedHours,
                'pending_hours' => $totalHours - $approvedHours,
                'daily_hours' => $dailyHours,
                'entries' => $entries,
            ],
        ]);
    }
}
