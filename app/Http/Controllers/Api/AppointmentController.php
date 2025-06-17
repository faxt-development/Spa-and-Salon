<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Get upcoming appointments for the authenticated client
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clientAppointments(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        
        // Get the client associated with the user
        $client = Client::where('user_id', $user->id)->first();
        
        if (!$client) {
            return response()->json([
                'message' => 'Client not found for this user',
                'appointments' => []
            ], 200);
        }
        
        // Get future appointments for the client
        $appointments = Appointment::with(['staff', 'services'])
            ->where('client_id', $client->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'service_name' => $appointment->services->first()?->name ?? 'Service',
                    'appointment_date' => $appointment->start_time->format('Y-m-d'),
                    'appointment_time' => $appointment->start_time->format('H:i'),
                    'staff_name' => $appointment->staff ? $appointment->staff->first_name . ' ' . $appointment->staff->last_name : 'Staff',
                    'notes' => $appointment->notes,
                ];
            });
        
        return response()->json([
            'appointments' => $appointments
        ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['client', 'staff', 'services']);
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_time', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }
        
        // Filter by staff if provided
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        
        // Filter by client if provided
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $appointments = $query->orderBy('start_time')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'staff_id' => 'required|exists:staff,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'services' => 'required|array',
            'services.*.id' => 'required|exists:services,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if staff is available during the requested time slot
        $isAvailable = $this->checkStaffAvailability(
            $request->staff_id,
            $request->start_time,
            $request->end_time
        );

        if (!$isAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'The selected staff member is not available during this time slot.'
            ], 422);
        }

        // Calculate total price based on selected services
        $services = Service::whereIn('id', array_column($request->services, 'id'))->get();
        $totalPrice = $services->sum('price');

        DB::beginTransaction();

        try {
            // Create the appointment
            $appointment = Appointment::create([
                'client_id' => $request->client_id,
                'staff_id' => $request->staff_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
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
                'message' => 'Appointment created successfully',
                'data' => $appointment->load(['client', 'staff', 'services'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['client', 'staff', 'services', 'products', 'payments'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|required|exists:clients,id',
            'staff_id' => 'sometimes|required|exists:staff,id',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'status' => 'sometimes|required|in:scheduled,confirmed,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'services' => 'sometimes|required|array',
            'services.*.id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If changing time or staff, check availability
        if (($request->has('start_time') && $request->has('end_time')) || $request->has('staff_id')) {
            $staffId = $request->staff_id ?? $appointment->staff_id;
            $startTime = $request->start_time ?? $appointment->start_time;
            $endTime = $request->end_time ?? $appointment->end_time;
            
            $isAvailable = $this->checkStaffAvailability(
                $staffId,
                $startTime,
                $endTime,
                $appointment->id
            );

            if (!$isAvailable) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected staff member is not available during this time slot.'
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            // Update appointment details
            $appointment->update($request->only([
                'client_id', 'staff_id', 'start_time', 'end_time',
                'status', 'notes', 'is_paid'
            ]));

            // Update services if provided
            if ($request->has('services')) {
                $services = Service::whereIn('id', array_column($request->services, 'id'))->get();
                
                // Detach existing services
                $appointment->services()->detach();
                
                // Attach new services
                foreach ($services as $service) {
                    $appointment->services()->attach($service->id, [
                        'price' => $service->price,
                        'duration' => $service->duration
                    ]);
                }
                
                // Recalculate total price
                $appointment->total_price = $services->sum('price');
                $appointment->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully',
                'data' => $appointment->load(['client', 'staff', 'services'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Check if appointment can be deleted (e.g., not completed)
        if ($appointment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed appointments cannot be deleted.'
            ], 422);
        }
        
        $appointment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully'
        ]);
    }
    
    /**
     * Get appointments for calendar view by month.
     *
     * @param  int  $year
     * @param  int  $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function calendar(int $year, int $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $appointments = Appointment::with(['client', 'staff', 'services'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->client->full_name,
                    'start' => $appointment->start_time->toDateTimeString(),
                    'end' => $appointment->end_time->toDateTimeString(),
                    'status' => $appointment->status,
                    'staff' => $appointment->staff->full_name,
                    'services' => $appointment->services->pluck('name'),
                    'color' => $this->getStatusColor($appointment->status)
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
    
    /**
     * Cancel an appointment.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(string $id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $appointment = Appointment::findOrFail($id);
        
        // Check if appointment can be cancelled
        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return response()->json([
                'success' => false,
                'message' => 'This appointment cannot be cancelled.'
            ], 422);
        }
        
        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully',
            'data' => $appointment
        ]);
    }
    
    /**
     * Mark an appointment as completed.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Check if appointment can be marked as completed
        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return response()->json([
                'success' => false,
                'message' => 'This appointment cannot be marked as completed.'
            ], 422);
        }
        
        $appointment->update([
            'status' => 'completed',
            'last_visit' => now()
        ]);
        
        // Update client's last visit date
        $appointment->client->update([
            'last_visit' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment marked as completed',
            'data' => $appointment
        ]);
    }
    
    /**
     * Check if a staff member is available during a specific time slot.
     *
     * @param  int  $staffId
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  int|null  $excludeAppointmentId
     * @return bool
     */
    private function checkStaffAvailability($staffId, $startTime, $endTime, $excludeAppointmentId = null)
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);
        
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
        $query = Appointment::where('staff_id', $staffId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->whereNotIn('status', ['cancelled', 'no_show']);
        
        // Exclude the current appointment if updating
        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }
        
        $conflictingAppointments = $query->count();
        
        return $conflictingAppointments === 0;
    }
    
    /**
     * Get color code for appointment status.
     *
     * @param  string  $status
     * @return string
     */
    private function getStatusColor($status)
    {
        $colors = [
            'scheduled' => '#3788d8',  // Blue
            'confirmed' => '#4CAF50',  // Green
            'completed' => '#9C27B0',  // Purple
            'cancelled' => '#F44336',  // Red
            'no_show' => '#FF9800'     // Orange
        ];
        
        return $colors[$status] ?? '#3788d8';
    }
}
