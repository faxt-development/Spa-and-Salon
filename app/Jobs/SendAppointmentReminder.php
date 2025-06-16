<?php

namespace App\Jobs;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminder implements ShouldQueue
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
     * The appointment instance.
     *
     * @var \App\Models\Appointment
     */
    protected $appointment;

    /**
     * The number of hours until the appointment.
     *
     * @var int
     */
    protected $hoursBefore;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Appointment  $appointment
     * @param  int  $hoursBefore
     * @return void
     */
    public function __construct(Appointment $appointment, int $hoursBefore)
    {
        $this->appointment = $appointment->withoutRelations();
        $this->hoursBefore = $hoursBefore;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Reload the appointment with relationships
        $appointment = $this->appointment->load(['client', 'service', 'staff']);
        
        // Double-check that the appointment hasn't been cancelled
        if ($appointment->is_cancelled) {
            $this->delete();
            return;
        }
        
        // Send the reminder email
        Mail::to($appointment->client->email)
            ->send(new AppointmentReminder($appointment, $this->hoursBefore));
            
        $this->appointment->touch('last_reminder_sent_at');
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
        \Log::error("Failed to send appointment reminder: " . $exception->getMessage(), [
            'appointment_id' => $this->appointment->id,
            'hours_before' => $this->hoursBefore,
            'exception' => $exception,
        ]);
    }
}
