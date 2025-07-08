<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription required page.
     *
     * @return \Illuminate\View\View
     */
    public function showRequired()
    {
        return view('subscription.required');
    }
}
