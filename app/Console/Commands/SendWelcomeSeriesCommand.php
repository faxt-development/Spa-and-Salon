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

class SendWelcomeSeriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-welcome-series {--days=30 : Number of days to consider as new clients} {--limit=100 : Maximum number of emails to send}'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send welcome series drip campaign emails to new clients';

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
        $days = $this->option('days');
        $limit = $this->option('limit');
        
        $this->info("Processing welcome series for clients created in the last {$days} days...");
        
        // Get active welcome series campaigns ordered by sequence
        $campaigns = DripCampaign::where('type', DripCampaign::TYPE_WELCOME)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();
            
        if ($campaigns->isEmpty()) {
            $this->warn('No active welcome series campaigns found.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$campaigns->count()} active welcome series campaigns.");
        
        // Get new clients from the segmentation service
        $newClients = $this->segmentationService->getRecentClients($days, $limit);
        
        if ($newClients->isEmpty()) {
            $this->warn("No new clients found in the last {$days} days.");
            return Command::SUCCESS;
        }
        
        $this->info("Found {$newClients->count()} new clients to process.");
        
        $totalSent = 0;
        $bar = $this->output->createProgressBar($newClients->count());
        $bar->start();
        
        foreach ($newClients as $client) {
            // Process each campaign for this client
            foreach ($campaigns as $campaign) {
                // Check if this client already received this campaign
                $alreadySent = DripCampaignRecipient::where('client_id', $client->id)
                    ->where('drip_campaign_id', $campaign->id)
                    ->exists();
                    
                if ($alreadySent) {
                    continue; // Skip to the next campaign
                }
                
                // Check if client creation date + delay days is in the past
                $sendDate = Carbon::parse($client->created_at)->addDays($campaign->delay_days);
                
                if ($sendDate->isPast()) {
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
        $this->info("Welcome series processing completed. {$totalSent} emails queued for sending.");
        
        // Log the results
        Log::info("Welcome series drip campaign processed: {$totalSent} emails queued for sending.");
        
        return Command::SUCCESS;
    }
}
