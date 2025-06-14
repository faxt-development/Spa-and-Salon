<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\AppointmentController as BaseAppointmentController;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends BaseAppointmentController
{
    /**
     * Display a listing of the appointments for the authenticated staff member.
     */
    public function index()
    {
        $appointments = Appointment::with(['client', 'services'])
            ->where('staff_id', Auth::id())
            ->latest()
            ->paginate(15);
            
        return view('staff.appointments.index', compact('appointments'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        // Only allow staff to edit their own appointments
        if ($appointment->staff_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $clients = \App\Models\Client::orderBy('last_name')->get();
        $services = \App\Models\Service::orderBy('name')->get();
        
        return view('staff.appointments.edit', compact('appointment', 'clients', 'services'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Only allow staff to update their own appointments
        if ($appointment->staff_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,no_show,cancelled',
            'services' => 'required|array',
            'services.*' => 'exists:services,id'
        ]);
        
        // Format the date and time
        $startDateTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endDateTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        
        // Update the appointment
        $appointment->update([
            'client_id' => $validated['client_id'],
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'notes' => $validated['notes'],
            'status' => $validated['status']
        ]);
        
        // Sync the services
        $appointment->services()->sync($validated['services']);
        
        return redirect()->route('staff.appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }
}
