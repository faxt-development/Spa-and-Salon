<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     * If accessed via a custom domain, display the company-specific homepage.
     * Otherwise, display the default homepage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Redirect based on user role
            if ($user->hasRole('admin') || $user->hasRole('staff')) {
                return redirect()->route('admin.dashboard');
            }
            
            // For clients or users with no specific role
            return redirect()->route('dashboard');
        }

        // For unauthenticated users, show the appropriate homepage
        // Try to get company from different sources
        $company = $request->attributes->get('company') 
                   ?? (app()->bound('currentCompany') ? app()->make('currentCompany') : null);
        
        // Log the current state for debugging
        \Log::debug('HomeController: Company detection', [
            'from_request' => $request->attributes->has('company'),
            'from_container' => app()->bound('currentCompany'),
            'company_id' => $company ? $company->id : null,
            'url' => $request->url()
        ]);
        
        if ($company) {
            // Company-specific homepage
            return view('company-homepage', compact('company'));
        }
        
        // Default homepage
        $companyName = config('app.name', 'Faxtina');
        return view('welcome', compact('companyName'));
    }
}
