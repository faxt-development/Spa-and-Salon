# Staff Availability Management

This document provides technical documentation for the Staff Availability Management feature in the Faxtina Spa and Salon application.

## Overview

The Staff Availability Management feature allows administrators to view and set staff working days and hours. This is crucial for proper scheduling and appointment management, as it determines when staff members are available to take appointments.

## Database Structure

The feature leverages existing fields in the `Staff` model:

- `work_days`: An array of days of the week when the staff member works (e.g., ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
- `work_start_time`: The time when the staff member starts working each day
- `work_end_time`: The time when the staff member finishes working each day

## Routes

The following routes are defined in `routes/web.php` within the admin route group:

```php
// View staff availability page
Route::get('/staff/availability', [App\Http\Controllers\StaffController::class, 'availability'])
    ->name('staff.availability');

// Update staff availability
Route::post('/staff/availability/update', [App\Http\Controllers\StaffController::class, 'updateAvailability'])
    ->name('staff.update-availability');
```

These routes are prefixed with `admin.` due to the admin route group, resulting in the route names `admin.staff.availability` and `admin.staff.update-availability`.

## Controller Methods

### StaffController@availability

This method displays the staff availability management page:

```php
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

    // Get the next 7 days for the availability calendar
    $startDate = now()->startOfDay();
    $endDate = now()->addDays(6)->endOfDay();
    $dateRange = [];
    
    for ($i = 0; $i < 7; $i++) {
        $date = $startDate->copy()->addDays($i);
        $dateRange[] = [
            'date' => $date->format('Y-m-d'),
            'day' => $date->format('D'),
            'full_day' => $date->format('l')
        ];
    }

    return view('admin.staff.availability', compact('staff', 'dateRange', 'startDate', 'endDate'));
}
```

### StaffController@updateAvailability

This method handles updating staff availability settings:

```php
/**
 * Update staff availability settings.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function updateAvailability(Request $request)
{
    $request->validate([
        'staff_id' => 'required|exists:staff,id',
        'work_days' => 'nullable|array',
        'work_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        'work_start_time' => 'required|date_format:H:i',
        'work_end_time' => 'required|date_format:H:i|after:work_start_time',
    ]);

    $staff = Staff::findOrFail($request->staff_id);

    // Check if user has permission to edit this staff member
    $user = auth()->user();
    $company = $user->primaryCompany();
    $staffUserIds = $company->users()->pluck('users.id');
    
    if (!$staffUserIds->contains($staff->user_id)) {
        return back()->with('error', 'You do not have permission to edit this staff member.');
    }

    // Update staff availability
    $staff->work_days = $request->work_days ?: [];
    $staff->work_start_time = $request->work_start_time;
    $staff->work_end_time = $request->work_end_time;
    $staff->save();

    return back()->with('success', 'Staff availability updated successfully.');
}
```

## View

The feature includes a Blade view `resources/views/admin/staff/availability.blade.php` that provides:

1. A list of staff members to select from
2. A weekly schedule display showing availability for the selected staff member
3. A form to edit working days and hours

The view uses JavaScript to:
- Handle staff selection
- Display the staff member's current availability settings
- Update the availability grid when a staff member is selected
- Handle form submission for updating availability

## Related Model Methods

The Staff model includes several methods related to availability:

### getScheduleForDate

Returns the staff member's work schedule for a given date:

```php
/**
 * Get the staff member's work schedule for a given date.
 *
 * @param Carbon|null $date
 * @return array
 */
public function getScheduleForDate(?Carbon $date = null): array
{
    $date = $date ?: now();
    $dayName = strtolower($date->format('l'));

    $workDays = $this->work_days ?: $this->defaultWorkDays;
    $isWorkDay = in_array($dayName, $workDays);

    return [
        'is_working' => $isWorkDay,
        'start_time' => $isWorkDay ? $this->work_start_time : null,
        'end_time' => $isWorkDay ? $this->work_end_time : null,
        'day_name' => $dayName,
    ];
}
```

### isAvailable

Checks if the staff member is available for an appointment at a given time:

```php
/**
 * Check if the staff member is available for an appointment at a given time.
 *
 * @param Carbon $startTime
 * @param Carbon $endTime
 * @param int|null $excludeAppointmentId
 * @return bool
 */
public function isAvailable(Carbon $startTime, Carbon $endTime, ?int $excludeAppointmentId = null): bool
{
    // Implementation details omitted for brevity
    // This method checks if the staff member is working at the given time
    // and if they don't have any overlapping appointments
}
```

### getAvailability

Gets the staff member's availability for a date range:

```php
/**
 * Get the staff member's availability for a date range.
 *
 * @param Carbon $startDate
 * @param Carbon $endDate
 * @param int $intervalMinutes
 * @return array
 */
public function getAvailability(Carbon $startDate, Carbon $endDate, int $intervalMinutes = 30): array
{
    // Implementation details omitted for brevity
    // This method returns an array of available time slots for each day in the date range
}
```

## Integration with Other Features

The Staff Availability Management feature integrates with:

1. **Appointment Scheduling**: The availability settings determine when appointments can be booked with a staff member.
2. **Admin Onboarding Checklist**: The feature is linked from the admin onboarding checklist to guide new administrators.

## Security

The feature includes security checks to ensure:

1. Only authenticated users with the admin role can access the feature
2. Admins can only modify staff members belonging to their company

## Future Enhancements

Potential future enhancements for this feature could include:

1. Support for custom breaks during the day
2. Support for different working hours on different days
3. Support for recurring time off (e.g., lunch breaks)
4. Calendar integration for importing availability from external calendars
5. Bulk editing of availability for multiple staff members
