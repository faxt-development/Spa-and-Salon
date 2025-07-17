<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSetting;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentPolicyController extends Controller
{
    /**
     * Display the appointment cancellation policies page.
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

        return view('admin.appointments.policies', [
            'settings' => $settings,
            'locations' => $locations,
            'company' => $company,
        ]);
    }

    /**
     * Update the appointment cancellation policies.
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
            'settings.*.cancellation_notice' => 'required|integer|min:1',
            'settings.*.enforce_cancellation_fee' => 'boolean',
            'settings.*.cancellation_fee' => 'required_if:settings.*.enforce_cancellation_fee,1|nullable|numeric|min:0',
        ]);

        foreach ($validated['settings'] as $settingData) {
            $setting = AppointmentSetting::findOrFail($settingData['id']);

            // Ensure the setting belongs to the user's company
            if ($setting->company_id !== $company->id) {
                continue;
            }

            // Update cancellation policy settings
            $setting->update([
                'cancellation_notice' => $settingData['cancellation_notice'],
                'enforce_cancellation_fee' => $settingData['enforce_cancellation_fee'] ?? false,
                'cancellation_fee' => $settingData['enforce_cancellation_fee'] ? $settingData['cancellation_fee'] : null,
            ]);
        }

        return redirect()
            ->route('admin.appointments.policies')
            ->with('success', 'Cancellation policies updated successfully.');
    }
}
