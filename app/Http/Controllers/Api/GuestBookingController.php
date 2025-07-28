<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Client;
use App\Models\Appointment;
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
        $services = Service::where('active', true)
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
        // Validate the request
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:services,id',
            'location_id' => 'required|exists:locations,id',
        ]);

        $location = \App\Models\Location::findOrFail($request->location_id);
        $timezone = $location->timezone; // e.g., 'America/New_York'

        $date = Carbon::parse($request->date, $timezone)->startOfDay();
        $service = Service::findOrFail($request->service_id);

        // Get staff members for this location who can perform this service and are scheduled to work
        $dayOfWeek = strtolower($date->englishDayOfWeek);
        $staffMembers = $service->staff()
            ->where('staff.location_id', $location->id)
            ->where('active', true)
            ->whereJsonContains('work_days', $dayOfWeek)
            ->whereNotNull('work_start_time')
            ->whereNotNull('work_end_time')
            ->get();

        $availableSlots = [];

        foreach ($staffMembers as $staff) {
            // Create start and end times in the location's timezone
            $workStart = Carbon::parse($date->toDateString() . ' ' . $staff->work_start_time, $timezone);
            $workEnd = Carbon::parse($date->toDateString() . ' ' . $staff->work_end_time, $timezone);

            // Get appointments for this staff in UTC to match database storage
            $startOfDayUTC = $workStart->copy()->utc();
            $endOfDayUTC = $workEnd->copy()->utc();
            $bookedAppointments = $staff->appointments()
                ->whereBetween('start_time', [$startOfDayUTC, $endOfDayUTC])
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->orderBy('start_time')
                ->get(['start_time', 'end_time']);

            $slots = [];
            $currentSlot = $workStart->copy();
            $interval = 15; // 15-minute intervals

            while ($currentSlot->copy()->addMinutes($service->duration) <= $workEnd) {
                $slotEnd = $currentSlot->copy()->addMinutes($service->duration);
                $isBooked = false;

                // Check for overlap with booked appointments. All comparisons in UTC.
                foreach ($bookedAppointments as $appointment) {
                    $apptStart = Carbon::parse($appointment->start_time)->setTimezone($timezone);
                    $apptEnd = Carbon::parse($appointment->end_time)->setTimezone($timezone);

                    if ($currentSlot < $apptEnd && $slotEnd > $apptStart) {
                        $isBooked = true;
                        // Fast-forward the current slot to the end of the booked appointment
                        $currentSlot = $apptEnd->copy();
                        break;
                    }
                }

                if ($isBooked) {
                    continue; // Skip to the next iteration after fast-forwarding
                }
                
                // If we are here, the slot is available
                $slots[] = [
                    'start_time' => $currentSlot->copy()->utc()->toDateTimeString(), // Send full UTC time for booking
                    'end_time' => $slotEnd->copy()->utc()->toDateTimeString(),
                    'is_available' => true,
                    'formatted_time' => $currentSlot->format('g:i A') . ' - ' . $slotEnd->format('g:i A')
                ];

                $currentSlot->addMinutes($interval);
            }

            if (!empty($slots)) {
                $availableSlots[] = [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->full_name,
                    'staff_photo' => $staff->photo_url,
                    'slots' => $slots,
                    'work_hours' => [
                        'start' => $workStart->format('g:i A'),
                        'end' => $workEnd->format('g:i A')
                    ]
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'duration' => $service->duration,
                    'price' => $service->price
                ],
                'availability' => $availableSlots
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

            // Check for duplicate appointments
            $startTime = Carbon::parse($request->date . ' ' . $request->time);
            $existingAppointment = $this->checkForDuplicateAppointment(
                $client->id,
                $request->service_id,
                $startTime
            );

            if ($existingAppointment) {
                Log::info('GuestBookingController@book: Duplicate appointment detected', [
                    'client_id' => $client->id,
                    'service_id' => $request->service_id,
                    'start_time' => $startTime,
                    'existing_appointment_id' => $existingAppointment->id
                ]);

                // Resend confirmation email
                event(new \App\Events\AppointmentCreated($existingAppointment));

                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => 'You already have an appointment for this service on ' .
                                 $existingAppointment->start_time->format('F j, Y \a\t g:i A') .
                                 '. A confirmation email has been resent to your email address.',
                    'appointment' => [
                        'id' => $existingAppointment->id,
                        'start_time' => $existingAppointment->start_time,
                        'end_time' => $existingAppointment->end_time,
                        'staff_name' => $existingAppointment->staff->name,
                        'services' => $existingAppointment->services->map(function($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                                'duration' => $service->duration,
                                'price' => $service->price
                            ];
                        })
                    ]
                ], 409);
            }

            // Create appointment
            $appointment = $this->bookingService->createAppointment([
                'client_id' => $client->id,
                'staff_id' => $request->staff_id,
                'service_ids' => [$request->service_id], // Convert single service to array
                'start_time' => $startTime,
                'notes' => $request->notes,
            ]);

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, the selected time slot is no longer available. Please choose another time.',
                    'error_type' => 'TIME_SLOT_UNAVAILABLE'
                ], 409); // Using 409 Conflict status
            }

            // Generate appointment token for guest access
            $appointmentToken = \App\Models\AppointmentToken::createForAppointment(
                $appointment->id,
                $request->email,
                30 // Token valid for 30 days
            );

            DB::commit();

            Log::info('GuestBookingController@book: Appointment booked successfully', [
                'appointment_id' => $appointment->id,
                'client_id' => $client->id,
                'token' => $appointmentToken->token
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
                    }),
                    'guest_token' => $appointmentToken->token,
                    'guest_link' => route('guest.appointment.view', ['token' => $appointmentToken->token])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), 'Time slot no longer available')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, the selected time slot is no longer available. Please choose another time.',
                    'error_type' => 'TIME_SLOT_UNAVAILABLE'
                ], 409); // 409 Conflict
            }

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

    /**
     * Check for duplicate appointments for a client.
     *
     * @param  int  $clientId
     * @param  int  $serviceId
     * @param  \Carbon\Carbon  $startTime
     * @return \App\Models\Appointment|null
     */
    private function checkForDuplicateAppointment($clientId, $serviceId, $startTime)
    {
        return Appointment::where('client_id', $clientId)
            ->whereHas('services', function ($query) use ($serviceId) {
                $query->where('services.id', $serviceId);
            })
            ->where('start_time', $startTime)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['staff', 'services'])
            ->first();
    }
}
