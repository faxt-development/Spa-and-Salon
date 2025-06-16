<?php

namespace App\Mail;

use App\Models\GiftCard;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GiftCardPurchased extends Mailable
{
    use Queueable, SerializesModels;

    public $giftCard;
    public $senderName;
    public $message;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\GiftCard  $giftCard
     * @param  string  $senderName
     * @param  string|null  $message
     * @return void
     */
    public function __construct(GiftCard $giftCard, string $senderName, ?string $message = null)
    {
        $this->giftCard = $giftCard;
        $this->senderName = $senderName;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("You've Received a Gift Card! 🎁")
                    ->view('emails.gift-card-purchased')
                    ->text('emails.gift-card-purchased-plain');
    }
}
