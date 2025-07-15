<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Show the form for editing the company.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No company found for this user.');
        }

        return view('admin.company.edit', [
            'company' => $company,
            'title' => 'Edit Business Settings'
        ]);
    }

    /**
     * Update the company in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No company found for this user.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
        ]);

        $company->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip' => $validated['zip'],
            'phone' => $validated['phone'],
            'website' => $validated['website'] ?? null,
        ]);

        return redirect()->route('admin.company.edit')
            ->with('success', 'Business settings updated successfully.');
    }
}
