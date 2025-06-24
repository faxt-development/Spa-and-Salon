<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppointmentController as BaseAppointmentController;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends BaseAppointmentController
{
    /**
     * Display a listing of the appointments.
     */
    public function index()
    {
        $appointments = Appointment::with(['client', 'staff', 'services'])
            ->latest()
            ->paginate(15);
            
        return view('admin.appointments.index', compact('appointments'));
    }

    /**
     * Display the specified appointment.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['client', 'staff', 'services', 'transactions'])
            ->findOrFail($id);
        
        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        $appointment = Appointment::with(['client', 'staff', 'services'])->findOrFail($id);
        $clients = \App\Models\Client::all();
        $staffMembers = \App\Models\Staff::all();
        $services = \App\Models\Service::all();
        
        return view('admin.appointments.edit', compact('appointment', 'clients', 'staffMembers', 'services'));
    }

    /**
     * Update the specified appointment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'staff_id' => 'required|exists:staff,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
        ]);
        
        $appointment->update($validated);
        
        // Sync services
        $appointment->services()->sync($request->services);
        
        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment updated successfully');
    }

    /**
     * Remove the specified appointment from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        
        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully');
    }
}
