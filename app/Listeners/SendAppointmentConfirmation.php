<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Mail\AppointmentConfirmation;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAppointmentConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'emails';

    /**
     * Handle the event.
     *
     * @param  \App\Events\AppointmentCreated  $event
     * @return void
     */
    public function handle(AppointmentCreated $event)
    {
        $appointment = $event->appointment;
        
        // Only send confirmation if the appointment is not cancelled
        if (!$appointment->is_cancelled) {
            $client = $appointment->client;
            
            // Send the confirmation email
            Mail::to($client->email)
                ->send(new AppointmentConfirmation($appointment));
            
            // Track the email as sent for marketing purposes
            $this->trackMarketingEmail($client, $appointment);
            
            // Add client to marketing list if they have consented
            $this->addToMarketingList($client);
            
            Log::info('Appointment confirmation email sent and tracked for marketing', [
                'appointment_id' => $appointment->id,
                'client_id' => $client->id,
                'email' => $client->email
            ]);
        }
    }
    
    /**
     * Track the confirmation email as sent for marketing purposes
     *
     * @param \App\Models\Client $client
     * @param \App\Models\Appointment $appointment
     * @return void
     */
    protected function trackMarketingEmail($client, $appointment)
    {
        // Find or create a default booking confirmation campaign
        $campaign = EmailCampaign::firstOrCreate(
            [
                'name' => 'Booking Confirmation Emails',
                'type' => 'transactional',
            ],
            [
                'subject' => 'Appointment Confirmed',
                'status' => 'active',
                'is_system' => true,
            ]
        );
        
        // Create email recipient record for tracking
        EmailRecipient::create([
            'email_campaign_id' => $campaign->id,
            'email' => $client->email,
            'client_id' => $client->id,
            'status' => 'sent',
            'sent_at' => now(),
            'token' => str()->uuid(),
        ]);
    }
    
    /**
     * Add client to marketing list if they have consented
     *
     * @param \App\Models\Client $client
     * @return void
     */
    protected function addToMarketingList($client)
    {
        // Only add to marketing if client has given consent
        if ($client->marketing_consent) {
            // Find or create a general marketing campaign
            $marketingCampaign = EmailCampaign::firstOrCreate(
                [
                    'name' => 'General Marketing',
                    'type' => 'marketing',
                ],
                [
                    'subject' => 'Marketing Communications',
                    'status' => 'active',
                    'is_system' => true,
                ]
            );
            
            // Check if client is already in this marketing campaign
            $existingRecipient = EmailRecipient::where('email_campaign_id', $marketingCampaign->id)
                ->where('email', $client->email)
                ->first();
                
            if (!$existingRecipient) {
                EmailRecipient::create([
                    'email_campaign_id' => $marketingCampaign->id,
                    'email' => $client->email,
                    'client_id' => $client->id,
                    'status' => 'subscribed',
                    'token' => str()->uuid(),
                ]);
                
                Log::info('Client added to marketing list', [
                    'client_id' => $client->id,
                    'email' => $client->email
                ]);
            }
        }
    }
}
