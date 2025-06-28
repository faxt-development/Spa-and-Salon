<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoyaltyTransaction extends Model
{
    const TYPE_EARN = 'earn';
    const TYPE_REDEEM = 'redeem';
    const TYPE_EXPIRE = 'expire';
    const TYPE_ADJUST = 'adjust';

    protected $fillable = [
        'loyalty_account_id',
        'type',
        'points',
        'points_value',
        'reference_type',
        'reference_id',
        'description',
        'expires_at',
        'reversed_at',
        'reversed_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'points_value' => 'decimal:2',
        'expires_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
