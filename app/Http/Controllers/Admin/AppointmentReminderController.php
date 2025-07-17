<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSetting;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentReminderController extends Controller
{
    /**
     * Display the appointment reminder settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        // Get all appointment settings for the company
        $settings = AppointmentSetting::where('company_id', $company->id)
            ->with('location')
            ->get();
        
        // Get locations for the company
        $locations = Location::where('company_id', $company->id)->get();
        
        return view('admin.appointments.reminders', [
            'settings' => $settings,
            'locations' => $locations,
            'company' => $company,
        ]);
    }

    /**
     * Update the appointment reminder settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:appointment_settings,id',
            'settings.*.send_customer_reminders' => 'boolean',
            'settings.*.reminder_hours_before' => 'required|integer|min:1',
            'settings.*.send_staff_notifications' => 'boolean',
        ]);
        
        foreach ($validated['settings'] as $settingData) {
            $setting = AppointmentSetting::findOrFail($settingData['id']);
            
            // Ensure the setting belongs to the user's company
            if ($setting->company_id !== $company->id) {
                continue;
            }
            
            // Update reminder settings
            $setting->update([
                'send_customer_reminders' => $settingData['send_customer_reminders'] ?? false,
                'reminder_hours_before' => $settingData['reminder_hours_before'],
                'send_staff_notifications' => $settingData['send_staff_notifications'] ?? false,
            ]);
        }
        
        return redirect()
            ->route('admin.appointments.reminders')
            ->with('success', 'Appointment reminder settings updated successfully.');
    }
}
