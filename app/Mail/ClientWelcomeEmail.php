<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Client $client;
    public array $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(Client $client, array $emailData)
    {
        $this->client = $client;
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->emailData['from_email'] ?? config('mail.from.address'),
            subject: $this->emailData['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.client-welcome',
            with: [
                'client' => $this->client,
                'content' => $this->emailData['content'],
                'company' => $this->client->company,
            ],
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
