<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the default dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Redirect based on user role
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('staff')) {
            return redirect()->route('staff.dashboard');
        } elseif ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        }

        return view('dashboard');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function admin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.dashboard', [
            'title' => 'Admin Dashboard'
        ]);
    }

    /**
     * Show the staff dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function staff()
    {
        if (!auth()->user()->hasRole('staff')) {
            abort(403, 'Unauthorized action.');
        }

        return view('staff.dashboard', [
            'title' => 'Staff Dashboard'
        ]);
    }

    /**
     * Show the client dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function client()
    {
        if (!auth()->user()->hasRole('client')) {
            abort(403, 'Unauthorized action.');
        }

        return view('client.dashboard', [
            'title' => 'My Dashboard'
        ]);
    }
}
