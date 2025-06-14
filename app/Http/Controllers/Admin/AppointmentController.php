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
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        
        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
