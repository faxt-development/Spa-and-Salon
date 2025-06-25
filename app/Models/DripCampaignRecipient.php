<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripCampaignRecipient extends Model
{
    use SoftDeletes;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'drip_campaign_id',
        'client_id',
        'email',
        'name',
        'token',
        'unsubscribe_token',
        'preferences_token',
        'sent_at',
        'opened_at',
        'clicked_at',
        'unsubscribed_at',
        'ip_address',
        'user_agent',
        'last_clicked_url',
        'unsubscribed_ip',
        'merge_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'merge_data' => 'array',
    ];

    /**
     * Get the drip campaign this recipient belongs to.
     */
    public function dripCampaign(): BelongsTo
    {
        return $this->belongsTo(DripCampaign::class);
    }

    /**
     * Get the client this recipient represents.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Check if the recipient has opened the email.
     */
    public function hasOpened(): bool
    {
        return $this->opened_at !== null;
    }

    /**
     * Check if the recipient has clicked any link in the email.
     */
    public function hasClicked(): bool
    {
        return $this->clicked_at !== null;
    }

    /**
     * Check if the recipient has unsubscribed.
     */
    public function hasUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }
}
