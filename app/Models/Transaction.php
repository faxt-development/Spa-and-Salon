<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Transaction types
     */
    public const TYPE_APPOINTMENT = 'appointment';
    public const TYPE_RETAIL = 'retail';
    public const TYPE_GIFT_CARD = 'gift_card';
    public const TYPE_REFUND = 'refund';
    public const TYPE_OTHER = 'other';

    /**
     * Transaction statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_VOIDED = 'voided';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'staff_id',
        'room_id', // Service location
        'payment_method',
        'transaction_type',
        'reference_id', // Could be appointment_id, order_id, etc.
        'reference_type', // Polymorphic relation type
        'subtotal',
        'tax_amount',
        'tip_amount',
        'discount_amount',
        'total_amount',
        'status',
        'transaction_date',
        'notes',
        'payment_gateway',
        'card_last_four',
        'card_brand',
        'external_transaction_id',
        'parent_transaction_id', // For refunds
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the client associated with this transaction.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the staff member who processed this transaction.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the room (service location) where the transaction occurred.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the reference model (appointment, order, etc.)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get the line items for this transaction.
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(TransactionLineItem::class);
    }

    /**
     * Get the revenue events associated with this transaction.
     */
    public function revenueEvents(): HasMany
    {
        return $this->hasMany(RevenueEvent::class);
    }

    /**
     * Get the parent transaction (if this is a refund).
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Get the refund transactions for this transaction.
     */
    public function refundTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Check if the transaction is refundable.
     *
     * @return bool
     */
    public function isRefundable(): bool
    {
        return $this->status === self::STATUS_COMPLETED &&
               $this->status !== self::STATUS_REFUNDED &&
               $this->total_amount > 0;
    }

    /**
     * Get the total amount that can still be refunded.
     *
     * @return float
     */
    public function getRefundableAmount(): float
    {
        if (!$this->isRefundable()) {
            return 0;
        }

        $refundedAmount = $this->refundTransactions()->sum('total_amount');
        return max(0, $this->total_amount - $refundedAmount);
    }

    /**
     * Get the tax breakdown for this transaction.
     * 
     * @return array
     */
    public function getTaxBreakdown(): array
    {
        $taxBreakdown = [];
        $taxLineItems = $this->lineItems()->where('item_type', 'tax')->get();
        
        foreach ($taxLineItems as $item) {
            $taxBreakdown[] = [
                'name' => $item->name,
                'rate' => $item->tax_rate,
                'amount' => $item->amount,
            ];
        }
        
        return $taxBreakdown;
    }
}
