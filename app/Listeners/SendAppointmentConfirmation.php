<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Mail\AppointmentConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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
            Mail::to($appointment->client->email)
                ->send(new AppointmentConfirmation($appointment));
        }
    }
}
