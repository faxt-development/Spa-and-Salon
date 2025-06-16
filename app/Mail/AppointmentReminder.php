<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The appointment instance.
     *
     * @var \App\Models\Appointment
     */
    public $appointment;

    /**
     * The number of hours until the appointment.
     *
     * @var int
     */
    public $hoursUntilAppointment;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Appointment  $appointment
     * @param  int  $hoursUntilAppointment
     * @return void
     */
    public function __construct(Appointment $appointment, int $hoursUntilAppointment = 24)
    {
        $this->appointment = $appointment->load(['client', 'service', 'staff']);
        $this->hoursUntilAppointment = $hoursUntilAppointment;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $timePhrase = $this->hoursUntilAppointment <= 24 
            ? 'Tomorrow' 
            : 'in ' . ($this->hoursUntilAppointment / 24) . ' days';
            
        return new Envelope(
            subject: 'Reminder: ' . $this->appointment->service->name . ' ' . $timePhrase,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.appointments.reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
