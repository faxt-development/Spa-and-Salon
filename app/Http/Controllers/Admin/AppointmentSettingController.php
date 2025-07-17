<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSetting;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentSettingController extends Controller
{
    /**
     * Display a listing of the appointment settings.
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        $settings = AppointmentSetting::where('company_id', $company->id)
            ->with('location')
            ->get();
        
        return view('admin.appointments.settings.index', [
            'settings' => $settings,
            'company' => $company,
        ]);
    }

    /**
     * Show the form for creating a new appointment setting.
     */
    public function create()
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        // Get locations that don't have settings yet
        $locationsWithSettings = AppointmentSetting::where('company_id', $company->id)
            ->pluck('location_id')
            ->filter();
            
        $availableLocations = Location::where('company_id', $company->id)
            ->whereNotIn('id', $locationsWithSettings)
            ->get();
            
        // Check if company-wide settings exist
        $hasCompanyWideSettings = AppointmentSetting::where('company_id', $company->id)
            ->whereNull('location_id')
            ->exists();
            
        return view('admin.appointments.settings.create', [
            'company' => $company,
            'availableLocations' => $availableLocations,
            'hasCompanyWideSettings' => $hasCompanyWideSettings,
        ]);
    }

    /**
     * Store a newly created appointment setting in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        $validated = $request->validate([
            'location_id' => [
                'nullable',
                Rule::unique('appointment_settings')->where(function ($query) use ($company) {
                    return $query->where('company_id', $company->id);
                }),
            ],
            'time_slot_interval' => 'required|integer|min:5|max:120',
            'booking_lead_time' => 'required|integer|min:0',
            'cancellation_notice' => 'required|integer|min:0',
            'enforce_cancellation_fee' => 'boolean',
            'cancellation_fee' => 'nullable|numeric|min:0',
            'default_padding_time' => 'required|integer|min:0',
            'allow_sequential_booking' => 'boolean',
            'allow_time_based_pricing' => 'boolean',
            'auto_confirm_appointments' => 'boolean',
            'send_customer_reminders' => 'boolean',
            'reminder_hours_before' => 'required|integer|min:1',
            'send_staff_notifications' => 'boolean',
            'max_future_booking_days' => 'required|integer|min:1',
            'require_customer_login' => 'boolean',
            'allow_customer_reschedule' => 'boolean',
            'reschedule_notice_hours' => 'required|integer|min:0',
            'enable_waitlist' => 'boolean',
            'prevent_double_booking' => 'boolean',
            'track_no_shows' => 'boolean',
            'max_no_shows_before_warning' => 'required|integer|min:1',
        ]);
        
        // Set default values for checkboxes if not present
        $checkboxFields = [
            'enforce_cancellation_fee',
            'allow_sequential_booking',
            'allow_time_based_pricing',
            'auto_confirm_appointments',
            'send_customer_reminders',
            'send_staff_notifications',
            'require_customer_login',
            'allow_customer_reschedule',
            'enable_waitlist',
            'prevent_double_booking',
            'track_no_shows',
        ];
        
        foreach ($checkboxFields as $field) {
            if (!isset($validated[$field])) {
                $validated[$field] = false;
            }
        }
        
        // Add company ID
        $validated['company_id'] = $company->id;
        
        $setting = AppointmentSetting::create($validated);
        
        return redirect()
            ->route('admin.appointments.settings')
            ->with('success', 'Appointment settings created successfully.');
    }

    /**
     * Show the form for editing the specified appointment setting.
     */
    public function edit(AppointmentSetting $appointmentSetting)
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        // Ensure the setting belongs to the user's company
        if ($appointmentSetting->company_id !== $company->id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('admin.appointments.settings.edit', [
            'setting' => $appointmentSetting,
            'company' => $company,
        ]);
    }

    /**
     * Update the specified appointment setting in storage.
     */
    public function update(Request $request, AppointmentSetting $appointmentSetting)
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        // Ensure the setting belongs to the user's company
        if ($appointmentSetting->company_id !== $company->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'time_slot_interval' => 'required|integer|min:5|max:120',
            'booking_lead_time' => 'required|integer|min:0',
            'cancellation_notice' => 'required|integer|min:0',
            'enforce_cancellation_fee' => 'boolean',
            'cancellation_fee' => 'nullable|numeric|min:0',
            'default_padding_time' => 'required|integer|min:0',
            'allow_sequential_booking' => 'boolean',
            'allow_time_based_pricing' => 'boolean',
            'auto_confirm_appointments' => 'boolean',
            'send_customer_reminders' => 'boolean',
            'reminder_hours_before' => 'required|integer|min:1',
            'send_staff_notifications' => 'boolean',
            'max_future_booking_days' => 'required|integer|min:1',
            'require_customer_login' => 'boolean',
            'allow_customer_reschedule' => 'boolean',
            'reschedule_notice_hours' => 'required|integer|min:0',
            'enable_waitlist' => 'boolean',
            'prevent_double_booking' => 'boolean',
            'track_no_shows' => 'boolean',
            'max_no_shows_before_warning' => 'required|integer|min:1',
        ]);
        
        // Set default values for checkboxes if not present
        $checkboxFields = [
            'enforce_cancellation_fee',
            'allow_sequential_booking',
            'allow_time_based_pricing',
            'auto_confirm_appointments',
            'send_customer_reminders',
            'send_staff_notifications',
            'require_customer_login',
            'allow_customer_reschedule',
            'enable_waitlist',
            'prevent_double_booking',
            'track_no_shows',
        ];
        
        foreach ($checkboxFields as $field) {
            if (!isset($validated[$field])) {
                $validated[$field] = false;
            }
        }
        
        $appointmentSetting->update($validated);
        
        return redirect()
            ->route('admin.appointments.settings')
            ->with('success', 'Appointment settings updated successfully.');
    }

    /**
     * Remove the specified appointment setting from storage.
     */
    public function destroy(AppointmentSetting $appointmentSetting)
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        // Ensure the setting belongs to the user's company
        if ($appointmentSetting->company_id !== $company->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $appointmentSetting->delete();
        
        return redirect()
            ->route('admin.appointments.settings')
            ->with('success', 'Appointment settings deleted successfully.');
    }
}
