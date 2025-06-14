<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign the 'Client' role to the new user
            $clientRole = Role::where('name', 'client')->first();
            
            if ($clientRole) {
                $user->assignRole($clientRole);
            } else {
                // Log a warning if the client role doesn't exist
                Log::warning('Client role not found during user registration', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            // Fire the registered event
            event(new Registered($user));

            // Log in the user
            Auth::login($user);

            // Redirect to the dashboard with a success message
            return redirect(route('dashboard', absolute: false))
                ->with('success', 'Registration successful! Welcome to our platform.');
                
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error during user registration', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect back with error message
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['registration' => 'An error occurred during registration. Please try again.']);
        }
    }
}
