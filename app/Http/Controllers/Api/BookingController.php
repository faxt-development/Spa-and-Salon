<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Service;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Check availability for booking an appointment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the selected services
        $services = Service::whereIn('id', $request->service_ids)->get();
        
        // Calculate total duration needed for the appointment
        $totalDuration = $services->sum('duration'); // in minutes
        
        // Get the date for availability check
        $date = Carbon::parse($request->date)->startOfDay();
        
        // Find available staff members and time slots
        $availableSlots = $this->findAvailableTimeSlots($date, $totalDuration, $request->staff_id, $services);
        
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
     * Reserve a time slot for an appointment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'staff_id' => 'required|exists:staff,id',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'start_time' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the selected services
        $services = Service::whereIn('id', $request->service_ids)->get();
        
        // Calculate total duration and price
        $totalDuration = $services->sum('duration'); // in minutes
        $totalPrice = $services->sum('price');
        
        // Calculate end time based on start time and total duration
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($totalDuration);
        
        // Check if the staff is available during this time slot
        $isAvailable = $this->checkStaffAvailability(
            $request->staff_id,
            $startTime,
            $endTime
        );

        if (!$isAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'The selected time slot is no longer available.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Create the appointment
            $appointment = Appointment::create([
                'client_id' => $request->client_id,
                'staff_id' => $request->staff_id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'notes' => $request->notes,
                'total_price' => $totalPrice,
                'is_paid' => false,
            ]);

            // Attach services to the appointment
            foreach ($services as $service) {
                $appointment->services()->attach($service->id, [
                    'price' => $service->price,
                    'duration' => $service->duration
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Appointment reserved successfully',
                'data' => $appointment->load(['client', 'staff', 'services'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find available time slots for a given date, duration, and staff.
     *
     * @param  \Carbon\Carbon  $date
     * @param  int  $duration
     * @param  int|null  $staffId
     * @param  \Illuminate\Database\Eloquent\Collection  $services
     * @return array
     */
    private function findAvailableTimeSlots(Carbon $date, int $duration, $staffId = null, $services = null)
    {
        // Define business hours (e.g., 9 AM to 5 PM)
        $businessStartHour = 9;
        $businessEndHour = 17;
        
        // Time slot interval in minutes (e.g., 30 minutes)
        $slotInterval = 30;
        
        $availableSlots = [];
        
        // If a specific staff member is requested, only check their availability
        if ($staffId) {
            $staffMembers = Staff::where('id', $staffId)
                ->where('active', true)
                ->get();
        } else {
            // Otherwise, find all staff members who can perform the requested services
            $staffMembers = Staff::where('active', true);
            
            if ($services && $services->count() > 0) {
                $staffMembers = $staffMembers->whereHas('services', function ($query) use ($services) {
                    $query->whereIn('services.id', $services->pluck('id'));
                });
            }
            
            $staffMembers = $staffMembers->get();
        }
        
        foreach ($staffMembers as $staff) {
            // Check if staff works on this day
            $dayOfWeek = $date->dayOfWeek;
            $workDays = $staff->work_days ?? [];
            
            if (!in_array($dayOfWeek, $workDays)) {
                continue; // Staff doesn't work on this day
            }
            
            // Get staff's working hours for this day
            $workStartTime = Carbon::parse($staff->work_start_time)->setDateFrom($date);
            $workEndTime = Carbon::parse($staff->work_end_time)->setDateFrom($date);
            
            // Adjust to business hours if needed
            $dayStartTime = $date->copy()->setHour($businessStartHour)->setMinute(0)->setSecond(0);
            $dayEndTime = $date->copy()->setHour($businessEndHour)->setMinute(0)->setSecond(0);
            
            // Use the later of business start and staff start time
            $startTime = $workStartTime->gt($dayStartTime) ? $workStartTime : $dayStartTime;
            
            // Use the earlier of business end and staff end time
            $endTime = $workEndTime->lt($dayEndTime) ? $workEndTime : $dayEndTime;
            
            // Get existing appointments for this staff on this day
            $appointments = Appointment::where('staff_id', $staff->id)
                ->whereDate('start_time', $date)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->orderBy('start_time')
                ->get();
            
            // Generate time slots
            $currentSlot = $startTime->copy();
            
            while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
                $slotEndTime = $currentSlot->copy()->addMinutes($duration);
                $isAvailable = true;
                
                // Check if this slot conflicts with any existing appointment
                foreach ($appointments as $appointment) {
                    $appointmentStart = Carbon::parse($appointment->start_time);
                    $appointmentEnd = Carbon::parse($appointment->end_time);
                    
                    // Check for overlap
                    if (($currentSlot->gte($appointmentStart) && $currentSlot->lt($appointmentEnd)) ||
                        ($slotEndTime->gt($appointmentStart) && $slotEndTime->lte($appointmentEnd)) ||
                        ($currentSlot->lte($appointmentStart) && $slotEndTime->gte($appointmentEnd))) {
                        $isAvailable = false;
                        break;
                    }
                }
                
                if ($isAvailable) {
                    $availableSlots[] = [
                        'staff_id' => $staff->id,
                        'staff_name' => $staff->full_name,
                        'start_time' => $currentSlot->toDateTimeString(),
                        'end_time' => $slotEndTime->toDateTimeString(),
                    ];
                }
                
                // Move to the next slot
                $currentSlot->addMinutes($slotInterval);
            }
        }
        
        return $availableSlots;
    }

    /**
     * Check if a staff member is available during a specific time slot.
     *
     * @param  int  $staffId
     * @param  \Carbon\Carbon  $startTime
     * @param  \Carbon\Carbon  $endTime
     * @return bool
     */
    private function checkStaffAvailability($staffId, $startTime, $endTime)
    {
        // Check if the staff member exists and is active
        $staff = Staff::find($staffId);
        if (!$staff || !$staff->active) {
            return false;
        }
        
        // Check if the requested time is within the staff's working hours
        $dayOfWeek = $startTime->dayOfWeek;
        $workDays = $staff->work_days ?? [];
        
        if (!in_array($dayOfWeek, $workDays)) {
            return false; // Staff doesn't work on this day
        }
        
        $workStartTime = Carbon::parse($staff->work_start_time)->setDateFrom($startTime);
        $workEndTime = Carbon::parse($staff->work_end_time)->setDateFrom($startTime);
        
        if ($startTime->lt($workStartTime) || $endTime->gt($workEndTime)) {
            return false; // Outside of working hours
        }
        
        // Check for conflicting appointments
        $conflictingAppointments = Appointment::where('staff_id', $staffId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();
        
        return $conflictingAppointments === 0;
    }
}
