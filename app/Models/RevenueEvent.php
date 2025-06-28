<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueEvent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Revenue event types
     */
    public const TYPE_APPOINTMENT_COMPLETED = 'appointment_completed';
    public const TYPE_RETAIL_SALE = 'retail_sale';
    public const TYPE_GIFT_CARD_REDEMPTION = 'gift_card_redemption';
    public const TYPE_SUBSCRIPTION_PAYMENT = 'subscription_payment';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_OTHER = 'other';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'event_type',
        'event_date',
        'amount',
        'description',
        'source_type', // For polymorphic relation (appointment, order, etc.)
        'source_id',   // For polymorphic relation
        'staff_id',    // For commission tracking
        'payment_method_id', // For payment method tracking
        'metadata',    // JSON field for additional data
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date' => 'datetime',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the transaction associated with this revenue event.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the staff member associated with this revenue event.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
    
    /**
     * Get the payment method associated with this revenue event.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the source model (appointment, order, etc.)
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope a query to only include events for a specific staff member.
     */
    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope a query to only include events within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include positive revenue events.
     */
    public function scopePositiveRevenue($query)
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Scope a query to only include negative revenue events (refunds, etc.).
     */
    public function scopeNegativeRevenue($query)
    {
        return $query->where('amount', '<', 0);
    }
}
