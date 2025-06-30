<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueSnapshot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'snapshot_date',
        'amount',
        'location_id',
        'breakdown',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'snapshot_date' => 'date',
        'amount' => 'decimal:2',
        'breakdown' => 'array',
    ];

    /**
     * Get the location that owns the revenue snapshot.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
