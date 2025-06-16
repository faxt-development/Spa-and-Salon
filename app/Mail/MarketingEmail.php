<?php

namespace App\Mail;

use App\Models\EmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The email recipient instance.
     *
     * @var \App\Models\EmailRecipient
     */
    public $recipient;

    /**
     * The email subject.
     *
     * @var string
     */
    public $subject;

    /**
     * The email content.
     *
     * @var string
     */
    public $content;

    /**
     * The tracking token for this email.
     *
     * @var string
     */
    public $trackingToken;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\EmailRecipient  $recipient
     * @param  string  $subject
     * @param  string  $content
     * @return void
     */
    public function __construct(EmailRecipient $recipient, string $subject, string $content)
    {
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->content = $content;
        $this->trackingToken = $recipient->tracking_token ?? (string) \Illuminate\Support\Str::uuid();
        
        // Store the tracking token if not already set
        if (!$recipient->tracking_token) {
            $recipient->update(['tracking_token' => $this->trackingToken]);
        }
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        // Generate tracking pixel for open tracking
        $trackingPixelUrl = route('email.track.open', ['token' => $this->trackingToken]);
        
        // Process content to add tracking to links
        $processedContent = $this->processLinks($this->content, $this->trackingToken);
        
        // Add tracking pixel to the email
        $processedContent .= "\n<img src=\"$trackingPixelUrl\" alt=\"\" style=\"width:1px;height:1px;border:0;\">";
        
        return new Content(
            view: 'emails.marketing',
            with: [
                'content' => $processedContent,
                'unsubscribeUrl' => route('email.unsubscribe', ['token' => $this->trackingToken]),
                'preferencesUrl' => route('email.preferences', ['token' => $this->trackingToken]),
            ],
        );
    }

    /**
     * Process links in the content to add tracking.
     *
     * @param  string  $content
     * @param  string  $trackingToken
     * @return string
     */
    protected function processLinks(string $content, string $trackingToken): string
    {
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/ims';
        
        return preg_replace_callback($pattern, function($matches) use ($trackingToken) {
            $url = $matches[2];
            
            // Don't track unsubscribe or preference links
            if (str_contains($url, 'unsubscribe') || str_contains($url, 'preferences')) {
                return $matches[0];
            }
            
            // Add tracking to the URL
            $separator = (parse_url($url, PHP_URL_QUERY) == null) ? '?' : '&';
            $trackedUrl = url(route('email.track.click', [
                'token' => $trackingToken,
                'url' => urlencode($url),
            ]));
            
            return str_replace($url, $trackedUrl, $matches[0]);
        }, $content);
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
