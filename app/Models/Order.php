<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use SoftDeletes;

    use HasFactory;
    
    // Order status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    
    // Tax calculation modes
    public const TAX_CALCULATION_ORDER_LEVEL = 'order_level';
    public const TAX_CALCULATION_ITEM_LEVEL = 'item_level';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'staff_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the client that owns the order.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the staff member that processed the order.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for the order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Calculate and apply taxes to the order.
     * 
     * @param string $calculationMode Either 'order_level' or 'item_level'
     * @return $this
     */
    /**
     * Calculate and apply taxes to the order.
     * 
     * @param string $calculationMode Either 'order_level' or 'item_level'
     * @return $this
     */
    public function calculateTaxes(string $calculationMode = self::TAX_CALCULATION_ITEM_LEVEL): self
    {
        $taxService = app(\App\Services\TaxCalculationService::class);
        return $taxService->calculateOrderTaxes($this, $calculationMode);
    }
    
    /**
     * Get applicable tax rates for an order or order item.
     * 
     * @param  \App\Models\OrderItem|null  $item
     * @return \Illuminate\Database\Eloquent\Collection
     */
    /**
     * Get applicable tax rates for an order or order item.
     * 
     * @deprecated Use TaxCalculationService directly for better separation of concerns
     * @param  \App\Models\OrderItem|null  $item
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getApplicableTaxRates(OrderItem $item = null)
    {
        $taxService = app(\App\Services\TaxCalculationService::class);
        
        if ($item) {
            return $taxService->getApplicableTaxRatesForItem($item);
        }
        
        return $taxService->getApplicableTaxRatesForOrder($this);
    }
    
    /**
     * Get the total tax amount for the order.
     * 
     * @return float
     */
    public function getTotalTaxAttribute(): float
    {
        return (float) ($this->tax_amount ?? 0);
    }
    
    /**
     * Get the total amount before tax.
     * 
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->attributes['subtotal'] ?? $this->items->sum('subtotal'));
    }
    
    /**
     * Get the total discount amount.
     * 
     * @return float
     */
    public function getTotalDiscountAttribute(): float
    {
        return (float) ($this->discount_amount ?? $this->items->sum('discount'));
    }
    
    /**
     * Get the total amount including tax and after discounts.
     * 
     * @return float
     */
    public function getTotalAmountAttribute(): float
    {
        if (isset($this->attributes['total_amount'])) {
            return (float) $this->attributes['total_amount'];
        }
        
        return $this->subtotal + $this->total_tax - $this->total_discount;
    }
    
    /**
     * Get the tax breakdown for this order.
     * 
     * @return array
     */
    public function getTaxBreakdown(): array
    {
        $taxService = app(\App\Services\TaxCalculationService::class);
        return $taxService->getTaxBreakdown($this);
    }
}
