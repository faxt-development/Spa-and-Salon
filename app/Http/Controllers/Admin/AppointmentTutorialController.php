<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentTutorialController extends Controller
{
    /**
     * Display the appointment scheduling tutorial.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.appointments.tutorial');
    }
}
