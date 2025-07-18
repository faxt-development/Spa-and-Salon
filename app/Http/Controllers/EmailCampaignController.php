<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Models\Client;
use App\Services\EmailSegmentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\MarketingEmail;
use App\Jobs\SendMarketingEmail;
use Illuminate\Support\Str;

class EmailCampaignController extends Controller
{
    protected $segmentationService;

    public function __construct(EmailSegmentationService $segmentationService)
    {



        $this->segmentationService = $segmentationService;
    }

    /**
     * Display a listing of email campaigns.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $campaigns = EmailCampaign::latest()
            ->withCount('recipients')
            ->paginate(10);

        return view('email-campaigns.index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new email campaign.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $segments = [
            ['id' => 'all', 'name' => 'All Clients'],
            ['id' => 'recent', 'name' => 'Recent Clients (last 30 days)'],
            ['id' => 'inactive', 'name' => 'Inactive Clients (60+ days)'],
            ['id' => 'high_value', 'name' => 'High-Value Clients ($500+ this year)'],
            ['id' => 'birthday_this_month', 'name' => 'Birthdays This Month'],
            ['id' => 'no_appointments', 'name' => 'No Appointments Yet'],
        ];

        return view('email-campaigns.create', [
            'segments' => $segments,
            'defaultFrom' => config('mail.from.address'),
            'defaultFromName' => config('mail.from.name'),
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
            'preview_text' => 'nullable|string|max:255',
            'content' => 'required|string',
            'segment' => 'required|string|in:all,recent,inactive,high_value,birthday_this_month,no_appointments',
            'scheduled_for' => 'nullable|date|after:now',
            'from_email' => 'required|email',
            'from_name' => 'required|string|max:255',
            'reply_to' => 'nullable|email',
            'status' => 'required|in:draft,scheduled',
        ]);

        // Create the campaign
        $campaign = EmailCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'content' => $validated['content'],
            'segment' => $validated['segment'],
            'status' => $validated['status'],
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'],
            'reply_to' => $validated['reply_to'] ?? $validated['from_email'],
            'user_id' => auth()->id(),
        ]);

        // Get recipients based on segment
        $recipients = $this->getRecipientsForSegment($validated['segment']);

        // Add recipients to campaign in chunks to avoid memory issues
        $recipientData = [];
        $now = now();

        foreach ($recipients as $recipient) {
            $recipientData[] = [
                'email_campaign_id' => $campaign->id,
                'email' => $recipient->email,
                'name' => $recipient->name,
                'token' => Str::random(32),
                'unsubscribe_token' => Str::random(32),
                'preferences_token' => Str::random(32),
                'client_id' => $recipient->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert in chunks of 100
            if (count($recipientData) >= 100) {
                EmailRecipient::insert($recipientData);
                $recipientData = [];
            }
        }

        // Insert any remaining recipients
        if (!empty($recipientData)) {
            EmailRecipient::insert($recipientData);
        }

        // Update recipient count
        $campaign->update(['recipient_count' => count($recipients)]);

        // If campaign is scheduled, dispatch the job
        if ($campaign->isScheduled()) {
            SendMarketingEmail::dispatch($campaign)
                ->delay($campaign->scheduled_for);

            $message = 'Campaign scheduled successfully.';
        } else {
            $message = 'Campaign saved as draft.';
        }

        return redirect()->route('admin.email-campaigns.index')
                         ->with('success', $message);
    }

    /**
     * Display the specified email campaign.
     *
     * @param  \App\Models\EmailCampaign  $emailCampaign
     * @return \Illuminate\View\View
     */
    public function show(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load(['user']);

        // Basic stats
        $stats = [
            'sent' => $emailCampaign->recipients_count,
            'opened' => $emailCampaign->opened_recipients_count ?? 0,
            'clicked' => $emailCampaign->clicked_recipients_count ?? 0,
            'bounced' => $emailCampaign->bounced_recipients_count ?? 0,
            'unsubscribed' => $emailCampaign->unsubscribed_recipients_count ?? 0,
            'complained' => $emailCampaign->recipients()->whereNotNull('complained_at')->count(),
        ];

        // Calculate rates
        $stats['open_rate'] = $stats['sent'] > 0 ? round(($stats['opened'] / $stats['sent']) * 100, 2) : 0;
        $stats['click_rate'] = $stats['sent'] > 0 ? round(($stats['clicked'] / $stats['sent']) * 100, 2) : 0;
        $stats['bounce_rate'] = $stats['sent'] > 0 ? round(($stats['bounced'] / $stats['sent']) * 100, 2) : 0;
        $stats['unsubscribe_rate'] = $stats['sent'] > 0 ? round(($stats['unsubscribed'] / $stats['sent']) * 100, 2) : 0;
        $stats['complaint_rate'] = $stats['sent'] > 0 ? round(($stats['complained'] / $stats['sent']) * 100, 2) : 0;
        $stats['click_to_open_rate'] = $stats['opened'] > 0 ? round(($stats['clicked'] / $stats['opened']) * 100, 2) : 0;

        // Get engagement timeline data for the chart
        $timeline = [];
        if ($emailCampaign->sent_at) {
            $startDate = $emailCampaign->sent_at->copy()->subDay();
            $endDate = now()->gt($emailCampaign->sent_at->copy()->addDays(14)) ?
                $emailCampaign->sent_at->copy()->addDays(14) : now();

            // Get daily counts
            $dailyOpens = $emailCampaign->recipients()
                ->select(DB::raw('DATE(opened_at) as date'), DB::raw('count(*) as count'))
                ->whereNotNull('opened_at')
                ->whereBetween('opened_at', [$startDate, $endDate])
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $dailyClicks = $emailCampaign->recipients()
                ->select(DB::raw('DATE(clicked_at) as date'), DB::raw('count(*) as count'))
                ->whereNotNull('clicked_at')
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            // Build timeline data
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $timeline[] = [
                    'date' => $dateStr,
                    'opens' => $dailyOpens[$dateStr] ?? 0,
                    'clicks' => $dailyClicks[$dateStr] ?? 0,
                    'label' => $currentDate->format('M j')
                ];
                $currentDate->addDay();
            }
        }

