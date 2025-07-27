<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Service;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Find available time slots for a given date, duration, staff, and location.
     *
     * @param  \Carbon\Carbon  $date
     * @param  int  $duration
     * @param  int|null  $staffId
     * @param  \Illuminate\Database\Eloquent\Collection  $services
     * @param  int|null  $locationId
     * @return array
     */
    public function findAvailableTimeSlots(Carbon $date, int $duration, $staffId = null, $services = null, $locationId = null)
    {
        // Business hours
        $startHour = 9; // 9 AM
        $endHour = 17; // 5 PM
        
        $availableSlots = [];
        
        // If a specific staff member is requested
        if ($staffId) {
            $staff = Staff::find($staffId);
            
            // Check if staff exists and is active
            if (!$staff || !$staff->is_active) {
                return $availableSlots;
            }
            
            // Get staff working hours if available
            if ($staff->work_days && $staff->work_start_time && $staff->work_end_time) {
                $workDays = json_decode($staff->work_days, true);
                $dayOfWeek = strtolower($date->format('l'));
                
                // Check if staff works on this day
                if (!in_array($dayOfWeek, $workDays)) {
                    return $availableSlots;
                }
                
                // Use staff's working hours
                $startHour = Carbon::parse($staff->work_start_time)->hour;
                $endHour = Carbon::parse($staff->work_end_time)->hour;
            }
            
            // Generate time slots at 30-minute intervals
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 30) {
                    if ($hour == $endHour - 1 && $minute + ($duration / 60) > 60) {
                        continue; // Skip if appointment would go past end of day
                    }
                    
                    $slotStart = $date->copy()->setTime($hour, $minute);
                    $slotEnd = $slotStart->copy()->addMinutes($duration);
                    
                    // Check if this time slot is available for this staff member
                    $isAvailable = $this->checkStaffAvailability($staffId, $slotStart, $slotEnd);
                    
                    if ($isAvailable) {
                        $availableSlots[] = [
                            'start_time' => $slotStart->format('Y-m-d H:i:s'),
                            'end_time' => $slotEnd->format('Y-m-d H:i:s'),
                            'staff_id' => $staffId,
                            'staff_name' => $staff->name,
                        ];
                    }
                }
            }
        } else {
            // No specific staff requested, find all available staff
            $staffQuery = Staff::where('is_active', true);
            
            // Filter by location if specified
            if ($locationId) {
                $staffQuery->whereHas('locations', function ($query) use ($locationId) {
                    $query->where('locations.id', $locationId);
                });
            }
            
            $staffMembers = $staffQuery->get();
            
            // If services are specified, filter staff by those who can perform all services
            if ($services && $services->count() > 0) {
                $serviceIds = $services->pluck('id')->toArray();
                $staffMembers = $staffMembers->filter(function ($staff) use ($serviceIds) {
                    $staffServiceIds = $staff->services->pluck('id')->toArray();
                    return count(array_intersect($serviceIds, $staffServiceIds)) == count($serviceIds);
                });
            }
            
            foreach ($staffMembers as $staff) {
                // Get staff working hours if available
                $staffStartHour = $startHour;
                $staffEndHour = $endHour;
                
                if ($staff->work_days && $staff->work_start_time && $staff->work_end_time) {
                    $workDays = json_decode($staff->work_days, true);
                    $dayOfWeek = strtolower($date->format('l'));
                    
                    // Check if staff works on this day
                    if (!in_array($dayOfWeek, $workDays)) {
                        continue;
                    }
                    
                    // Use staff's working hours
                    $staffStartHour = Carbon::parse($staff->work_start_time)->hour;
                    $staffEndHour = Carbon::parse($staff->work_end_time)->hour;
                }
                
                // Generate time slots at 30-minute intervals
                for ($hour = $staffStartHour; $hour < $staffEndHour; $hour++) {
                    for ($minute = 0; $minute < 60; $minute += 30) {
                        if ($hour == $staffEndHour - 1 && $minute + ($duration / 60) > 60) {
                            continue; // Skip if appointment would go past end of day
                        }
                        
                        $slotStart = $date->copy()->setTime($hour, $minute);
                        $slotEnd = $slotStart->copy()->addMinutes($duration);
                        
                        // Check if this time slot is available for this staff member
                        $isAvailable = $this->checkStaffAvailability($staff->id, $slotStart, $slotEnd);
                        
                        if ($isAvailable) {
                            $availableSlots[] = [
                                'start_time' => $slotStart->format('Y-m-d H:i:s'),
                                'end_time' => $slotEnd->format('Y-m-d H:i:s'),
                                'staff_id' => $staff->id,
                                'staff_name' => $staff->name,
                            ];
                        }
                    }
                }
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
    public function checkStaffAvailability($staffId, $startTime, $endTime)
    {
        // Check if there are any overlapping appointments
        $overlappingAppointments = Appointment::where('staff_id', $staffId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->count();
        
        return $overlappingAppointments === 0;
    }

    /**
     * Create a new appointment.
     *
     * @param  array  $data
     * @return \App\Models\Appointment|null
     */
    public function createAppointment(array $data)
    {
        Log::info('BookingService@createAppointment: Creating appointment', [
            'data' => $data
        ]);

        try {
            DB::beginTransaction();

            // Get the selected services
            $services = Service::whereIn('id', $data['service_ids'])->get();
            
            // Calculate total duration and price
            $totalDuration = $services->sum('duration'); // in minutes
            $totalPrice = $services->sum('price');
            
            // Calculate end time based on start time and total duration
            $startTime = Carbon::parse($data['start_time']);
            $endTime = $startTime->copy()->addMinutes($totalDuration);
            
            // Check if the staff is available during this time slot
            $isAvailable = $this->checkStaffAvailability(
                $data['staff_id'],
                $startTime,
                $endTime
            );

            if (!$isAvailable) {
                Log::warning('BookingService@createAppointment: Time slot no longer available', [
                    'staff_id' => $data['staff_id'],
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]);
                DB::rollBack();
                return null;
            }

            // Create the appointment
            $appointment = Appointment::create([
                'client_id' => $data['client_id'],
                'staff_id' => $data['staff_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? null,
                'total_price' => $totalPrice,
            ]);
            
            // Attach services to the appointment
            foreach ($services as $service) {
                $appointment->services()->attach($service->id, [
                    'price' => $service->price,
                    'duration' => $service->duration
                ]);
            }
            
            DB::commit();
        
            Log::info('BookingService@createAppointment: Appointment created successfully', [
                'appointment_id' => $appointment->id
            ]);
        
            // Dispatch the AppointmentCreated event to trigger confirmation email
            event(new \App\Events\AppointmentCreated($appointment));
        
            return $appointment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BookingService@createAppointment: Error creating appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Create a new guest client.
     *
     * @param  array  $data
     * @return \App\Models\Client|null
     */
    public function createGuestClient(array $data)
    {
        Log::info('BookingService@createGuestClient: Creating guest client', [
            'data' => array_diff_key($data, array_flip(['password']))
        ]);

        try {
            $client = Client::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_guest' => true,
                'marketing_consent' => $data['marketing_consent'] ?? false,
            ]);
            
            Log::info('BookingService@createGuestClient: Guest client created successfully', [
                'client_id' => $client->id
            ]);
            
            return $client;
        } catch (\Exception $e) {
            Log::error('BookingService@createGuestClient: Error creating guest client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
