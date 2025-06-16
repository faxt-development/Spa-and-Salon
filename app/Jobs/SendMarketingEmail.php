<?php

namespace App\Jobs;

use App\Mail\MarketingEmail;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMarketingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [60, 300, 600];

    /**
     * The email campaign instance.
     *
     * @var \App\Models\EmailCampaign
     */
    protected $campaign;

    /**
     * The email recipient instance.
     *
     * @var \App\Models\EmailRecipient
     */
    protected $recipient;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\EmailCampaign  $campaign
     * @param  \App\Models\EmailRecipient  $recipient
     * @return void
     */
    public function __construct(EmailCampaign $campaign, EmailRecipient $recipient)
    {
        $this->campaign = $campaign->withoutRelations();
        $this->recipient = $recipient->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Reload the models with their relationships
        $campaign = $this->campaign->fresh();
        $recipient = $this->recipient->fresh();
        
        // Check if the email has already been sent or if the campaign is cancelled
        if ($recipient->status !== 'pending' || $campaign->status === 'cancelled') {
            return;
        }
        
        try {
            // Update status to sending
            $recipient->update(['status' => 'sending']);
            
            // Send the email
            Mail::to($recipient->email)
                ->send(new MarketingEmail(
                    $recipient,
                    $campaign->subject,
                    $campaign->content
                ));
            
            // Mark as sent
            $recipient->markAsSent();
            
            // Update campaign status if all emails are sent
            $this->updateCampaignStatusIfComplete($campaign);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to send marketing email: ' . $e->getMessage(), [
                'recipient_id' => $recipient->id,
                'campaign_id' => $campaign->id,
                'exception' => $e,
            ]);
            
            // Mark as failed
            $recipient->markAsFailed($e->getMessage());
            
            // Re-throw to allow for retries
            throw $e;
        }
    }
    
    /**
     * Update the campaign status if all emails have been sent.
     *
     * @param  \App\Models\EmailCampaign  $campaign
     * @return void
     */
    protected function updateCampaignStatusIfComplete(EmailCampaign $campaign)
    {
        $pendingCount = $campaign->recipients()
            ->where('status', 'pending')
            ->count();
            
        if ($pendingCount === 0) {
            $campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // Log the failure
        Log::error('Marketing email job failed: ' . $exception->getMessage(), [
            'campaign_id' => $this->campaign->id,
            'recipient_id' => $this->recipient->id,
            'exception' => $exception,
        ]);
        
        // Mark as failed if not already sent
        $recipient = $this->recipient->fresh();
        if ($recipient && $recipient->status !== 'sent') {
            $recipient->markAsFailed($exception->getMessage());
        }
    }
}
