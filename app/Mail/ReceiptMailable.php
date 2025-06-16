<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $businessName;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['items', 'client', 'items.serviceEmployee']);
        $this->businessName = config('app.name');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Receipt from ' . $this->businessName . ' - Order #' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt',
            with: [
                'order' => $this->order,
            ]
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(
            config('mail.from.address'),
            config('mail.from.name')
        )->view('emails.receipt', ['order' => $this->order]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // You can add attachments here if needed
        // Example:
        // return [
        //     Attachment::fromPath('/path/to/file.pdf')
        //             ->as('receipt.pdf')
        //             ->withMime('application/pdf'),
        // ];
        return [];
    }
}
