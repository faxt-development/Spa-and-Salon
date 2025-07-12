<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingChecklistController extends Controller
{
    /**
     * Show the admin onboarding checklist.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('admin.onboarding-checklist', [
            'title' => 'Admin Onboarding Checklist'
        ]);
    }
}
