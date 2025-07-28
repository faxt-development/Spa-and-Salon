<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'content',
        'from_email',
        'from_name',
        'status',
        'scheduled_for',
        'sent_at',
        'user_id',
        'type',
        'is_template',
        'is_readonly',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_template' => 'boolean',
        'is_readonly' => 'boolean',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * The possible campaign types.
     *
     * @var array
     */
    public const TYPES = [
        'welcome',
        'promotional',
        'transactional',
        'newsletter',
        'other',
    ];

    /**
     * The possible status values for the campaign.
     *
     * @var array
     */
    public const STATUSES = [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'sending' => 'Sending',
        'sent' => 'Sent',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Get the user that created the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipients for the campaign.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    /**
     * Scope a query to only include active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Scope a query to only include scheduled campaigns that are ready to be sent.
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_for', '<=', now())
            ->whereDoesntHave('recipients', function ($query) {
                $query->where('status', '!=', 'pending');
            });
    }
}
