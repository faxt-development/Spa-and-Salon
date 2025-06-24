<?php

namespace App\Http\Controllers;

use App\Models\DripCampaign;
use App\Models\Client;
use App\Services\EmailSegmentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\MarketingEmail;
use App\Jobs\SendDripCampaignEmail;
use Illuminate\Support\Str;

class DripCampaignController extends Controller
{
    protected $segmentationService;

    public function __construct(EmailSegmentationService $segmentationService)
    {
        
        
        
        $this->segmentationService = $segmentationService;
    }

    /**
     * Display a listing of the drip campaigns.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $campaigns = DripCampaign::latest()
            ->withCount('recipients')
            ->paginate(10);
            
        return view('drip-campaigns.index', [
            'campaigns' => $campaigns,
            'welcomeCount' => DripCampaign::where('type', DripCampaign::TYPE_WELCOME)->count(),
            'birthdayCount' => DripCampaign::where('type', DripCampaign::TYPE_BIRTHDAY)->count(),
            'reengagementCount' => DripCampaign::where('type', DripCampaign::TYPE_REENGAGEMENT)->count(),
        ]);
    }

    /**
     * Show the form for creating a new drip campaign.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $campaignTypes = [
            ['id' => DripCampaign::TYPE_WELCOME, 'name' => 'Welcome Series'],
            ['id' => DripCampaign::TYPE_BIRTHDAY, 'name' => 'Birthday Promotion'],
            ['id' => DripCampaign::TYPE_REENGAGEMENT, 'name' => 'Re-engagement Campaign'],
        ];
        
        return view('drip-campaigns.create', [
            'campaignTypes' => $campaignTypes,
            'defaultFrom' => config('mail.from.address'),
            'defaultFromName' => config('mail.from.name'),
        ]);
    }

    /**
     * Store a newly created drip campaign in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', [
                DripCampaign::TYPE_WELCOME,
                DripCampaign::TYPE_BIRTHDAY,
                DripCampaign::TYPE_REENGAGEMENT
            ]),
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'content' => 'required|string',
            'delay_days' => 'required|integer|min:0',
            'sequence_order' => 'required|integer|min:0',
            'from_email' => 'required|email',
            'from_name' => 'required|string|max:255',
            'reply_to' => 'nullable|email',
            'is_active' => 'boolean',
            'configuration' => 'nullable|array',
        ]);

        // Create the campaign
        $campaign = DripCampaign::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'content' => $validated['content'],
            'delay_days' => $validated['delay_days'],
            'sequence_order' => $validated['sequence_order'],
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'],
            'reply_to' => $validated['reply_to'] ?? $validated['from_email'],
            'is_active' => $validated['is_active'] ?? true,
            'configuration' => $validated['configuration'] ?? [],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('drip-campaigns.index')
                         ->with('success', 'Drip campaign created successfully.');
    }

    /**
     * Display the specified drip campaign.
     *
     * @param  \App\Models\DripCampaign  $dripCampaign
     * @return \Illuminate\View\View
     */
    public function show(DripCampaign $dripCampaign)
    {
        $dripCampaign->load(['recipients' => function($query) {
            $query->latest()->take(50);
        }]);
        
        // Get campaign stats
        $stats = [
            'sent' => $dripCampaign->recipients()->whereNotNull('sent_at')->count(),
            'opened' => $dripCampaign->recipients()->whereNotNull('opened_at')->count(),
            'clicked' => $dripCampaign->recipients()->whereNotNull('clicked_at')->count(),
            'unsubscribed' => $dripCampaign->recipients()->whereNotNull('unsubscribed_at')->count(),
        ];
        
        $stats['open_rate'] = $stats['sent'] > 0 ? round(($stats['opened'] / $stats['sent']) * 100, 2) : 0;
        $stats['click_rate'] = $stats['sent'] > 0 ? round(($stats['clicked'] / $stats['sent']) * 100, 2) : 0;
        $stats['unsubscribe_rate'] = $stats['sent'] > 0 ? round(($stats['unsubscribed'] / $stats['sent']) * 100, 2) : 0;
        
        return view('drip-campaigns.show', [
            'campaign' => $dripCampaign,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified drip campaign.
     *
     * @param  \App\Models\DripCampaign  $dripCampaign
     * @return \Illuminate\View\View
     */
    public function edit(DripCampaign $dripCampaign)
    {
        $campaignTypes = [
            ['id' => DripCampaign::TYPE_WELCOME, 'name' => 'Welcome Series'],
            ['id' => DripCampaign::TYPE_BIRTHDAY, 'name' => 'Birthday Promotion'],
            ['id' => DripCampaign::TYPE_REENGAGEMENT, 'name' => 'Re-engagement Campaign'],
        ];
        
        return view('drip-campaigns.edit', [
            'campaign' => $dripCampaign,
            'campaignTypes' => $campaignTypes,
        ]);
    }

    /**
     * Update the specified drip campaign in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DripCampaign  $dripCampaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, DripCampaign $dripCampaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', [
                DripCampaign::TYPE_WELCOME,
                DripCampaign::TYPE_BIRTHDAY,
                DripCampaign::TYPE_REENGAGEMENT
            ]),
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'content' => 'required|string',
            'delay_days' => 'required|integer|min:0',
            'sequence_order' => 'required|integer|min:0',
            'from_email' => 'required|email',
            'from_name' => 'required|string|max:255',
            'reply_to' => 'nullable|email',
            'is_active' => 'boolean',
            'configuration' => 'nullable|array',
        ]);

        // Update the campaign
        $dripCampaign->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'content' => $validated['content'],
            'delay_days' => $validated['delay_days'],
            'sequence_order' => $validated['sequence_order'],
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'],
            'reply_to' => $validated['reply_to'] ?? $validated['from_email'],
            'is_active' => $validated['is_active'] ?? true,
            'configuration' => $validated['configuration'] ?? [],
        ]);

        return redirect()->route('drip-campaigns.show', $dripCampaign)
                         ->with('success', 'Drip campaign updated successfully.');
    }

    /**
     * Remove the specified drip campaign from storage.
     *
     * @param  \App\Models\DripCampaign  $dripCampaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(DripCampaign $dripCampaign)
    {
        $dripCampaign->delete();

        return redirect()->route('drip-campaigns.index')
                         ->with('success', 'Drip campaign deleted successfully.');
    }
    
    /**
     * Toggle the active status of a drip campaign.
     *
     * @param  \App\Models\DripCampaign  $dripCampaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(DripCampaign $dripCampaign)
    {
        $dripCampaign->update([
            'is_active' => !$dripCampaign->is_active,
        ]);

        $status = $dripCampaign->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
                         ->with('success', "Drip campaign {$status} successfully.");
    }
    
    /**
     * Manually trigger a drip campaign for testing.
     *
     * @param  \App\Models\DripCampaign  $dripCampaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function manualTrigger(DripCampaign $dripCampaign)
    {
        // Get appropriate segment based on campaign type
        $clients = collect();
        
        if ($dripCampaign->isWelcomeSeries()) {
            // Get recent clients (last 30 days) for testing
            $clients = $this->segmentationService->getRecentClients(30, 10);
        } elseif ($dripCampaign->isBirthdayPromotion()) {
            // Get clients with birthdays in the next 30 days for testing
            $clients = $this->segmentationService->getBirthdayClients(30, 10);
        } elseif ($dripCampaign->isReengagement()) {
            // Get inactive clients for testing
            $clients = $this->segmentationService->getInactiveClients(90, 180, 10);
        }
        
        // Queue emails for the test segment
        $count = 0;
        foreach ($clients as $client) {
            // Create a unique tracking token
            $token = Str::random(64);
            $unsubscribeToken = Str::random(64);
            $preferencesToken = Str::random(64);
            
            // Create recipient record
            $recipient = $dripCampaign->recipients()->create([
                'client_id' => $client->id,
                'email' => $client->email,
                'name' => $client->name,
                'token' => $token,
                'unsubscribe_token' => $unsubscribeToken,
                'preferences_token' => $preferencesToken,
                'merge_data' => [
                    'name' => $client->name,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                ],
            ]);
            
            // Queue the email for sending
            SendDripCampaignEmail::dispatch($recipient);
            $count++;
        }
        
        return redirect()->back()
                         ->with('success', "Test triggered for {$count} recipients. Emails will be sent shortly.");
    }
}
