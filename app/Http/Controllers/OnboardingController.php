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
        $user = null;

        // If user is already authenticated, use that user first
        if (Auth::check()) {
            $user = Auth::user();
            session(['onboarding_user_id' => $user->id]);
        }

        // If not authenticated or we want to override with session data
        if ($request->has('session_id') && !$user) {
            $sessionId = $request->session_id;
            session(['stripe_session_id' => $sessionId]);

            // Try to find the user based on the session ID
            if (strpos($sessionId, 'test_session_') === 0) {
                // For test sessions, use the test user
                $user = User::where('email', 'test@example.com')->first();

                if ($user) {
                    // Store the user ID in the session for later use
                    session(['onboarding_user_id' => $user->id]);
                }
            } else {
                // Look up the user by their stripe_session_id
                $user = User::where('stripe_session_id', $sessionId)->first();

                if ($user) {
                    Log::info('Found user by Stripe session ID', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'session_id' => $sessionId
                    ]);
                    session(['onboarding_user_id' => $user->id]);
                } else {
                    Log::warning('No user found with Stripe session ID', ['session_id' => $sessionId]);
                }
            }
        }

        return view('onboarding.start', [
            'user' => $user
        ]);
    }

    /**
     * Show the user registration form
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showUserForm(Request $request)
    {
        $user = null;

        // If user is already authenticated, use that user first
        if (Auth::check()) {
            $user = Auth::user();
            session(['onboarding_user_id' => $user->id]);
        } else {
            // Try to find user from session data if not authenticated
            $sessionId = session('stripe_session_id');

            // First check if we have a user ID stored in the session
            if (session()->has('onboarding_user_id')) {
                $user = User::find(session('onboarding_user_id'));
            }

            // If no user found, try to find by email from Stripe session
            if (!$user && $sessionId) {
                try {
                    // If this is a test session ID format, use our test data
                    if (strpos($sessionId, 'test_session_') === 0) {
                        $user = User::where('email', 'test@example.com')->first();
                        Log::info('Using test user for test session', ['user_id' => $user->id ?? null]);
                    } else {
                        // For real Stripe sessions, we would retrieve the customer email
                        // from Stripe API, but for now we'll just use the session data
                        // This would be implemented with Stripe API in production
                    }
                } catch (\Exception $e) {
                    Log::error('Error retrieving user from session ID: ' . $e->getMessage());
                }
            }
        }

        return view('onboarding.user-form', ['user' => $user]);
    }

    /**
     * Process the user registration form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processUserForm(Request $request)
    {
        // Check if we have an existing user ID in the request
        $existingUserId = $request->input('existing_user_id');

        // Validation rules
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ];

        // If we're updating an existing user, modify the email validation rule
        if ($existingUserId) {
            $validationRules['email'] = 'required|string|email|max:255|unique:users,email,' . $existingUserId;
        } else {
            $validationRules['email'] = 'required|string|email|max:255|unique:users,email';
        }

        $validated = $request->validate($validationRules);

        if ($existingUserId) {
            // Update existing user
            $user = User::find($existingUserId);
            if ($user) {
                $user->name = $validated['name'];
                $user->email = $validated['email'];

                // Only update password if it's different
                if ($request->filled('password')) {
                    $user->password = Hash::make($validated['password']);
                }

                $user->save();

                Log::info('Updated existing user during onboarding', ['user_id' => $user->id]);
            } else {
                // If user not found, create a new one
                $user = $this->createNewUser($validated);
            }
        } else {
            // Check if user exists with this email
            $user = User::where('email', $validated['email'])->first();

            if ($user) {
                // Update existing user
                $user->name = $validated['name'];

                // Only update password if it's different
                if ($request->filled('password')) {
                    $user->password = Hash::make($validated['password']);
                }

                $user->save();

                Log::info('Updated existing user by email during onboarding', ['user_id' => $user->id]);
            } else {
                // Create new user
                $user = $this->createNewUser($validated);
            }
        }

        // Assign admin role if not already assigned
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        // Log in the user
        Auth::login($user);

        // Store the user ID in the session
        Session::put('onboarding_user_id', $user->id);

        // Redirect to the company information form
        return redirect()->route('onboarding.company-form');
    }

    /**
     * Helper method to create a new user
     *
     * @param array $validated
     * @return User
     */
    private function createNewUser($validated)
    {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Log::info('Created new user during onboarding', ['user_id' => $user->id]);

        return $user;
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

        // Find existing company for this user or create a new one
        $user = Auth::user();
        $existingCompany = $user->companies()->first();
        
        if ($existingCompany) {
            // Update existing company
            $company = $existingCompany;
            $company->update([
                'name' => $validated['company_name'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
                'phone' => $validated['phone'],
                'website' => $validated['website'] ?? null,
            ]);
        } else {
            // Create new company
            $company = Company::create([
                'name' => $validated['company_name'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
                'phone' => $validated['phone'],
                'website' => $validated['website'] ?? null,
            ]);
        }
        
        // Get the authenticated user
        $user = Auth::user();
        
        // Associate the user with the company using the pivot table
        // First detach to avoid duplicates if re-running onboarding
        $user->companies()->detach($company->id);
        
        // Then attach with the admin role and mark as primary
        $user->companies()->attach($company->id, [
            'is_primary' => true,
            'role' => 'admin'
        ]);
        
        Log::info('Associated user with company', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'is_primary' => true,
            'role' => 'admin'
        ]);

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

        //if this user is admin, redirect to admin dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', 'Welcome to Faxtina! Your account has been set up successfully.');
        }
        // Redirect to the dashboard
        return redirect()->route('dashboard')->with('success', 'Welcome to Faxtina! Your account has been set up successfully.');
    }
}
