<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by employment type
        if ($request->has('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        // Filter by position
        if ($request->has('position')) {
            $query->where('position', $request->position);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort employees
        $sortField = $request->input('sort_field', 'last_name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $employees = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    /**
     * Store a newly created employee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees',
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:255',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'salary' => 'nullable|numeric|min:0',
            'payment_frequency' => 'required|in:weekly,bi-weekly,monthly',
            'tax_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'notes' => 'nullable|string',
            'create_user_account' => 'boolean',
            'password' => 'required_if:create_user_account,true|nullable|string|min:8',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = null;

            // Create user account if requested
            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
                
                $userId = $user->id;
            }

            // Create employee
            $employee = Employee::create([
                'user_id' => $userId,
                'staff_id' => $request->staff_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'termination_date' => $request->termination_date,
                'hourly_rate' => $request->hourly_rate,
                'salary' => $request->salary,
                'payment_frequency' => $request->payment_frequency,
                'tax_id' => $request->tax_id,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'is_active' => true,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::with([
            'user', 
            'staff',
            'payrollRecords' => function ($query) {
                $query->latest('payment_date')->limit(5);
            }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    /**
     * Update the specified employee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'position' => 'sometimes|required|string|max:255',
            'employment_type' => 'sometimes|required|in:full-time,part-time,contract',
            'hire_date' => 'sometimes|required|date',
            'termination_date' => 'nullable|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'salary' => 'nullable|numeric|min:0',
            'payment_frequency' => 'sometimes|required|in:weekly,bi-weekly,monthly',
            'tax_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employee->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => $employee->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified employee from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        try {
            // Instead of deleting, mark as inactive and set termination date
            $employee->update([
                'is_active' => false,
                'termination_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee deactivated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
