<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipDistribution extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'staff_id',
        'amount',
        'percentage',
        'is_processed',
        'processed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the transaction that owns the tip distribution.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the staff member who received the tip.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Mark the tip as processed.
     *
     * @param string|null $notes
     * @return bool
     */
    public function markAsProcessed(?string $notes = null): bool
    {
        return $this->update([
            'is_processed' => true,
            'processed_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
