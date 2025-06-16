<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {

        $request->authenticate();

        $request->session()->regenerate();

        // Generate a Sanctum token for the user
        $user = Auth::user();
        $deviceName = $request->userAgent() ?? 'Web Browser';
        $token = $user->createToken($deviceName)->plainTextToken;

        // Store the token in the session for later use
        session(['api_token' => $token]);

  //      return redirect()->intended(route('dashboard', absolute: false));


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
      return redirect()->route('admin.dashboard');
  } elseif ($user->hasRole('client')) {
      Log::info('LoginController@authenticated: Redirecting to client dashboard');
      return redirect()->route('dashboard');
  }

  Log::info('LoginController@authenticated: Redirecting to default dashboard');
  return redirect($this->redirectTo);



    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('AuthenticatedSessionController@destroy: Logout attempt');

        // Revoke all tokens for the current user if they exist
        $user = Auth::user();
        if ($user) {
            Log::info('AuthenticatedSessionController@destroy: Revoking tokens for user', ['user_id' => $user->id]);
            $user->tokens()->delete();
        } else {
            Log::warning('AuthenticatedSessionController@destroy: No authenticated user found during logout');
        }

        Auth::guard('web')->logout();
        Log::info('AuthenticatedSessionController@destroy: User logged out');

        $request->session()->invalidate();
        Log::info('AuthenticatedSessionController@destroy: Session invalidated');

        $request->session()->regenerateToken();
        Log::info('AuthenticatedSessionController@destroy: Session token regenerated');

        return redirect('/');
    }
}
