<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
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
    
    /**
     * Show the form for editing location hours.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\View\View
     */
    public function hours(Location $location)
    {
        // Get business hours for this location
        $businessHours = BusinessHour::where('location_id', $location->id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');
            
        // Ensure we have entries for all 7 days of the week
        $daysOfWeek = [0, 1, 2, 3, 4, 5, 6]; // 0 = Sunday, 1 = Monday, etc.
        
        foreach ($daysOfWeek as $day) {
            if (!$businessHours->has($day)) {
                // Create default hours for this day
                $defaultHour = new BusinessHour([
                    'day_of_week' => $day,
                    'open_time' => '09:00:00',
                    'close_time' => '17:00:00',
                    'is_closed' => ($day === 0 || $day === 6), // Closed on weekends by default
                ]);
                
                $businessHours->put($day, $defaultHour);
            }
        }
        
        return view('admin.locations.hours', compact('location', 'businessHours'));
    }
    
    /**
     * Update the location hours.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHours(Request $request, Location $location)
    {
        $validated = $request->validate([
            'business_hours' => 'required|array',
            'business_hours.*.is_open' => 'boolean',
            'business_hours.*.slots' => 'array',
            'business_hours.*.slots.*.open' => 'required_with:business_hours.*.slots|string',
            'business_hours.*.slots.*.close' => 'required_with:business_hours.*.slots|string',
        ]);
        
        // Delete existing business hours for this location
        BusinessHour::where('location_id', $location->id)->delete();
        
        // Map day names to day_of_week values (0 = Sunday, 1 = Monday, etc.)
        $dayMapping = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];
        
        // Process and save new business hours
        foreach ($request->input('business_hours', []) as $dayName => $data) {
            // Skip if day name is not valid
            if (!isset($dayMapping[$dayName])) {
                continue;
            }
            
            $dayOfWeek = $dayMapping[$dayName];
            $isClosed = !isset($data['is_open']) || !$data['is_open'];
            
            if ($isClosed) {
                // Create a closed day entry
                BusinessHour::create([
                    'location_id' => $location->id,
                    'day_of_week' => $dayOfWeek,
                    'open_time' => '00:00:00',
                    'close_time' => '00:00:00',
                    'is_closed' => true,
                ]);
            } else {
                // Handle time slots
                if (isset($data['slots']) && is_array($data['slots'])) {
                    // For now, we'll just use the first slot as we're transitioning from JSON
                    // In the future, we could support multiple slots per day
                    $slot = $data['slots'][0] ?? null;
                    
                    if ($slot && isset($slot['open']) && isset($slot['close'])) {
                        BusinessHour::create([
                            'location_id' => $location->id,
                            'day_of_week' => $dayOfWeek,
                            'open_time' => $slot['open'] . ':00', // Add seconds for proper time format
                            'close_time' => $slot['close'] . ':00',
                            'is_closed' => false,
                        ]);
                    }
                }
            }
        }
        
        return redirect()->route('admin.locations.hours', $location)
            ->with('success', 'Business hours updated successfully.');
    }
}
