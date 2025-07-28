<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Appointment;
use App\Models\Location;
use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestBookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display the guest booking page with zip code search form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('guest.booking', [
            'pageTitle' => 'Find a Spa or Salon Near You',
        ]);
    }

    /**
     * Search for locations by zip code
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchByZip(Request $request)
    {
        $request->validate([
            'zip_code' => 'required|string|min:5|max:10',
        ]);

        $zipCode = $request->input('zip_code');

        // Log the search request
        Log::info('Guest location search by zip code', [
            'zip_code' => $zipCode,
            'ip' => $request->ip(),
        ]);

        // Find locations near the provided zip code
        // In a real implementation, this would use a distance calculation
        // For now, we'll just search for exact or similar zip codes
        $locations = Location::where('is_active', true)
            ->where(function ($query) use ($zipCode) {
                // Exact match
                $query->where('postal_code', $zipCode)
                    // Or first 3 digits match (same general area)
                    ->orWhere('postal_code', 'like', substr($zipCode, 0, 3) . '%');
            })
            ->with('company')
            ->orderBy('name')
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address_line_1 . ($location->address_line_2 ? ', ' . $location->address_line_2 : ''),
                    'city' => $location->city,
                    'state' => $location->state,
                    'postal_code' => $location->postal_code,
                    'company_name' => $location->company ? $location->company->name : 'Independent',
                    'company_id' => $location->company_id,
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations,
            'count' => $locations->count(),
        ]);
    }

    /**
     * Display the booking form for a specific location
     *
     * @param  int  $locationId
     * @return \Illuminate\View\View
     */
    public function bookingForm($locationId)
    {
        $location = Location::where('is_active', true)
            ->with('company')
            ->findOrFail($locationId);

        // Get active services for this location's company
        $services = Service::where('active', true)
            ->whereHas('companies', function ($query) use ($location) {
                $query->where('companies.id', $location->company_id);
            })
            ->orderBy('name')
            ->get();

        return view('guest.booking-form', [
            'location' => $location,
            'services' => $services,
            'pageTitle' => 'Book an Appointment at ' . $location->name,
        ]);
    }
}
