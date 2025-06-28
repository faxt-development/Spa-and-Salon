<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'points_per_currency',
        'currency_per_point',
        'signup_points',
        'is_active',
    ];

    protected $casts = [
        'points_per_currency' => 'decimal:2',
        'currency_per_point' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(LoyaltyAccount::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(LoyaltyTier::class)->orderBy('points_required');
    }
}
