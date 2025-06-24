<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Policies\AppointmentPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    /**
     * Store a newly created appointment in storage.
     */
    protected $appointmentService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;

        // Apply auth middleware to all methods except those specified
        $this->middleware('auth');

        // Apply role middleware to admin-only methods
        $this->middleware('role:admin')->only([
            'show', 'edit', 'update', 'destroy'
        ]);
    }
    /**
     * Display the specified appointment.
     * Shows different views based on user role.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show(string $id)
    {
        // Eager load all necessary relationships
        $appointment = Appointment::with([
            'client',
            'staff',
            'services',
            'products',
            'payments',
            'transactions'
        ])->findOrFail($id);

        // Authorization check
        $this->authorize('view', $appointment);

        // Determine which view to show based on user role
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return view('admin.appointments.show', compact('appointment'));
        }

        // For non-admin users (staff or clients)
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Update the specified appointment in storage.
     * Handles both admin and non-admin updates with appropriate validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Authorization check
        $this->authorize('update', $appointment);

        // Common validation rules
        $rules = [
            'client_id' => 'required|exists:clients,id',
            'staff_id' => 'required|exists:staff,id',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
        ];

        // Add time validation based on input format
        if ($request->has('date') && $request->has('start_time') && $request->has('end_time')) {
            // Handle form with separate date and time fields
            $rules['date'] = 'required|date';
            $rules['start_time'] = 'required|string';
            $rules['end_time'] = 'required|string';
        } else {
            // Handle form with datetime fields
            $rules['start_time'] = 'required|date';
            $rules['end_time'] = 'required|date|after:start_time';
        }

        // Validate the request
        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            // Format start and end times based on input format
            if (isset($validated['date'])) {
                $startDateTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
                $endDateTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
            } else {
                $startDateTime = Carbon::parse($validated['start_time']);
                $endDateTime = Carbon::parse($validated['end_time']);
            }

            // Get services and calculate total price
            $services = Service::whereIn('id', $validated['service_ids'])->get();
            $totalPrice = $services->sum('price');

            // Prepare update data
            $updateData = [
                'client_id' => $validated['client_id'],
                'staff_id' => $validated['staff_id'],
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'total_price' => $totalPrice,
            ];

            // Update the appointment
            $appointment->update($updateData);

            // Update services
            $appointment->services()->detach();
            foreach ($services as $service) {
                $appointment->services()->attach($service->id, [
                    'price' => $service->price,
                    'duration' => $service->duration
                ]);
            }

            DB::commit();

            // Determine redirect route based on user role
            $route = auth()->user()->hasRole('admin')
                ? 'admin.appointments.show'
                : 'web.appointments.show';

            return redirect()->route($route, $appointment->id)
                ->with('success', 'Appointment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update appointment: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update appointment. Please try again.']);
        }

        // Sync services
        $appointment->services()->sync($request->services);

        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment updated successfully');
    }


    /**
     * Remove the specified appointment from storage.
     * Handles both admin and non-admin deletions with proper authorization.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Authorization check
        $this->authorize('delete', $appointment);

        // Additional validation for non-admin users
        if (!auth()->user()->hasRole('admin') && $appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Completed appointments cannot be deleted.']);
        }

        try {
            $appointment->delete();

            // Determine redirect route based on user role
            $route = auth()->user()->hasRole('admin')
                ? 'admin.appointments.index'
                : 'web.appointments.index';

            return redirect()->route($route)
                ->with('success', 'Appointment deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Failed to delete appointment: ' . $e->getMessage());
            return back()
                ->withErrors(['error' => 'Failed to delete appointment. Please try again.']);
        }
    }

    /**
     * Display a listing of the appointments.
     * Shows different views based on user role.
     */
    public function index()
    {
        // For admin users, show the admin appointments list
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            $appointments = Appointment::with(['client', 'staff', 'services'])
                ->latest()
                ->paginate(15);

            return view('admin.appointments.index', compact('appointments'));
        }

        // For non-admin users, show the appointment booking form
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
     * Show the form for editing the specified appointment.
     * Shows different edit forms based on user role.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        $appointment = Appointment::with(['client', 'staff', 'services'])->findOrFail($id);

        // Authorization check
        $this->authorize('update', $appointment);

        // Common data for both views
        $clients = Client::orderBy('last_name')->get();
        $services = Service::orderBy('name')->get();

        // For admin users, show the admin edit form with all staff
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            $staff = Staff::orderBy('last_name')->get();
            return view('admin.appointments.edit', compact('appointment', 'clients', 'staff', 'services'));
        }

        // For non-admin users (staff), only show staff members that are active
        $staff = Staff::where('is_active', true)
            ->orderBy('last_name')
            ->get();

        return view('appointments.edit', compact('appointment', 'clients', 'staff', 'services'));
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
