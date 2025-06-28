<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $fillable = [
        'client_id',
        'loyalty_program_id',
        'points_balance',
        'points_earned_lifetime',
        'points_redeemed_lifetime',
        'tier',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'loyalty_program_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function currentTier()
    {
        if (!$this->program) {
            return null;
        }

        return $this->program->tiers()
            ->where('points_required', '<=', $this->points_balance)
            ->orderByDesc('points_required')
            ->first();
    }
}
