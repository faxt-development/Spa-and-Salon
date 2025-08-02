<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Service;
use App\Models\User;
use App\Models\BusinessHour;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff members.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before managing staff members.');
        }

        // Get staff members belonging to the current company via user-company pivot
        $userIds = $company->users()->pluck('users.id');
        $staff = Staff::whereIn('user_id', $userIds)->with('user.roles')->get();

        // Check if the current admin is already a staff member
        $adminIsStaff = Staff::where('user_id', $user->id)->exists();

        // Check if there are no staff members yet
        $noStaff = $staff->isEmpty();

        return view('admin.staff.index', compact('staff', 'noStaff', 'adminIsStaff', 'user', 'company'));
    }

    /**
     * Show the form for creating a new staff member.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before adding staff members.');
        }

        $roles = Role::all();
        $prefill = null; // Default to no pre-filled data

        return view('admin.staff.create', compact('roles', 'company', 'prefill'));
    }

    /**
     * Store a newly created staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Staff store method called', ['request' => $request->all()]);

        // Get the current admin's primary company
        $adminUser = auth()->user();
        $company = $adminUser->primaryCompany();
        \Illuminate\Support\Facades\Log::info('Current company', ['company_id' => $company ? $company->id : null]);

        if (!$company) {
            \Illuminate\Support\Facades\Log::warning('No company found for user');
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before adding staff members.');
        }

        // Prepare validation rules
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'role' => 'required|exists:roles,id',
            'profile_image' => 'nullable|image|max:2048', // Profile image is optional
            'work_days' => 'nullable|array',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i|after:work_start_time',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'employee.hourly_rate' => 'nullable|numeric|min:0|required_if:is_employee,on',
        ];

        // Only validate email uniqueness if we're not using an existing user
        if ($request->has('user_id')) {
            \Illuminate\Support\Facades\Log::info('Using existing user', ['user_id' => $request->user_id]);
            $validationRules['email'] = 'required|string|email|max:255';
            // Password not required when using existing user
            if (!$request->has('is_admin')) {
                $validationRules['password'] = 'required|string|min:8|confirmed';
            } else {
                \Illuminate\Support\Facades\Log::info('Admin is adding themselves as staff');
            }
        } else {
            \Illuminate\Support\Facades\Log::info('Creating new user');
            $validationRules['email'] = 'required|string|email|max:255|unique:users,email';
            $validationRules['password'] = 'required|string|min:8|confirmed';
        }

        \Illuminate\Support\Facades\Log::info('Validation rules', ['rules' => $validationRules]);

        try {
            $validated = $request->validate($validationRules);
            \Illuminate\Support\Facades\Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed', ['errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        DB::beginTransaction();

        try {
            // Check if we're using an existing user (admin adding themselves)
            if ($request->has('user_id')) {
                \Illuminate\Support\Facades\Log::info('Finding existing user', ['user_id' => $request->user_id]);
                $user = User::findOrFail($request->user_id);
                \Illuminate\Support\Facades\Log::info('Found user', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_company_id' => $user->company_id
                ]);

                // Check if the user is already associated with this company
                $existingRelationship = $user->companies()->where('company_id', $company->id)->exists();

                if (!$existingRelationship && $request->has('is_admin')) {
                    // If this is an admin adding themselves to a new company
                    \Illuminate\Support\Facades\Log::info('Adding user to company', [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                        'as_admin' => true
                    ]);

                    // Associate the user with the company
                    $user->companies()->attach($company->id, [
                        'is_primary' => false, // Not making it primary by default
                        'role' => 'admin'
                    ]);
                }
                // For non-admin users, make sure they belong to this company
                elseif (!$existingRelationship) {
                    \Illuminate\Support\Facades\Log::warning('User not associated with this company', [
                        'user_id' => $user->id,
                        'company_id' => $company->id
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'User does not belong to your company.');
                }

                // Update role if specified
                if ($request->has('role')) {
                    \Illuminate\Support\Facades\Log::info('Updating user role', ['role_id' => $request->role]);
                    $role = Role::findById($request->role);
                    $user->syncRoles([$role]);
                }
            } else {
                // Create new user account
                \Illuminate\Support\Facades\Log::info('Creating new user', ['email' => $request->email]);
                $user = User::create([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    // No longer setting company_id directly
                ]);
                \Illuminate\Support\Facades\Log::info('User created', ['user_id' => $user->id]);

                // Associate the user with the company using the pivot table
                $user->companies()->attach($company->id, [
                    'is_primary' => true, // Make this the primary company for new users
                    'role' => 'staff'
                ]);

                \Illuminate\Support\Facades\Log::info('Associated new user with company', [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'is_primary' => true
                ]);

                // Assign role
                $role = Role::findById($request->role);
                $user->assignRole($role);
                \Illuminate\Support\Facades\Log::info('Role assigned', ['role' => $role->name]);
            }

            // Handle profile image
            $profileImage = null;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image')->store('staff-profiles', 'public');
            }

            // Create staff record
            \Illuminate\Support\Facades\Log::info('Creating staff record', [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name
            ]);

            // Check if a staff record already exists for this user
            $existingStaff = Staff::where('user_id', $user->id)->first();
            if ($existingStaff) {
                \Illuminate\Support\Facades\Log::warning('Staff record already exists for this user', ['staff_id' => $existingStaff->id]);
                return redirect()->route('admin.staff.index')
                    ->with('info', 'This user is already registered as a staff member.');
            }

            $staffData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'bio' => $request->bio,
                'profile_image' => $profileImage, // This is nullable, so it's fine if it's null
                'active' => $request->has('active'),
                'work_start_time' => $request->work_start_time,
                'work_end_time' => $request->work_end_time,
                'work_days' => $request->work_days,
                'user_id' => $user->id,
                'company_id' => $company->id, // Associate staff with the admin's company
                'commission_rate' => $request->commission_rate,
                'specialties' => $request->specialties,
                'certifications' => $request->certifications,
                'languages' => $request->languages,
                'notes' => $request->notes,
            ];

            \Illuminate\Support\Facades\Log::info('Staff data prepared', ['data' => $staffData]);

            try {
                $staff = Staff::create($staffData);
                \Illuminate\Support\Facades\Log::info('Staff record created', ['staff_id' => $staff->id]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to create staff record', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            // Create employee record if is_employee is checked
            if ($request->has('is_employee')) {
                $employeeData = $request->input('employee');
                $staff->employee()->create([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'position' => $request->position,
                    'hourly_rate' => $employeeData['hourly_rate'] ?? null,
                    'hire_date' => $employeeData['hire_date'] ?? date('Y-m-d'),
                    'is_active' => $request->has('active'),
                ]);
            }

            \Illuminate\Support\Facades\Log::info('Committing transaction');
            DB::commit();

            \Illuminate\Support\Facades\Log::info('Staff creation successful, redirecting to index');
            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member created successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception in staff creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();

            // Provide a more user-friendly error message
            $errorMessage = 'Failed to create staff member';
            if (app()->environment('local', 'development', 'testing')) {
                $errorMessage .= ': ' . $e->getMessage();
            } else {
                $errorMessage .= '. Please try again or contact support.';
            }

            return back()
                ->withInput()
                ->with('error', $errorMessage);
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
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before viewing staff members.');
        }

        // Get staff member
        $staff = Staff::with(['user.roles', 'user.permissions', 'services', 'appointments'])->findOrFail($id);

        // Check if this staff member's user is associated with the current company
        // First get the user IDs associated with this company
        $userIds = DB::table('company_user')
            ->where('company_id', $company->id)
            ->pluck('user_id');

        if (!$userIds->contains($staff->user_id)) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'You do not have permission to view this staff member.');
        }

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
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before editing staff members.');
        }

        // Get staff member
        $staff = Staff::with('user.roles')->findOrFail($id);

        // Check if this staff member's user is associated with the current company
        // First get the user IDs associated with this company
        $userIds = DB::table('company_user')
            ->where('company_id', $company->id)
            ->pluck('user_id');

        if (!$userIds->contains($staff->user_id)) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'You do not have permission to edit this staff member.');
        }

        // Get locations that belong to the current company
        $locations = Location::where('company_id', $company->id)->orderBy('name')->get();

        $roles = Role::all();
        return view('admin.staff.edit', compact('staff', 'roles', 'locations'));
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
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before updating staff members.');
        }
        info($request->all());
        // Get staff member
        $staff = Staff::findOrFail($id);

        // Check if this staff member's user is associated with the current company
        // First get the user IDs associated with this company
        $userIds = DB::table('company_user')
            ->where('company_id', $company->id)
            ->pluck('user_id');

        if (!$userIds->contains($staff->user_id)) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'You do not have permission to update this staff member.');
        }

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

            // Get timezone for conversion
            $timezone = null;
            if ($request->location_id) {
                $location = Location::find($request->location_id);
                $timezone = $location ? $location->timezone : null;
            }

            if (!$timezone && $company) {
                $primaryLocation = $company->locations()->where('is_primary', true)->first();
                $timezone = $primaryLocation ? $primaryLocation->timezone : null;
            }

            // Default to UTC if no timezone found
            $timezone = $timezone ?: 'UTC';

            // Convert work times from local timezone to UTC for storage
            $workStartTime = null;
            $workEndTime = null;

            if ($request->work_start_time) {
                $workStartTime = \Carbon\Carbon::createFromFormat(
                    'H:i',
                    $request->work_start_time,
                    $timezone
                )->setTimezone('UTC');
            }

            if ($request->work_end_time) {
                $workEndTime = \Carbon\Carbon::createFromFormat(
                    'H:i',
                    $request->work_end_time,
                    $timezone
                )->setTimezone('UTC');
            }

            // Update staff record
            $staffData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'bio' => $request->bio,
                'active' => $request->has('active'),
                'work_start_time' => $workStartTime,
                'work_end_time' => $workEndTime,
                'work_days' => $request->work_days,
                'commission_rate' => $request->commission_rate,
                'location_id' => $request->location_id,
                'specialties' => $request->specialties,
                'certifications' => $request->certifications,
                'languages' => $request->languages,
                'notes' => $request->notes,
            ];

            $staff->update($staffData);

            // Debug employee data
            info('Employee data from request:', [
                'employee_data' => $request->input('employee'),
                'hire_date' => $request->input('employee.hire_date'),
                'hire_date_array' => $request->input('employee')['hire_date'] ?? null,
            ]);

            // Handle employee record based on is_employee checkbox
            if ($request->has('is_employee')) {
                // Create or update employee record
                if ($staff->employee) {
                    // Update existing employee record
                    $employeeData = $request->input('employee');
                    $staff->employee->update([
                        'hourly_rate' => $employeeData['hourly_rate'] ?? null,
                        'hire_date' => $employeeData['hire_date'] ?? date('Y-m-d'),
                        'is_active' => $request->has('active'),
                    ]);
                } else {
                    // Create new employee record
                    $employeeData = $request->input('employee');
                    $staff->employee()->create([
                        'user_id' => $user->id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'position' => $request->position,
                        'hourly_rate' => $employeeData['hourly_rate'] ?? null,
                        'hire_date' => $employeeData['hire_date'] ?? date('Y-m-d'),
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
     * Add the current admin user as a staff member
     */
    public function addAdminAsStaff()
    {
        // Get the current admin's primary company
        $currentUser = auth()->user();
        $company = $currentUser->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before adding yourself as staff.');
        }

        // Check if the admin is already a staff member
        $existingStaff = Staff::where('user_id', $currentUser->id)->first();

        if ($existingStaff) {
            return redirect()->route('admin.staff.edit', $existingStaff->id)
                ->with('info', 'You are already registered as a staff member. You can edit your details here.');
        }

        // Pre-fill the form with admin's information
        $nameParts = explode(' ', $currentUser->name, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

        return view('admin.staff.create', [
            'roles' => Role::all(),
            'prefill' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $currentUser->email,
                'user_id' => $currentUser->id,
                'is_admin' => true
            ]
        ]);
    }

    /**
     * Remove the specified staff member from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before deleting staff members.');
        }

        // Get staff member
        $staff = Staff::findOrFail($id);

        // Check if this staff member's user is associated with the current company
        // First get the user IDs associated with this company
        $userIds = DB::table('company_user')
            ->where('company_id', $company->id)
            ->pluck('user_id');

        if (!$userIds->contains($staff->user_id)) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'You do not have permission to delete this staff member.');
        }

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

    /**
     * Display the staff availability management page.
     *
     * @return \Illuminate\View\View
     */
    public function availability()
    {
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before managing staff availability.');
        }

        // Get staff members belonging to the current company via user-company pivot
        $userIds = $company->users()->pluck('users.id');
        $staff = Staff::whereIn('user_id', $userIds)->with('user.roles')->active()->get();

        // Get all 7 days of the week for the availability calendar, starting with Monday
        $startDate = now()->startOfWeek(); // Start with Monday
        $dateRange = [];

        // Create a fixed order of days: Monday through Sunday
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateRange[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'full_day' => $date->format('l')
            ];
        }

        $endDate = $startDate->copy()->addDays(6)->endOfDay(); // Sunday end of day

        // Get business hours for the company
        $businessHours = [];

        // First check if there's a primary location
        $primaryLocation = $company->locations()->where('is_primary', true)->first();

        if ($primaryLocation) {
            // Try to get business hours from the primary location
            $locationBusinessHours = $primaryLocation->businessHours()->get();

            if ($locationBusinessHours->isNotEmpty()) {
                foreach ($locationBusinessHours as $hour) {
                    $businessHours[$hour->day_of_week] = [
                        'open_time' => $hour->open_time,
                        'close_time' => $hour->close_time,
                        'is_closed' => $hour->is_closed
                    ];
                }
            }
        }

        // If no location business hours found, try company-wide business hours
        if (empty($businessHours)) {
            $companyBusinessHours = BusinessHour::where('company_id', $company->id)
                ->whereNull('location_id')
                ->get();

            if ($companyBusinessHours->isNotEmpty()) {
                foreach ($companyBusinessHours as $hour) {
                    $businessHours[$hour->day_of_week] = [
                        'open_time' => $hour->open_time,
                        'close_time' => $hour->close_time,
                        'is_closed' => $hour->is_closed
                    ];
                }
            }
        }

        // Default business hours if none are set
        if (empty($businessHours)) {
            // Default to 9am-5pm for weekdays, closed on weekends
            for ($i = 0; $i < 7; $i++) {
                $isClosed = ($i == 0 || $i == 6); // Sunday (0) and Saturday (6) are closed
                $businessHours[$i] = [
                    'open_time' => '09:00:00',
                    'close_time' => '17:00:00',
                    'is_closed' => $isClosed
                ];
            }
        }

        return view('admin.staff.availability', compact('staff', 'dateRange', 'startDate', 'endDate', 'businessHours'));
    }

    /**
     * Update staff availability settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvailability(Request $request)
{
    \Log::info('Update availability request:', $request->all());

    // Custom validation for start and end times that handles day boundaries
    $endTime = $request->work_end_time;
    $startTime = $request->work_start_time;

    // Basic validation rules
    $rules = [
        'staff_id' => 'required|exists:staff,id',
        'work_days' => 'nullable|array',
        'work_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        'work_start_time' => 'required|date_format:H:i',
        'work_end_time' => 'required|date_format:H:i',
    ];

    // Perform basic validation first
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Now handle the time comparison logic
    if ($startTime && $endTime) {
        // Parse the times
        list($startHour, $startMinute) = array_map('intval', explode(':', $startTime));
        list($endHour, $endMinute) = array_map('intval', explode(':', $endTime));

        // Convert to minutes for easier comparison
        $startMinutes = $startHour * 60 + $startMinute;
        $endMinutes = $endHour * 60 + $endMinute;

        // Special case: if end time is earlier than start time, assume it's the next day
        // This handles cases like start=22:00, end=01:00 (meaning 1 AM the next day)
        if ($endMinutes < $startMinutes && $endTime !== '00:00') {
            // End time is on the next day, which is valid
            // No additional validation needed
        }
        // Special case: midnight (00:00) is treated as end of day (24:00)
        else if ($endTime === '00:00') {
            // Midnight is always considered after any other time of day
            // No additional validation needed
        }
        // Normal case: both times are on the same day, end must be after start
        else if ($endMinutes <= $startMinutes) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'work_end_time' => ['The end time must be after the start time.']
                ]
            ], 422);
        }
    }

    // If we've reached here, validation passed
    $validated = $validator->validated();

    \Log::info('Validated data:', $validated);

    $staff = Staff::findOrFail($request->staff_id);
    \Log::info('Found staff:', ['staff_id' => $staff->id, 'user_id' => $staff->user_id]);

    // Check if user has permission to edit this staff member
    $user = auth()->user();
    $company = $user->primaryCompany();
    $staffUserIds = $company->users()->pluck('users.id');

    \Log::info('Company users:', $staffUserIds->toArray());

    if (!$staffUserIds->contains($staff->user_id)) {
        \Log::error('Permission denied: User does not have permission to edit this staff member', [
            'current_user_id' => $user->id,
            'staff_user_id' => $staff->user_id,
            'company_id' => $company->id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to edit this staff member.'
        ], 403);
    }

    // Update staff availability
    $staff->work_days = $request->work_days ?: [];
    $staff->work_start_time = $request->work_start_time . ':00';
    $staff->work_end_time = $request->work_end_time . ':00';

    \Log::info('Updating staff availability:', [
        'work_days' => $staff->work_days,
        'work_start_time' => $staff->work_start_time,
        'work_end_time' => $staff->work_end_time
    ]);

    $staff->save();

    \Log::info('Staff availability updated successfully');

    return response()->json([
        'success' => true,
        'message' => 'Staff availability updated successfully.',
        'work_days' => $staff->work_days,
        'work_start_time' => $staff->work_start_time,
        'work_end_time' => $staff->work_end_time
    ]);
}

    /**
     * Display the staff services assignment page.
     *
     * @return \Illuminate\View\View
     */
    public function services()
    {
        // Get the current admin's primary company
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You need to set up your company before managing staff services.');
        }

        // Get staff members belonging to the current company via user-company pivot
        $userIds = $company->users()->pluck('users.id');
        $staff = Staff::whereIn('user_id', $userIds)->with('user.roles')->active()->get();

        // Get all services for the company
        $services = Service::whereHas('companies', function($query) use ($company) {
            $query->where('companies.id', $company->id);
        })->with('categories')->get();

        // Group services by category for easier display
        $servicesByCategory = [];
        foreach ($services as $service) {
            $categories = $service->categories;
            if ($categories->isEmpty()) {
                if (!isset($servicesByCategory['Uncategorized'])) {
                    $servicesByCategory['Uncategorized'] = [];
                }
                $servicesByCategory['Uncategorized'][] = $service;
            } else {
                foreach ($categories as $category) {
                    if (!isset($servicesByCategory[$category->name])) {
                        $servicesByCategory[$category->name] = [];
                    }
                    $servicesByCategory[$category->name][] = $service;
                }
            }
        }

        // Get existing staff-service assignments
        $staffServiceAssignments = [];
        foreach ($staff as $staffMember) {
            $staffServiceAssignments[$staffMember->id] = $staffMember->services()->pluck('services.id')->toArray();
        }

        return view('admin.staff.services', compact('staff', 'services', 'servicesByCategory', 'staffServiceAssignments'));
    }

    /**
     * Update staff service assignments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateServices(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'price_overrides' => 'nullable|array',
            'duration_overrides' => 'nullable|array',
        ]);

        $staff = Staff::findOrFail($request->staff_id);

        // Check if user has permission to edit this staff member
        $user = auth()->user();
        $company = $user->primaryCompany();
        $staffUserIds = $company->users()->pluck('users.id');

        if (!$staffUserIds->contains($staff->user_id)) {
            return back()->with('error', 'You do not have permission to edit this staff member.');
        }

        DB::beginTransaction();

        try {
            // Clear existing service assignments for this staff member
            $staff->services()->detach();

            // Add new service assignments
            if ($request->has('service_ids') && is_array($request->service_ids)) {
                $serviceData = [];

                foreach ($request->service_ids as $serviceId) {
                    $pivotData = [
                        'is_primary' => true, // Default to primary provider
                    ];

                    // Add price override if provided
                    if (isset($request->price_overrides[$serviceId]) && $request->price_overrides[$serviceId] !== '') {
                        $pivotData['price_override'] = $request->price_overrides[$serviceId];
                    }

                    // Add duration override if provided
                    if (isset($request->duration_overrides[$serviceId]) && $request->duration_overrides[$serviceId] !== '') {
                        $pivotData['duration_override'] = $request->duration_overrides[$serviceId];
                    }

                    $serviceData[$serviceId] = $pivotData;
                }

                $staff->services()->attach($serviceData);
            }

            DB::commit();
            return back()->with('success', 'Staff service assignments updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update staff service assignments: ' . $e->getMessage());
        }
    }

    /**
     * Log staff activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logActivity(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'activity_type' => 'required|string|max:50',
            'details' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
        ]);

        try {
            // Get the staff member
            $staff = Staff::findOrFail($request->staff_id);

            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($staff)
                ->withProperties([
                    'activity_type' => $request->activity_type,
                    'details' => $request->details,
                    'url' => $request->url,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('Staff activity: ' . $request->details);

            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log staff activity: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to log activity',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
