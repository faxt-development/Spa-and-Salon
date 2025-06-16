<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Services\EmailSegmentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MarketingEmail;
use App\Jobs\SendMarketingEmail;
use Inertia\Inertia;

class EmailCampaignController extends Controller
{
    protected $segmentationService;

    public function __construct(EmailSegmentationService $segmentationService)
    {
        $this->middleware('auth');
        $this->middleware('can:manage-marketing');
        
        $this->segmentationService = $segmentationService;
    }

    /**
     * Display a listing of email campaigns.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $campaigns = EmailCampaign::latest()
            ->withCount('recipients')
            ->paginate(10);
            
        return Inertia::render('EmailCampaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new email campaign.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $segments = [
            ['id' => 'recent', 'name' => 'Recent Clients (last 30 days)'],
            ['id' => 'inactive', 'name' => 'Inactive Clients (60+ days)'],
            ['id' => 'high_value', 'name' => 'High Value Clients ($500+ this year)'],
            ['id' => 'birthdays', 'name' => 'Upcoming Birthdays (next 30 days)'],
            ['id' => 'prospects', 'name' => 'Prospects (no appointments)'],
        ];
        
        return Inertia::render('EmailCampaigns/Create', [
            'segments' => $segments,
        ]);
    }

    /**
     * Store a newly created email campaign in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'segment' => 'required|string|in:recent,inactive,high_value,birthdays,prospects',
            'scheduled_for' => 'nullable|date|after:now',
        ]);
        
        // Create the campaign
        $campaign = EmailCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'segment' => $validated['segment'],
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'status' => $validated['scheduled_for'] ? 'scheduled' : 'draft',
            'user_id' => auth()->id(),
        ]);
        
        // Get recipients based on segment
        $recipients = $this->getRecipientsForSegment($validated['segment']);
        
        // Attach recipients to campaign
        $campaign->recipients()->createMany(
            $recipients->map(function($client) {
                return [
                    'client_id' => $client->id,
                    'email' => $client->email,
                    'status' => 'pending',
                ];
            })->toArray()
        );
        
        // If not scheduled for later, queue the emails
        if (!$validated['scheduled_for']) {
            $this->queueCampaignEmails($campaign);
            $campaign->update(['status' => 'sending']);
        }
        
        return redirect()->route('email-campaigns.show', $campaign)
            ->with('success', 'Email campaign created successfully.');
    }

    /**
     * Display the specified email campaign.
     *
     * @param  \App\Models\EmailCampaign  $emailCampaign
     * @return \Inertia\Response
     */
    public function show(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load(['user', 'recipients' => function($query) {
            $query->latest()->take(10);
        }]);
        
        $stats = [
            'total' => $emailCampaign->recipients()->count(),
            'sent' => $emailCampaign->recipients()->where('status', 'sent')->count(),
            'opened' => $emailCampaign->recipients()->where('status', 'opened')->count(),
            'clicked' => $emailCampaign->recipients()->where('status', 'clicked')->count(),
            'bounced' => $emailCampaign->recipients()->where('status', 'bounced')->count(),
            'complained' => $emailCampaign->recipients()->where('status', 'complained')->count(),
        ];
        
        return Inertia::render('EmailCampaigns/Show', [
            'campaign' => $emailCampaign,
            'stats' => $stats,
        ]);
    }

    /**
     * Get recipients for the specified segment.
     *
     * @param  string  $segment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecipientsForSegment(string $segment)
    {
        return match($segment) {
            'recent' => $this->segmentationService->getRecentClients(30),
            'inactive' => $this->segmentationService->getInactiveClients(60),
            'high_value' => $this->segmentationService->getHighValueClients(500, 'this_year'),
            'birthdays' => $this->segmentationService->getClientsWithUpcomingBirthdays(30),
            'prospects' => $this->segmentationService->getProspects(),
            default => collect(),
        };
    }
    
    /**
     * Queue emails for sending.
     *
     * @param  \App\Models\EmailCampaign  $campaign
     * @return void
     */
    protected function queueCampaignEmails($campaign)
    {
        $campaign->recipients()
            ->where('status', 'pending')
            ->chunk(100, function($recipients) use ($campaign) {
                foreach ($recipients as $recipient) {
                    SendMarketingEmail::dispatch($campaign, $recipient)
                        ->onQueue('emails');
                }
            });
    }
}
