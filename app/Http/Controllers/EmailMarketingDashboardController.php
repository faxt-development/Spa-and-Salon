<?php

namespace App\Http\Controllers;

use App\Models\DripCampaign;
use App\Models\DripCampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailMarketingDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-marketing');
    }

    /**
     * Display the email marketing dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get overall email marketing stats
        $stats = $this->getOverallStats();
        
        // Get recent campaigns
        $recentCampaigns = EmailCampaign::with('user')
            ->latest()
            ->take(5)
            ->get();
            
        // Get recent drip campaigns
        $recentDripCampaigns = DripCampaign::with('user')
            ->latest()
            ->take(5)
            ->get();
            
        // Get performance over time (last 30 days)
        $dailyStats = $this->getDailyStats();
        
        return view('email-marketing.dashboard', [
            'stats' => $stats,
            'recentCampaigns' => $recentCampaigns,
            'recentDripCampaigns' => $recentDripCampaigns,
            'dailyStats' => $dailyStats,
        ]);
    }
    
    /**
     * Get overall email marketing statistics.
     *
     * @return array
     */
    private function getOverallStats()
    {
        // Regular campaign stats
        $campaignStats = [
            'total' => EmailCampaign::count(),
            'active' => EmailCampaign::where('is_active', true)->count(),
            'sent' => EmailRecipient::whereNotNull('sent_at')->count(),
            'opened' => EmailRecipient::whereNotNull('opened_at')->count(),
            'clicked' => EmailRecipient::whereNotNull('clicked_at')->count(),
            'unsubscribed' => EmailRecipient::whereNotNull('unsubscribed_at')->count(),
        ];
        
        // Drip campaign stats
        $dripStats = [
            'total' => DripCampaign::count(),
            'active' => DripCampaign::where('is_active', true)->count(),
            'sent' => DripCampaignRecipient::whereNotNull('sent_at')->count(),
            'opened' => DripCampaignRecipient::whereNotNull('opened_at')->count(),
            'clicked' => DripCampaignRecipient::whereNotNull('clicked_at')->count(),
            'unsubscribed' => DripCampaignRecipient::whereNotNull('unsubscribed_at')->count(),
        ];
        
        // Combined stats
        $totalSent = $campaignStats['sent'] + $dripStats['sent'];
        $totalOpened = $campaignStats['opened'] + $dripStats['opened'];
        $totalClicked = $campaignStats['clicked'] + $dripStats['clicked'];
        $totalUnsubscribed = $campaignStats['unsubscribed'] + $dripStats['unsubscribed'];
        
        // Calculate rates
        $stats = [
            'campaigns' => $campaignStats,
            'drip' => $dripStats,
            'total_sent' => $totalSent,
            'total_opened' => $totalOpened,
            'total_clicked' => $totalClicked,
            'total_unsubscribed' => $totalUnsubscribed,
            'open_rate' => $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 2) : 0,
            'click_rate' => $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 2) : 0,
            'click_to_open_rate' => $totalOpened > 0 ? round(($totalClicked / $totalOpened) * 100, 2) : 0,
            'unsubscribe_rate' => $totalSent > 0 ? round(($totalUnsubscribed / $totalSent) * 100, 2) : 0,
            'subscribers' => Client::whereNull('unsubscribed_at')->count(),
            'unsubscribers' => Client::whereNotNull('unsubscribed_at')->count(),
        ];
        
        return $stats;
    }
    
    /**
     * Get daily email statistics for the last 30 days.
     *
     * @return array
     */
    private function getDailyStats()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Get daily sent counts (regular campaigns)
        $dailySent = EmailRecipient::select(
                DB::raw('DATE(sent_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $startDate)
            ->where('sent_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(sent_at)'))
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        // Get daily sent counts (drip campaigns)
        $dailyDripSent = DripCampaignRecipient::select(
                DB::raw('DATE(sent_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $startDate)
            ->where('sent_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(sent_at)'))
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        // Get daily opened counts (regular campaigns)
        $dailyOpened = EmailRecipient::select(
                DB::raw('DATE(opened_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('opened_at')
            ->where('opened_at', '>=', $startDate)
            ->where('opened_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(opened_at)'))
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        // Get daily opened counts (drip campaigns)
        $dailyDripOpened = DripCampaignRecipient::select(
                DB::raw('DATE(opened_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('opened_at')
            ->where('opened_at', '>=', $startDate)
            ->where('opened_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(opened_at)'))
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        // Combine the data for each date
        $dates = [];
        $current = clone $startDate;
        
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dates[$dateStr] = [
                'date' => $dateStr,
                'sent' => ($dailySent[$dateStr] ?? 0) + ($dailyDripSent[$dateStr] ?? 0),
                'opened' => ($dailyOpened[$dateStr] ?? 0) + ($dailyDripOpened[$dateStr] ?? 0),
            ];
            $current->addDay();
        }
        
        return array_values($dates);
    }
}
