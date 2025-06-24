<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GiftCard extends Model
{
    //use SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'amount',
        'balance',
        'recipient_name',
        'recipient_email',
        'sender_name',
        'message',
        'expires_at',
        'is_active',
        'is_redeemed',
        'redeemed_at',
        'redeemed_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'is_redeemed' => 'boolean',
        'redeemed_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
        'redeemed_at',
        'deleted_at',
    ];

    /**
     * Get the payments associated with the gift card.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    /**
     * Check if the gift card is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Check if the gift card has available balance.
     *
     * @return bool
     */
    public function hasAvailableBalance(): bool
    {
        return $this->balance > 0 && !$this->isExpired() && $this->is_active && !$this->is_redeemed;
    }
}
