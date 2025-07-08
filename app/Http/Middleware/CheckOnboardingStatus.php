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
        // Only handle authenticated users
        if (!Auth::check()) {
            // if url doesn't have a session id or there is no
            //session info in the cookies then redirect to login
            if (!session()->has('session_id') || !session()->has('user_id')) {
             //redirect to the login url
             //do not use the route
             return redirect()->to('/login');
            }
            return $next($request);
        }

        $user = Auth::user();

        // Only apply onboarding logic to admin users with active subscriptions
        if ($user->hasRole('admin') && $user->hasActiveSubscription()) {
            // If admin has not completed onboarding
            if (!$user->onboarding_completed) {
                // If not already on an onboarding route, redirect to onboarding
                if (!$request->routeIs('onboarding.*') && !$request->routeIs('logout')) {
                    return redirect()->route('onboarding.start');
                }
            } else {
                // If admin has completed onboarding but is on an onboarding route, redirect to dashboard
                if ($request->routeIs('onboarding.*')) {
                    return redirect()->route('admin.dashboard');
                }
            }
        }

        return $next($request);
    }
}
