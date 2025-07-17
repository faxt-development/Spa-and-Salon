<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $locations = Location::orderBy('is_primary', 'desc')
            ->orderBy('name')
            ->paginate(10);
        
        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $timezones = $this->getTimezoneOptions();
        $currencies = $this->getCurrencyOptions();
        
        return view('admin.locations.create', compact('timezones', 'currencies'));
    }

    /**
     * Store a newly created location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $this->validateLocation($request);
        
        // Generate a unique code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateUniqueCode($validated['name']);
        }
        
        // Handle primary location logic
        if (!empty($validated['is_primary'])) {
            // Remove primary status from all other locations
            Location::where('is_primary', true)->update(['is_primary' => false]);
        }
        
        // Create the location
        $location = Location::create($validated);
        
        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified location.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\View\View
     */
    public function show(Location $location)
    {
        return view('admin.locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified location.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\View\View
     */
    public function edit(Location $location)
    {
        $timezones = $this->getTimezoneOptions();
        $currencies = $this->getCurrencyOptions();
        
        return view('admin.locations.edit', compact('location', 'timezones', 'currencies'));
    }

    /**
     * Update the specified location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Location $location)
    {
        $validated = $this->validateLocation($request, $location->id);
        
        // Handle primary location logic
        if (!empty($validated['is_primary']) && !$location->is_primary) {
            // Remove primary status from all other locations
            Location::where('is_primary', true)->update(['is_primary' => false]);
        }
        
        // Update the location
        $location->update($validated);
        
        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified location from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Location $location)
    {
        // Prevent deletion of primary location
        if ($location->is_primary) {
            return redirect()->route('admin.locations.index')
                ->with('error', 'Cannot delete the primary location.');
        }
        
        // Check if location has related data
        if ($location->staff()->count() > 0 || $location->appointments()->count() > 0) {
            return redirect()->route('admin.locations.index')
                ->with('error', 'Cannot delete a location with associated staff or appointments.');
        }
        
        $location->delete();
        
        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }

    /**
     * Validate the location request data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $locationId
     * @return array
     */
    private function validateLocation(Request $request, $locationId = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('locations')->ignore($locationId),
            ],
            'description' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'timezone' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string',
        ]);
    }

    /**
     * Generate a unique location code based on the name.
     *
     * @param  string  $name
     * @return string
     */
    private function generateUniqueCode($name)
    {
        $baseCode = strtoupper(substr(Str::slug($name), 0, 8));
        $code = $baseCode;
        $counter = 1;
        
        // Make sure the code is unique
        while (Location::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Get timezone options for select field.
     *
     * @return array
     */
    private function getTimezoneOptions()
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        return array_combine($timezones, $timezones);
    }

    /**
     * Get currency options for select field.
     *
     * @return array
     */
    private function getCurrencyOptions()
    {
        return [
            'USD' => 'US Dollar (USD)',
            'CAD' => 'Canadian Dollar (CAD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'AUD' => 'Australian Dollar (AUD)',
            'JPY' => 'Japanese Yen (JPY)',
        ];
    }
}
