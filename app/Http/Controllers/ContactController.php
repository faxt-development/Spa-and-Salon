<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmission;
use Illuminate\Support\Facades\Session;

class ContactController extends Controller
{
    /**
     * Show the contact form
     */
    public function show()
    {
        return view('pages.contact');
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Send email
            Mail::to('info@faxt.com')->send(new ContactFormSubmission($validated));
            
            // Log the submission
            \Log::info('Contact form submitted', $validated);
            
            return redirect()->back()->with('success', 'Thank you for your message. We will get back to you soon!');
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'There was an error sending your message. Please try again later.');
        }
    }
}
