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

        // Log the token for debugging
        info('API token set in session: ' . substr($token, 0, 10) . '...');

  // Redirect based on role
  if ($user->hasRole('admin')) {
      return redirect()->route('admin.dashboard');
  } elseif ($user->hasRole('staff')) {
      return redirect()->route('admin.dashboard');
  } elseif ($user->hasRole('client')) {
      return redirect()->route('dashboard');
  }

  return redirect($this->redirectTo);



    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Revoke all tokens for the current user if they exist
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
