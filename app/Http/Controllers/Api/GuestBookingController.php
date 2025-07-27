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

        $date = Carbon::parse($request->date)->startOfDay();
        $service = Service::findOrFail($request->service_id);

        // Get staff members who can perform this service and are scheduled to work on this day
        $dayOfWeek = strtolower($date->englishDayOfWeek);

        $staffMembers = $service->staff()
            ->where('active', true)
            ->whereJsonContains('work_days', $dayOfWeek)
            ->whereNotNull('work_start_time')
            ->whereNotNull('work_end_time')
            ->get();

        // Get existing appointments for each staff member on this day
        $startOfDay = $date->copy();
        $endOfDay = $date->copy()->endOfDay();

        $availableSlots = [];

        foreach ($staffMembers as $staff) {
            // Get work schedule for this staff member
            $startTime = Carbon::parse($staff->work_start_time);
            $endTime = Carbon::parse($staff->work_end_time);

            // Get all appointments for this staff member on this day
            $bookedAppointments = $staff->appointments()
                ->whereBetween('start_time', [$startOfDay, $endOfDay])
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->orderBy('start_time')
                ->get(['start_time', 'end_time']);
            $interval = 15; // 15-minute intervals

            $slots = [];
            $currentSlot = $startTime->copy();

            while ($currentSlot->copy()->addMinutes($service->duration) <= $endTime) {
                $slotEnd = $currentSlot->copy()->addMinutes($service->duration);
                $isAvailable = true;

                // Check if this slot is already booked
                foreach ($bookedAppointments as $appointment) {
                    $apptStart = Carbon::parse($appointment->start_time);
                    $apptEnd = Carbon::parse($appointment->end_time);

                    if ($currentSlot < $apptEnd && $slotEnd > $apptStart) {
                        $isAvailable = false;
                        break;
                    }
                }

                $slots[] = [
                    'start_time' => $currentSlot->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'is_available' => $isAvailable,
                    'formatted_time' => $currentSlot->format('g:i A') . ' - ' . $slotEnd->format('g:i A')
                ];

                $currentSlot->addMinutes($interval);
            }

            $availableSlots[] = [
                'staff_id' => $staff->id,
                'staff_name' => $staff->full_name,
                'staff_photo' => $staff->photo_url,
                'slots' => $slots,
                'work_hours' => [
                    'start' => $staff->work_start_time,
                    'end' => $staff->work_end_time
                ]
            ];
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

            // Create the appointment
            $appointment = $this->bookingService->createAppointment([
                'client_id' => $client->id,
                'staff_id' => $request->staff_id,
                'service_ids' => [$request->service_id], // Convert single service to array
                'start_time' => $startTime,
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