        // Get device and platform data
        $devices = $emailCampaign->recipients()
            ->select('device_type', DB::raw('count(*) as count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();

        $platforms = $emailCampaign->recipients()
            ->select('platform', DB::raw('count(*) as count'))
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        // Get top links clicked
        $topLinks = $emailCampaign->recipients()
            ->select('clicked_links')
            ->whereNotNull('clicked_links')
            ->get()
            ->flatMap(function($recipient) {
                return json_decode($recipient->clicked_links, true) ?? [];
            })
            ->groupBy('url')
            ->map(function($clicks, $url) {
                return [
                    'url' => $url,
                    'clicks' => count($clicks),
                    'unique_clicks' => collect($clicks)->unique('ip')->count()
                ];
            })
            ->sortByDesc('clicks')
            ->take(5)
            ->values();

        // Get recent activity
        $recentActivity = $emailCampaign->recipients()
            ->with('client')
            ->where(function($query) {
                $query->whereNotNull('opened_at')
                    ->orWhereNotNull('clicked_at')
                    ->orWhereNotNull('bounced_at')
                    ->orWhereNotNull('unsubscribed_at');
            })
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($recipient) {
                return [
                    'id' => $recipient->id,
                    'email' => $recipient->email,
                    'name' => $recipient->name,
                    'opened_at' => $recipient->opened_at,
                    'clicked_at' => $recipient->clicked_at,
                    'bounced_at' => $recipient->bounced_at,
                    'unsubscribed_at' => $recipient->unsubscribed_at,
                    'complained_at' => $recipient->complained_at,
                    'device_type' => $recipient->device_type,
                    'platform' => $recipient->platform,
                    'client' => $recipient->client ? [
                        'id' => $recipient->client->id,
                        'name' => $recipient->client->name,
                        'email' => $recipient->client->email
                    ] : null
                ];
            });

        return view('email-campaigns.show', [
            'campaign' => $emailCampaign,
            'stats' => $stats,
            'timeline' => $timeline,
            'devices' => $devices,
            'platforms' => $platforms,
            'topLinks' => $topLinks,
            'recentActivity' => $recentActivity,
            'canEdit' => $emailCampaign->status === 'draft',
            'canSend' => in_array($emailCampaign->status, ['draft', 'scheduled']),
            'canCancel' => in_array($emailCampaign->status, ['scheduled', 'sending']),
            'canDuplicate' => true,
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
    /**
     * Send the specified email campaign.
     *
     * @param  \App\Models\EmailCampaign  $campaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(EmailCampaign $campaign)
    {
        if (!$campaign->isDraft() && !$campaign->isScheduled()) {
            return redirect()->back()->with('error', 'Only draft or scheduled campaigns can be sent.');
        }

        // If scheduled for future, just update status
        if ($campaign->scheduled_for && $campaign->scheduled_for->isFuture()) {
            $campaign->update(['status' => 'scheduled']);
            return redirect()->back()->with('success', 'Campaign is scheduled for sending.');
        }

        // Otherwise, send immediately
        try {
            $this->queueCampaignEmails($campaign);
            $campaign->update([
                'status' => 'sending',
                'sent_at' => now()
            ]);

            return redirect()->back()->with('success', 'Campaign is being sent to recipients.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send campaign: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a scheduled email campaign.
     *
     * @param  \App\Models\EmailCampaign  $campaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(EmailCampaign $campaign)
    {
        if (!$campaign->isScheduled() && !$campaign->isSending()) {
            return redirect()->back()->with('error', 'Only scheduled or sending campaigns can be canceled.');
        }

        $campaign->update(['status' => 'cancelled']);

        // Here you would also want to cancel any queued jobs for this campaign
        // This would depend on your queue implementation

        return redirect()->back()->with('success', 'Campaign has been canceled.');
    }

    /**
     * Duplicate an existing email campaign.
     *
     * @param  \App\Models\EmailCampaign  $campaign
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate(EmailCampaign $campaign)
    {
        $newCampaign = $campaign->replicate();
        $newCampaign->name = 'Copy of ' . $campaign->name;
        $newCampaign->status = 'draft';
        $newCampaign->sent_at = null;
        $newCampaign->scheduled_for = null;
        $newCampaign->save();

        // Duplicate recipients
        $campaign->recipients->each(function ($recipient) use ($newCampaign) {
            $newRecipient = $recipient->replicate();
            $newRecipient->email_campaign_id = $newCampaign->id;
            $newRecipient->status = 'pending';
            $newRecipient->opened_at = null;
            $newRecipient->clicked_at = null;
            $newRecipient->bounced_at = null;
            $newRecipient->unsubscribed_at = null;
            $newRecipient->save();
        });

        return redirect()->route('admin.email-campaigns.edit', $newCampaign)
            ->with('success', 'Campaign duplicated successfully. You can now edit the new campaign.');
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
