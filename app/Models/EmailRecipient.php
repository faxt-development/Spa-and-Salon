<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRecipient extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_campaign_id',
        'client_id',
        'email',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'complained_at',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'complained_at' => 'datetime',
    ];

    /**
     * The possible status values for the recipient.
     *
     * @var array
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'sending' => 'Sending',
        'sent' => 'Sent',
        'opened' => 'Opened',
        'clicked' => 'Clicked',
        'bounced' => 'Bounced',
        'complained' => 'Complained',
        'failed' => 'Failed',
    ];

    /**
     * Get the campaign that owns the recipient.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    /**
     * Get the client that owns the recipient.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Mark the email as sent.
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark the email as opened.
     */
    public function markAsOpened()
    {
        if (!$this->opened_at) {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Mark the email as clicked.
     */
    public function markAsClicked()
    {
        if (!$this->clicked_at) {
            $this->update([
                'status' => 'clicked',
                'clicked_at' => now(),
            ]);
        }
    }

    /**
     * Mark the email as bounced.
     */
    public function markAsBounced(string $message = null)
    {
        $this->update([
            'status' => 'bounced',
            'bounced_at' => now(),
            'error_message' => $message,
        ]);
    }

    /**
     * Mark the email as complained.
     */
    public function markAsComplained()
    {
        $this->update([
            'status' => 'complained',
            'complained_at' => now(),
        ]);
    }

    /**
     * Mark the email as failed.
     */
    public function markAsFailed(string $message)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $message,
        ]);
    }
}
