<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Client;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuestBookingController extends Controller
{
    protected $bookingService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\BookingService  $bookingService
     * @return void
     */
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Get list of services for guest booking.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function services()
    {
        $services = Service::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Check service availability for a specific date and location
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
            'location_id' => 'required|exists:locations,id',
        ]);

        Log::info('GuestBookingController@checkAvailability: Request received', [
            'request_data' => $request->all(),
            'session_id' => session()->getId(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        // Get the selected services
        $services = Service::whereIn('id', $request->service_ids)->get();
        Log::info('GuestBookingController@checkAvailability: Services retrieved', [
            'service_count' => $services->count(),
            'service_ids' => $services->pluck('id')->toArray(),
            'service_names' => $services->pluck('name')->toArray(),
        ]);
        
        // Calculate total duration needed for the appointment
        $totalDuration = $services->sum('duration'); // in minutes
        Log::info('GuestBookingController@checkAvailability: Total duration calculated', [
            'total_duration_minutes' => $totalDuration
        ]);
        
        // Get the date for availability check
        $date = Carbon::parse($request->date)->startOfDay();
        Log::info('GuestBookingController@checkAvailability: Date parsed', [
            'requested_date' => $request->date,
            'parsed_date' => $date->toDateString(),
        ]);
        
        // Find available staff members and time slots
        Log::info('GuestBookingController@checkAvailability: Finding available time slots', [
            'date' => $date->toDateString(),
            'duration' => $totalDuration,
            'staff_id' => $request->staff_id,
            'location_id' => $request->location_id,
        ]);
        $availableSlots = $this->bookingService->findAvailableTimeSlots($date, $totalDuration, $request->staff_id, $services, $request->location_id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
                'total_duration' => $totalDuration,
                'available_slots' => $availableSlots
            ]
        ]);
    }

    /**
     * Book an appointment as a guest.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function book(Request $request)
    {
        Log::info('GuestBookingController@book: Request received', [
            'request_data' => array_diff_key($request->all(), array_flip(['password'])),
            'session_id' => session()->getId(),
            'ip' => $request->ip(),
        ]);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'required|exists:staff,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'marketing_consent' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('GuestBookingController@book: Validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Create or find guest client
            $existingClient = Client::where('email', $request->email)->first();
            
            if ($existingClient) {
                $client = $existingClient;
                Log::info('GuestBookingController@book: Using existing client', [
                    'client_id' => $client->id
                ]);
            } else {
                $client = $this->bookingService->createGuestClient([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'marketing_consent' => $request->marketing_consent ?? false,
                ]);
                
                if (!$client) {
                    throw new \Exception('Failed to create guest client');
                }
            }

            // Create the appointment
            $appointment = $this->bookingService->createAppointment([
                'client_id' => $client->id,
                'staff_id' => $request->staff_id,
                'service_ids' => $request->service_ids,
                'start_time' => $request->start_time,
                'notes' => $request->notes,
            ]);

            if (!$appointment) {
                throw new \Exception('Failed to create appointment');
            }

            DB::commit();

            Log::info('GuestBookingController@book: Appointment booked successfully', [
                'appointment_id' => $appointment->id,
                'client_id' => $client->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'appointment_id' => $appointment->id,
                    'client_id' => $client->id,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'staff_name' => $appointment->staff->name,
                    'services' => $appointment->services->map(function($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                            'duration' => $service->duration,
                            'price' => $service->price
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GuestBookingController@book: Error booking appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while booking your appointment. Please try again.'
            ], 500);
        }
    }
}
