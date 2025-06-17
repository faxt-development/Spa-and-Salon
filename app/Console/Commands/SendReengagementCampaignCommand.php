<?php

namespace App\Console\Commands;

use App\Models\DripCampaign;
use App\Models\Client;
use App\Models\DripCampaignRecipient;
use App\Services\EmailSegmentationService;
use App\Jobs\SendDripCampaignEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SendReengagementCampaignCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-reengagement-campaign {--min-days=90 : Minimum days of inactivity} {--max-days=365 : Maximum days of inactivity} {--limit=100 : Maximum number of emails to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send re-engagement drip campaign emails to inactive clients';

    /**
     * The segmentation service instance.
     *
     * @var \App\Services\EmailSegmentationService
     */
    protected $segmentationService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\EmailSegmentationService  $segmentationService
     * @return void
     */
    public function __construct(EmailSegmentationService $segmentationService)
    {
        parent::__construct();
        $this->segmentationService = $segmentationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $minDays = $this->option('min-days');
        $maxDays = $this->option('max-days');
        $limit = $this->option('limit');
        
        $this->info("Processing re-engagement campaigns for clients inactive between {$minDays} and {$maxDays} days...");
        
        // Get active re-engagement campaigns ordered by sequence
        $campaigns = DripCampaign::where('type', DripCampaign::TYPE_REENGAGEMENT)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();
            
        if ($campaigns->isEmpty()) {
            $this->warn('No active re-engagement campaigns found.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$campaigns->count()} active re-engagement campaigns.");
        
        // Get inactive clients from the segmentation service
        $inactiveClients = $this->segmentationService->getInactiveClients($minDays, $maxDays, $limit);
        
        if ($inactiveClients->isEmpty()) {
            $this->warn("No inactive clients found between {$minDays} and {$maxDays} days.");
            return Command::SUCCESS;
        }
        
        $this->info("Found {$inactiveClients->count()} inactive clients to process.");
        
        $totalSent = 0;
        $bar = $this->output->createProgressBar($inactiveClients->count());
        $bar->start();
        
        foreach ($inactiveClients as $client) {
            // Process each campaign for this client
            foreach ($campaigns as $campaign) {
                // Check if this client already received this campaign in the last 90 days
                $alreadySent = DripCampaignRecipient::where('client_id', $client->id)
                    ->where('drip_campaign_id', $campaign->id)
                    ->where('created_at', '>', now()->subDays(90))
                    ->exists();
                    
                if ($alreadySent) {
                    continue; // Skip to the next campaign
                }
                
                // Calculate days since last visit
                $lastVisit = Carbon::parse($client->last_appointment_date ?? $client->created_at);
                $daysSinceLastVisit = $lastVisit->diffInDays(now());
                
                // If we should send the email now based on the campaign's delay_days
                if ($daysSinceLastVisit >= $minDays && $daysSinceLastVisit <= $maxDays) {
                    // Create a unique tracking token
                    $token = Str::random(64);
                    $unsubscribeToken = Str::random(64);
                    $preferencesToken = Str::random(64);
                    
                    // Create recipient record
                    $recipient = new DripCampaignRecipient([
                        'drip_campaign_id' => $campaign->id,
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
                            'days_since_last_visit' => $daysSinceLastVisit,
                            'last_visit_date' => $lastVisit->format('F j, Y'),
                        ],
                    ]);
                    
                    $recipient->save();
                    
                    // Queue the email for sending
                    SendDripCampaignEmail::dispatch($recipient);
                    $totalSent++;
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("Re-engagement campaign processing completed. {$totalSent} emails queued for sending.");
        
        // Log the results
        Log::info("Re-engagement drip campaign processed: {$totalSent} emails queued for sending.");
        
        return Command::SUCCESS;
    }
}
