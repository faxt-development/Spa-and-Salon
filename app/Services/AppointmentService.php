<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Create a new appointment
     *
     * @param array $data
     * @return array
     */
    public function createAppointment(array $data)
    {
        DB::beginTransaction();

        try {
            // Find or create client if client_id is not provided
            if (empty($data['client_id'])) {
                $client = $this->findOrCreateClient($data);
                $data['client_id'] = $client->id;
            }

            // Parse start time
            $startTime = Carbon::parse($data['date'] . ' ' . $data['start_time']);
            $data['start_time'] = $startTime;

            // Calculate end time if not provided
            if (empty($data['end_time'])) {
                // Get service durations
                $serviceIds = $data['service_ids'] ?? [];
                $totalDuration = (int) Service::whereIn('id', $serviceIds)->sum('duration');
                
                // Default to 30 minutes if no duration from services
                if ($totalDuration <= 0) {
                    $totalDuration = 30;
                    \Log::warning('No valid service duration found, using default 30 minutes', [
                        'service_ids' => $serviceIds,
                        'start_time' => $startTime->toDateTimeString(),
                    ]);
                }
                
                $data['end_time'] = (clone $startTime)->addMinutes($totalDuration);
            } else {
                // If end_time is provided, parse it
                $data['end_time'] = Carbon::parse($data['date'] . ' ' . $data['end_time']);
            }

            // Calculate total price based on selected services
            $serviceIds = $data['service_ids'] ?? array_column($data['services'] ?? [], 'id');
            $services = Service::whereIn('id', $serviceIds)->get();
            $totalPrice = $services->sum('price');

            // Create the appointment
            $appointment = Appointment::create([
                'client_id' => $data['client_id'],
                'staff_id' => $data['staff_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => $data['status'] ?? 'scheduled',
                'notes' => $data['notes'] ?? null,
                'total_price' => $totalPrice,
                'is_paid' => $data['is_paid'] ?? false,
            ]);

            // Attach services to the appointment
            foreach ($services as $service) {
                $appointment->services()->attach($service->id, [
                    'price' => $service->price,
                    'duration' => $service->duration
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'appointment' => $appointment->load(['client', 'staff', 'services'])
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Appointment creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create appointment: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Check if staff is available during the requested time slot
     * 
     * @param int $staffId
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeAppointmentId
     * @return bool
     */
    public function checkStaffAvailability($staffId, $startTime, $endTime, $excludeAppointmentId = null)
    {
        $query = Appointment::where('staff_id', $staffId)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q) use ($startTime, $endTime) {
                      $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return !$query->exists();
    }

    /**
     * Find or create a client
     * 
     * @param array $data
     * @return \App\Models\Client
     */
    protected function findOrCreateClient(array $data)
    {
        // If we have client details, use them to find or create
        if (!empty($data['client_email'])) {
            $nameParts = explode(' ', trim($data['client_name'] ?? 'Client'), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            $clientData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['client_email'],
                'phone' => $data['client_phone'] ?? null,
            ];

            return Client::firstOrCreate(
                ['email' => $data['client_email']],
                $clientData
            );
        }

        throw new \InvalidArgumentException('Client information is required');
    }
}
