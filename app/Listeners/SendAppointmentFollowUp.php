<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use App\Mail\AppointmentFollowUp;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendAppointmentFollowUp implements ShouldQueue
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
     * @param  \App\Events\AppointmentCompleted  $event
     * @return void
     */
    public function handle(AppointmentCompleted $event)
    {
        $appointment = $event->appointment->load('client');
        
        // Generate a unique token for this review
        $reviewToken = (string) Str::uuid();
        
        // Store the token with the appointment
        $appointment->update([
            'review_token' => $reviewToken,
            'review_token_expires_at' => now()->addDays(14), // Allow 2 weeks to leave a review
        ]);
        
        // Send the follow-up email
        Mail::to($appointment->client->email)
            ->queue(new \App\Mail\AppointmentFollowUp($appointment));
            
        // Log that we've sent this follow-up
        $appointment->notifications()->create([
            'type' => 'follow_up',
            'sent_at' => now(),
            'data' => [
                'review_token' => $reviewToken,
                'expires_at' => now()->addDays(14)->toDateTimeString(),
            ],
        ]);
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \App\Events\AppointmentCompleted  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(AppointmentCompleted $event, $exception)
    {
        // Log the failure
        \Log::error('Failed to send follow-up email: ' . $exception->getMessage(), [
            'appointment_id' => $event->appointment->id,
            'exception' => $exception,
        ]);
    }
}
