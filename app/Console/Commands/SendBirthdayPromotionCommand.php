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

class SendBirthdayPromotionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-birthday-promotion {--days=30 : Number of days in advance to send birthday promotions} {--limit=100 : Maximum number of emails to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday promotion drip campaign emails to clients with upcoming birthdays';

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
        
        $this->info("Processing birthday promotions for clients with birthdays in the next {$days} days...");
        
        // Get active birthday promotion campaigns ordered by sequence
        $campaigns = DripCampaign::where('type', DripCampaign::TYPE_BIRTHDAY)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();
            
        if ($campaigns->isEmpty()) {
            $this->warn('No active birthday promotion campaigns found.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$campaigns->count()} active birthday promotion campaigns.");
        
        // Get clients with upcoming birthdays from the segmentation service
        $birthdayClients = $this->segmentationService->getBirthdayClients($days, $limit);
        
        if ($birthdayClients->isEmpty()) {
            $this->warn("No clients with birthdays in the next {$days} days found.");
            return Command::SUCCESS;
        }
        
        $this->info("Found {$birthdayClients->count()} clients with upcoming birthdays to process.");
        
        $totalSent = 0;
        $bar = $this->output->createProgressBar($birthdayClients->count());
        $bar->start();
        
        foreach ($birthdayClients as $client) {
            // Process each campaign for this client
            foreach ($campaigns as $campaign) {
                // Check if this client already received this campaign this year
                $alreadySent = DripCampaignRecipient::where('client_id', $client->id)
                    ->where('drip_campaign_id', $campaign->id)
                    ->whereYear('created_at', now()->year)
                    ->exists();
                    
                if ($alreadySent) {
                    continue; // Skip to the next campaign
                }
                
                // Calculate days until birthday
                $birthday = Carbon::parse($client->birthday);
                $nextBirthday = $birthday->copy()->year(now()->year);
                
                // If birthday has already passed this year, use next year's birthday
                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }
                
                $daysUntilBirthday = now()->diffInDays($nextBirthday, false);
                
                // If we should send the email now based on the campaign's delay_days
                if ($daysUntilBirthday <= $days && $daysUntilBirthday >= $campaign->delay_days) {
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
                            'birthday' => $birthday->format('F j'),
                            'days_until_birthday' => $daysUntilBirthday,
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
        $this->info("Birthday promotion processing completed. {$totalSent} emails queued for sending.");
        
        // Log the results
        Log::info("Birthday promotion drip campaign processed: {$totalSent} emails queued for sending.");
        
        return Command::SUCCESS;
    }
}
