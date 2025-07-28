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
    public $temporaryPassword;
    public $onboardingUrl;
    public $customSubject;
    public $customContent;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?string $temporaryPassword = null, ?string $onboardingUrl = null, ?string $customSubject = null, ?string $customContent = null)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->onboardingUrl = $onboardingUrl;
        $this->customSubject = $customSubject;
        $this->customContent = $customContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject ?? 'Welcome to Faxtina - Your Account Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if ($this->customContent) {
            return new Content(
                htmlString: $this->customContent
            );
        }
        
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
