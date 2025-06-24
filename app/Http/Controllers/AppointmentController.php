<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{

    /**
     * Display a listing of the appointments.
     */
    public function index()
    {
        // Simplified authentication check
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            Log::warning('AppointmentController@index: User is NOT authenticated', [
                'session_id' => session()->getId()
            ]);
        }

        $staff = Staff::all();
        return view('appointments.index', compact('staff'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        $clients = Client::orderBy('last_name')->get();
        $staff = Staff::orderBy('last_name')->get();
        $services = Service::orderBy('name')->get();

        // Check if the user is a client
        $isClient = false;
        if (Auth::check()) {
            $user = Auth::user();
            $isClient = $user->hasRole('client');
        }

        return view('appointments.create', compact('clients', 'staff', 'services', 'isClient'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function store(Request $request)
    {
        Log::info('AppointmentController@store: Storing new appointment', [
            'request' => $request->all()
        ]);

        // Validate request data
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required_without:client_id|string|max:255',
            'client_email' => 'required_without:client_id|email|max:255',
            'client_phone' => 'required_without:client_id|string|max:20',
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'start_time' => 'required|string',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'notes' => 'nullable|string',
        ], [
            'client_name.required_without' => 'Client name is required when not selecting an existing client',
            'client_email.required_without' => 'Client email is required when not selecting an existing client',
            'client_phone.required_without' => 'Client phone is required when not selecting an existing client',
        ]);

        // If we have client details but no client_id, try to find an existing client
        if (empty($validated['client_id']) && !empty($validated['client_email'])) {
            // Split full name into first and last name
            $nameParts = explode(' ', trim($validated['client_name']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            // Look for existing client by email and name
            $client = Client::where('email', $validated['client_email'])
                ->where(function($query) use ($firstName, $lastName) {
                    $query->where('first_name', 'like', $firstName . '%')
                          ->where('last_name', 'like', $lastName . '%');
                })
                ->first();

            if ($client) {
                // Use existing client
                $validated['client_id'] = $client->id;
                $validated['client_phone'] = $validated['client_phone'] ?? $client->phone;
            } else {
                // Create new client
                $client = Client::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $validated['client_email'],
                    'phone' => $validated['client_phone'],
                    'marketing_consent' => false, // Default to false for GDPR compliance
                ]);
                $validated['client_id'] = $client->id;
            }
        }

        // Format the request data to match the service expectations
        $appointmentData = [
            'client_id' => $validated['client_id'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'client_email' => $validated['client_email'] ?? null,
            'client_phone' => $validated['client_phone'] ?? null,
            'staff_id' => $validated['staff_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'service_ids' => $validated['service_ids'],
            'notes' => $validated['notes'] ?? null,
        ];

        try {
            // Create the appointment using the service
            $result = $this->appointmentService->createAppointment($appointmentData);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            return redirect()->route('web.appointments.show', $result['appointment']->id)
                ->with('success', 'Appointment created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create appointment: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['client', 'staff', 'services', 'products', 'payments'])
            ->findOrFail($id);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(string $id)
    {
        $appointment = Appointment::with(['client', 'staff', 'services'])->findOrFail($id);
        $clients = Client::orderBy('last_name')->get();
        $staff = Staff::orderBy('last_name')->get();
        $services = Service::orderBy('name')->get();

        return view('appointments.edit', compact('appointment', 'clients', 'staff', 'services'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($id);

        DB::beginTransaction();

        try {
            // Format start and end times
            $startDateTime = Carbon::parse($request->date . ' ' . $request->start_time);
            $endDateTime = Carbon::parse($request->date . ' ' . $request->end_time);

            // Calculate total price based on selected services
            $services = Service::whereIn('id', $request->service_ids)->get();
            $totalPrice = $services->sum('price');

            // Update the appointment
            $appointment->update([
                'client_id' => $request->client_id,
                'staff_id' => $request->staff_id,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'status' => $request->status,
                'notes' => $request->notes,
                'total_price' => $totalPrice,
            ]);

            // Update services
            $appointment->services()->detach();
            foreach ($services as $service) {
                $appointment->services()->attach($service->id, [
                    'price' => $service->price,
                    'duration' => $service->duration
                ]);
            }

            DB::commit();

            return redirect()->route('web.appointments.show', $appointment->id)
                ->with('success', 'Appointment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Failed to update appointment: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Check if appointment can be deleted (e.g., not completed)
        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Completed appointments cannot be deleted.']);
        }

        $appointment->delete();

        return redirect()->route('web.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    /**
     * Cancel the specified appointment.
     */
    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:255'
        ]);

        $appointment = Appointment::findOrFail($id);

        // Check if appointment can be cancelled
        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return back()->withErrors(['error' => 'This appointment cannot be cancelled.']);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason
        ]);

        return redirect()->route('web.appointments.show', $appointment->id)
            ->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Mark the specified appointment as completed.
     */
    public function complete(string $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Check if appointment can be marked as completed
        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return back()->withErrors(['error' => 'This appointment cannot be marked as completed.']);
        }

        $appointment->update([
            'status' => 'completed'
        ]);

        // Update client's last visit date
        $appointment->client->update([
            'last_visit' => now()
        ]);

        return redirect()->route('web.appointments.show', $appointment->id)
            ->with('success', 'Appointment marked as completed.');
    }
}
