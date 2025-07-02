<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The temporary password for the user.
     *
     * @var string|null
     */
    public $temporaryPassword;

    /**
     * The onboarding URL for the user.
     *
     * @var string|null
     */
    public $onboardingUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?string $temporaryPassword = null, ?string $onboardingUrl = null)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->onboardingUrl = $onboardingUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Faxtina - Your Account Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-new-user',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
