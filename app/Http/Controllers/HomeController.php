<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

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
        // Try to get company from different sources
        $company = $request->attributes->get('company') 
                   ?? app()->bound('currentCompany') ? app()->make('currentCompany') : null;
        
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
