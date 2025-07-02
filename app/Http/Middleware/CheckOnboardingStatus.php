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
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user has not completed onboarding and is not already on an onboarding route
            if (!$user->onboarding_completed && 
                !$request->routeIs('onboarding.*') && 
                !$request->routeIs('logout')) {
                
                // Redirect to the onboarding start page
                return redirect()->route('onboarding.start');
            }
        }

        return $next($request);
    }
}
