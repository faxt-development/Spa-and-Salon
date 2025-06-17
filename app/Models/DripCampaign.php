<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripCampaign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
        'configuration',
        'delay_days',
        'subject',
        'content',
        'from_email',
        'from_name',
        'reply_to',
        'preview_text',
        'sequence_order',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Campaign types
     */
    const TYPE_WELCOME = 'welcome_series';
    const TYPE_BIRTHDAY = 'birthday_promotion';
    const TYPE_REENGAGEMENT = 'reengagement';

    /**
     * Get the user who created the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipients for this campaign.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(DripCampaignRecipient::class);
    }

    /**
     * Check if this is a welcome series campaign.
     */
    public function isWelcomeSeries(): bool
    {
        return $this->type === self::TYPE_WELCOME;
    }

    /**
     * Check if this is a birthday promotion campaign.
     */
    public function isBirthdayPromotion(): bool
    {
        return $this->type === self::TYPE_BIRTHDAY;
    }

    /**
     * Check if this is a reengagement campaign.
     */
    public function isReengagement(): bool
    {
        return $this->type === self::TYPE_REENGAGEMENT;
    }
}
