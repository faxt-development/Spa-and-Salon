<?php

namespace App\Http\Controllers;

use App\Models\EmailRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmailTrackingController extends Controller
{
    /**
     * Track when an email is opened
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function trackOpen($token)
    {
        try {
            $recipient = EmailRecipient::where('token', $token)->first();
            
            if ($recipient && !$recipient->opened_at) {
                $recipient->update([
                    'opened_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                
                // Update the campaign stats
                $this->updateCampaignStats($recipient->email_campaign_id);
            }
            
            // Return a transparent 1x1 pixel gif
            $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            return response($gif, 200, [
                'Content-Type' => 'image/gif',
                'Content-Length' => strlen($gif),
                'Cache-Control' => 'private, no-cache, no-cache=Set-Cookie, proxy-revalidate',
                'Expires' => '0',
                'Pragma' => 'no-cache',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Email open tracking failed: ' . $e->getMessage());
            
            // Still return the gif even if tracking fails
            $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            return response($gif, 200, ['Content-Type' => 'image/gif']);
        }
    }
    
    /**
     * Track when a link in an email is clicked
     * 
     * @param string $token
     * @param string $url
     * @return \Illuminate\Http\RedirectResponse
     */
    public function trackClick($token, $url)
    {
        try {
            $recipient = EmailRecipient::where('token', $token)->first();
            
            if ($recipient) {
                // Record the click
                $recipient->update([
                    'clicked_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'last_clicked_url' => $url,
                ]);
                
                // If this is the first time the email is being opened, record that too
                if (!$recipient->opened_at) {
                    $recipient->update([
                        'opened_at' => now(),
                    ]);
                }
                
                // Update the campaign stats
                $this->updateCampaignStats($recipient->email_campaign_id);
                
                // Log the click
                DB::table('email_click_logs')->insert([
                    'email_recipient_id' => $recipient->id,
                    'url' => $url,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Decode the URL and redirect
            $decodedUrl = base64_decode(str_replace(['-', '_'], ['+', '/'], $url));
            
            // Validate the URL to prevent open redirects
            if (filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
                // Check if the URL is from our domain
                $appUrl = config('app.url');
                if (strpos($decodedUrl, $appUrl) === 0) {
                    return redirect()->to($decodedUrl);
                }
                
                // For external URLs, show a warning page
                return view('emails.link-warning', [
                    'url' => $decodedUrl,
                    'domain' => parse_url($decodedUrl, PHP_URL_HOST)
                ]);
            }
            
            // If URL is invalid, redirect to home
            return redirect()->route('home');
            
        } catch (\Exception $e) {
            Log::error('Email click tracking failed: ' . $e->getMessage());
            
            // If something goes wrong, try to redirect to the decoded URL or home
            try {
                $decodedUrl = base64_decode(str_replace(['-', '_'], ['+', '/'], $url));
                if (filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
                    return redirect()->to($decodedUrl);
                }
            } catch (\Exception $e) {
                // Do nothing
            }
            
            return redirect()->route('home');
        }
    }
    
    /**
     * Unsubscribe a user from emails
     * 
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function unsubscribe($token)
    {
        $recipient = EmailRecipient::where('unsubscribe_token', $token)
                                 ->orWhere('token', $token)
                                 ->first();
        
        if (!$recipient) {
            return view('emails.unsubscribe-invalid');
        }
        
        // If this is a specific campaign unsubscribe, mark it
        if ($recipient->token === $token) {
            $recipient->update([
                'unsubscribed_at' => now(),
                'unsubscribed_ip' => request()->ip(),
            ]);
            
            // Update campaign stats
            $this->updateCampaignStats($recipient->email_campaign_id);
            
            return view('emails.unsubscribed', [
                'email' => $recipient->email,
                'fromCampaign' => true,
                'campaignName' => $recipient->campaign->name ?? null,
            ]);
        }
        
        // If this is a global unsubscribe link
        if ($recipient->unsubscribe_token === $token) {
            // Update all recipient records for this email
            EmailRecipient::where('email', $recipient->email)
                        ->update([
                            'unsubscribed_at' => now(),
                            'unsubscribed_ip' => request()->ip(),
                        ]);
            
            // Also update the client record if it exists
            if ($recipient->client) {
                $recipient->client->update([
                    'unsubscribed_at' => now(),
                    'unsubscribed_ip' => request()->ip(),
                ]);
            }
            
            return view('emails.unsubscribed', [
                'email' => $recipient->email,
                'fromCampaign' => false,
            ]);
        }
        
        return view('emails.unsubscribe-invalid');
    }
    
    /**
     * Show email preferences page
     * 
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function preferences($token)
    {
        $recipient = EmailRecipient::where('preferences_token', $token)
                                 ->orWhere('token', $token)
                                 ->first();
        
        if (!$recipient) {
            return view('emails.preferences-invalid');
        }
        
        // Get all subscription types/categories
        $subscriptionTypes = [
            'marketing' => 'Marketing Emails',
            'newsletter' => 'Newsletters',
            'appointments' => 'Appointment Reminders',
            'promotions' => 'Special Promotions',
        ];
        
        // Get current subscriptions (this would come from the client model in a real app)
        $currentSubscriptions = $recipient->client->subscriptions ?? [
            'marketing' => true,
            'newsletter' => true,
            'appointments' => true,
            'promotions' => true,
        ];
        
        return view('emails.preferences', [
            'email' => $recipient->email,
            'subscriptionTypes' => $subscriptionTypes,
            'currentSubscriptions' => $currentSubscriptions,
            'token' => $token,
        ]);
    }
    
    /**
     * Update email preferences
     * 
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePreferences(Request $request, $token)
    {
        $recipient = EmailRecipient::where('preferences_token', $token)
                                 ->orWhere('token', $token)
                                 ->first();
        
        if (!$recipient) {
            return redirect()->route('email.preferences', $token)
                           ->with('error', 'Invalid or expired preferences link.');
        }
        
        // Validate the request
        $validated = $request->validate([
            'subscriptions' => 'required|array',
            'subscriptions.*' => 'boolean',
        ]);
        
        // Update the client's subscriptions
        if ($recipient->client) {
            $recipient->client->update([
                'email_preferences' => $validated['subscriptions'],
                'preferences_updated_at' => now(),
                'preferences_updated_ip' => $request->ip(),
            ]);
        }
        
        // If they unchecked all subscriptions, treat as an unsubscribe
        if (empty(array_filter($validated['subscriptions']))) {
            return $this->unsubscribe($token);
        }
        
        return redirect()->route('email.preferences', $token)
                         ->with('success', 'Your email preferences have been updated.');
    }
    
    /**
     * Resubscribe a user to emails
     * 
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function resubscribe($token)
    {
        $recipient = EmailRecipient::where('token', $token)
                                 ->orWhere('unsubscribe_token', $token)
                                 ->orWhere('preferences_token', $token)
                                 ->first();
        
        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.'
            ], 404);
        }
        
        try {
            // Clear unsubscribe status for this recipient
            $recipient->update([
                'unsubscribed_at' => null,
                'unsubscribed_ip' => null,
            ]);
            
            // If this is a global token, also update all other recipients with this email
            if ($recipient->unsubscribe_token === $token) {
                EmailRecipient::where('email', $recipient->email)
                            ->where('id', '!=', $recipient->id)
                            ->update([
                                'unsubscribed_at' => null,
                                'unsubscribed_ip' => null,
                            ]);
                
                // Also update the client record if it exists
                if ($recipient->client) {
                    $recipient->client->update([
                        'unsubscribed_at' => null,
                        'unsubscribed_ip' => null,
                    ]);
                }
            }
            
            // Update campaign stats if applicable
            if ($recipient->email_campaign_id) {
                $this->updateCampaignStats($recipient->email_campaign_id);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'You have been successfully resubscribed.',
                'preferences_url' => route('email.preferences', $recipient->preferences_token ?: $token)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ], 500);
        }
    }
    
    /**
     * Update campaign statistics
     * 
     * @param int $campaignId
     * @return void
     */
    protected function updateCampaignStats($campaignId)
    {
        try {
            $stats = DB::table('email_recipients')
                     ->selectRaw('COUNT(*) as total')
                     ->selectRaw('SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened')
                     ->selectRaw('SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked')
                     ->selectRaw('SUM(CASE WHEN unsubscribed_at IS NOT NULL THEN 1 ELSE 0 END) as unsubscribed')
                     ->where('email_campaign_id', $campaignId)
                     ->first();
            
            if ($stats) {
                DB::table('email_campaigns')
                  ->where('id', $campaignId)
                  ->update([
                      'opened_count' => $stats->opened,
                      'clicked_count' => $stats->clicked,
                      'unsubscribed_count' => $stats->unsubscribed,
                      'open_rate' => $stats->total > 0 ? ($stats->opened / $stats->total) * 100 : 0,
                      'click_rate' => $stats->total > 0 ? ($stats->clicked / $stats->total) * 100 : 0,
                      'updated_at' => now(),
                  ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update campaign stats: ' . $e->getMessage());
        }
    }
}
