<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding start page
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showStart(Request $request)
    {
        // Store session ID if provided
        if ($request->has('session_id')) {
            session(['stripe_session_id' => $request->session_id]);
        }
        
        // If user is already authenticated, store their ID in the session
        if (Auth::check()) {
            session(['onboarding_user_id' => Auth::id()]);
        }
        
        return view('onboarding.start');
    }
    
    /**
     * Show the user registration form
     * 
     * @return \Illuminate\View\View
     */
    public function showUserForm()
    {
        return view('onboarding.user-form');
    }
    
    /**
     * Process the user registration form
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processUserForm(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        // Assign admin role
        $user->assignRole('admin');
        
        // Log in the user
        Auth::login($user);
        
        // Store the user ID in the session
        Session::put('onboarding_user_id', $user->id);
        
        // Redirect to the company information form
        return redirect()->route('onboarding.company-form');
    }
    
    /**
     * Show the company information form
     * 
     * @return \Illuminate\View\View
     */
    public function showCompanyForm()
    {
        return view('onboarding.company-form');
    }
    
    /**
     * Process the company information form
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCompanyForm(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
        ]);
        
        // Create or update the company
        $company = Company::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'name' => $validated['company_name'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
                'phone' => $validated['phone'],
                'website' => $validated['website'] ?? null,
            ]
        );
        
        // Redirect to the feature tour
        return redirect()->route('onboarding.feature-tour');
    }
    
    /**
     * Show the feature tour
     * 
     * @return \Illuminate\View\View
     */
    public function showFeatureTour()
    {
        return view('onboarding.feature-tour');
    }
    
    /**
     * Complete the onboarding process
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete()
    {
        // Mark onboarding as complete in the user's record
        $user = Auth::user();
        $user->onboarding_completed = true;
        $user->save();
        
        // Clear onboarding session data
        Session::forget('stripe_session_id');
        Session::forget('onboarding_user_id');
        
        // Redirect to the dashboard
        return redirect()->route('dashboard')->with('success', 'Welcome to Faxtina! Your account has been set up successfully.');
    }
}
