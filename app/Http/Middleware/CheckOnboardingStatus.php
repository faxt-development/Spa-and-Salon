<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        info('checking onboarding status in middleware');
        // Only handle authenticated users
        if (!Auth::check()) {
            info('user is not authenticated');
            // if url doesn't have a session id or there is no
            //session info in the cookies then redirect to login
            if (!session()->has('session_id') || !session()->has('user_id')) {
                info('user is not authenticated and session id is not set');
             //redirect to the login url
             info('redirecting to login');
             //do not use the route
             return redirect()->to('/login');
            }
            return $next($request);
        }

        $user = Auth::user();
info('user: ' . $user->id);
        // If user has not completed onboarding and is not already on an onboarding route
        if (!$user->onboarding_completed &&
            !$request->routeIs('onboarding.*') &&
            !$request->routeIs('logout')) {
info('user has not completed onboarding');
            // Redirect to the onboarding start page
            return redirect()->route('onboarding.start');
        }

        // If user is on onboarding but has completed it, redirect to dashboard
        if ($user->onboarding_completed && $request->routeIs('onboarding.*')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
