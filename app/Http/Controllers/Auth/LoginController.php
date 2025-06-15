<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        Log::info('LoginController@authenticated: User authenticated', ['user_id' => $user->id, 'email' => $user->email]);
        
        // Generate a Sanctum token for the user
        $deviceName = $request->userAgent() ?? 'Web Browser';
        $token = $user->createToken($deviceName)->plainTextToken;
        Log::info('LoginController@authenticated: Sanctum token generated', ['token_length' => strlen($token)]);
        
        // Store the token in the session for later use
        session(['api_token' => $token]);
        Log::info('LoginController@authenticated: Token stored in session');
        
        // Log user roles
        Log::info('LoginController@authenticated: User roles', ['roles' => $user->getRoleNames()]);
        
        // Redirect based on role
        if ($user->hasRole('admin')) {
            Log::info('LoginController@authenticated: Redirecting to admin dashboard');
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('staff')) {
            Log::info('LoginController@authenticated: Redirecting to staff dashboard');
            return redirect()->route('staff.dashboard');
        } elseif ($user->hasRole('client')) {
            Log::info('LoginController@authenticated: Redirecting to client dashboard');
            return redirect()->route('client.dashboard');
        }

        Log::info('LoginController@authenticated: Redirecting to default dashboard');
        return redirect($this->redirectTo);
    }
}
