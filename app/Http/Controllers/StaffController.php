<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff members.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $staff = Staff::with('user.roles')->get();
        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff member.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.staff.create', compact('roles'));
    }

    /**
     * Store a newly created staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
            'profile_image' => 'nullable|image|max:2048',
            'work_days' => 'nullable|array',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i|after:work_start_time',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'employee.hourly_rate' => 'nullable|numeric|min:0|required_if:is_employee,on',
        ]);

        DB::beginTransaction();

        try {
            // Create user account
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role
            $role = Role::findById($request->role);
            $user->assignRole($role);

            // Handle profile image
            $profileImage = null;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image')->store('staff-profiles', 'public');
            }

            // Create staff record
            $staff = Staff::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'bio' => $request->bio,
                'profile_image' => $profileImage,
                'active' => $request->has('active'),
                'work_start_time' => $request->work_start_time,
                'work_end_time' => $request->work_end_time,
                'work_days' => $request->work_days,
                'user_id' => $user->id,
                'commission_rate' => $request->commission_rate,
                'specialties' => $request->specialties,
                'certifications' => $request->certifications,
                'languages' => $request->languages,
                'notes' => $request->notes,
            ]);
            
            // Create employee record if is_employee is checked
            if ($request->has('is_employee')) {
                $staff->employee()->create([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'position' => $request->position,
                    'hourly_rate' => $request->input('employee.hourly_rate'),
                    'is_active' => $request->has('active'),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create staff member: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified staff member.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $staff = Staff::with(['user.roles', 'user.permissions', 'services', 'appointments'])->findOrFail($id);
        return view('admin.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified staff member.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $staff = Staff::with('user.roles')->findOrFail($id);
        $roles = Role::all();
        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    /**
     * Update the specified staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($staff->user_id),
            ],
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
            'profile_image' => 'nullable|image|max:2048',
            'work_days' => 'nullable|array',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i|after:work_start_time',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'employee.hourly_rate' => 'nullable|numeric|min:0|required_if:is_employee,on',
        ]);

        DB::beginTransaction();

        try {
            // Update user account
            $user = User::findOrFail($staff->user_id);
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Update role
            $user->roles()->detach();
            $role = Role::findById($request->role);
            $user->assignRole($role);

            // Handle profile image
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($staff->profile_image) {
                    Storage::disk('public')->delete($staff->profile_image);
                }
                $profileImage = $request->file('profile_image')->store('staff-profiles', 'public');
                $staff->profile_image = $profileImage;
            }

            // Update staff record
            $staff->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'bio' => $request->bio,
                'active' => $request->has('active'),
                'work_start_time' => $request->work_start_time,
                'work_end_time' => $request->work_end_time,
                'work_days' => $request->work_days,
                'commission_rate' => $request->commission_rate,
                'specialties' => $request->specialties,
                'certifications' => $request->certifications,
                'languages' => $request->languages,
                'notes' => $request->notes,
            ]);
            
            // Handle employee record based on is_employee checkbox
            if ($request->has('is_employee')) {
                // Create or update employee record
                if ($staff->employee) {
                    // Update existing employee record
                    $staff->employee->update([
                        'hourly_rate' => $request->input('employee.hourly_rate'),
                        'is_active' => $request->has('active'),
                    ]);
                } else {
                    // Create new employee record
                    $staff->employee()->create([
                        'user_id' => $user->id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'position' => $request->position,
                        'hourly_rate' => $request->input('employee.hourly_rate'),
                        'is_active' => $request->has('active'),
                    ]);
                }
            } else if ($staff->employee) {
                // Remove employee record if exists and checkbox is unchecked
                $staff->employee->delete();
            }

            DB::commit();

            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update staff member: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified staff member from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);

        DB::beginTransaction();
        try {
            // Soft delete the staff record
            $staff->delete();

            DB::commit();
            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete staff member: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the roles and permissions management page.
     *
     * @return \Illuminate\View\View
     */
    public function rolesAndPermissions()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('admin.staff.roles', compact('roles', 'permissions'));
    }

    /**
     * Store a new role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'guard_name' => 'web',
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return redirect()->route('admin.staff.roles')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create role: ' . $e->getMessage()]);
        }
    }

    /**
     * Update an existing role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();

        try {
            $role->update([
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return redirect()->route('admin.staff.roles')
                ->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update role: ' . $e->getMessage()]);
        }
    }
}
