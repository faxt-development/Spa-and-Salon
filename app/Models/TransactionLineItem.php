<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionLineItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Line item types
     */
    public const TYPE_SERVICE = 'service';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_TAX = 'tax';
    public const TYPE_TIP = 'tip';
    public const TYPE_DISCOUNT = 'discount';
    public const TYPE_GIFT_CARD = 'gift_card';
    public const TYPE_OTHER = 'other';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'item_type', // service, product, tax, tip, discount, gift_card, other
        'name',
        'description',
        'quantity',
        'unit_price',
        'amount', // total for this line item
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'itemable_type', // For polymorphic relation
        'itemable_id',   // For polymorphic relation
        'staff_id',      // For commission tracking
        'metadata',      // JSON field for additional data
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the transaction that owns the line item.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the staff member associated with this line item (for commission).
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the itemable model (service, product, etc.)
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Calculate the total amount for this line item.
     *
     * @return float
     */
    public function calculateAmount(): float
    {
        $baseAmount = $this->quantity * $this->unit_price;
        
        if ($this->item_type === self::TYPE_DISCOUNT) {
            return -abs($baseAmount); // Discounts are negative
        }
        
        return $baseAmount - $this->discount_amount;
    }

    /**
     * Calculate the taxable amount for this line item.
     *
     * @return float
     */
    public function calculateTaxableAmount(): float
    {
        if ($this->item_type === self::TYPE_TAX || $this->item_type === self::TYPE_TIP) {
            return 0; // Taxes and tips are not taxable
        }
        
        $baseAmount = $this->quantity * $this->unit_price;
        return max(0, $baseAmount - $this->discount_amount);
    }

    /**
     * Scope a query to only include items of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('item_type', $type);
    }

    /**
     * Scope a query to only include items for a specific staff member.
     */
    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }
}
