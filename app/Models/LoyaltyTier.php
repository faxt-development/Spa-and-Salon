<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTier extends Model
{
    protected $fillable = [
        'loyalty_program_id',
        'name',
        'points_required',
        'multiplier',
        'benefits',
        'priority',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'multiplier' => 'decimal:2',
        'benefits' => 'array',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'loyalty_program_id');
    }
}
