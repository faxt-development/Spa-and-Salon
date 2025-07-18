<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Display the support documentation page.
     *
     * @return \Illuminate\View\View
     */
    public function docs()
    {
        return view('admin.support.docs', [
            'pageTitle' => 'Support Documentation',
            'user' => Auth::user(),
        ]);
    }

    /**
     * Display the emergency contacts page.
     *
     * @return \Illuminate\View\View
     */
    public function contacts()
    {
        return view('admin.support.contacts', [
            'pageTitle' => 'Emergency Contacts',
            'user' => Auth::user(),
        ]);
    }

    /**
     * Display the backup procedures page.
     *
     * @return \Illuminate\View\View
     */
    public function backup()
    {
        return view('admin.support.backup', [
            'pageTitle' => 'Backup Procedures',
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update emergency contacts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateContacts(Request $request)
    {
        $validated = $request->validate([
            'primary_contact_name' => 'required|string|max:255',
            'primary_contact_phone' => 'required|string|max:20',
            'primary_contact_email' => 'required|email|max:255',
            'secondary_contact_name' => 'nullable|string|max:255',
            'secondary_contact_phone' => 'nullable|string|max:20',
            'secondary_contact_email' => 'nullable|email|max:255',
        ]);

        // Get the user's company
        $company = Auth::user()->primaryCompany();
        
        // Store the contacts in company settings
        $company->settings()->updateOrCreate(
            ['key' => 'emergency_contacts'],
            ['value' => json_encode($validated)]
        );

        return redirect()->route('admin.support.contacts')
            ->with('success', 'Emergency contacts updated successfully.');
    }
}
