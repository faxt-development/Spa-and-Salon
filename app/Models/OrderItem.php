<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    use SoftDeletes;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'itemable_id',
        'itemable_type',
        'service_category_id',
        'name',
        'quantity',
        'unit_price',
        'discount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'service_category_id' => 'integer',
    ];

    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the owning itemable model (product or service).
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Get the service category for this order item.
     */
    public function serviceCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }
    
    /**
     * Calculate the subtotal for this order item.
     */
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }
    
    /**
     * Calculate the taxable amount for this order item.
     */
    public function calculateTaxableAmount(): float
    {
        $subtotal = $this->calculateSubtotal();
        return max(0, $subtotal - $this->discount);
    }
    
    /**
     * Calculate the tax amount for this order item.
     * 
     * @param bool $forceRecalculation If true, will recalculate tax even if it's already set
     * @return float
     */
    public function calculateTaxAmount(bool $forceRecalculation = false): float
    {
        if (!$forceRecalculation && $this->tax_amount !== null) {
            return (float) $this->tax_amount;
        }
        
        if ($this->order) {
            $taxRates = $this->order->getApplicableTaxRates($this);
            $taxableAmount = $this->calculateTaxableAmount();
            $taxAmount = 0;
            
            foreach ($taxRates as $taxRate) {
                $taxAmount += $taxRate->calculateTax($taxableAmount);
            }
            
            return $taxAmount;
        }
        
        return 0;
    }
    
    /**
     * Calculate the total for this order item including tax and after discounts.
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        $taxAmount = $this->calculateTaxAmount();
        return ($subtotal - $this->discount) + $taxAmount;
    }
    
    /**
     * Get the formatted price with currency symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->unit_price, 'USD');
    }
    
    /**
     * Get the formatted subtotal (price × quantity).
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->calculateSubtotal(), 'USD');
    }
    
    /**
     * Get the formatted tax amount.
     */
    public function getFormattedTaxAmountAttribute(): string
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->tax_amount, 'USD');
    }
    
    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->calculateTotal(), 'USD');
    }
    
    /**
     * Get the tax rate as a percentage.
     */
    public function getTaxRatePercentageAttribute(): string
    {
        return number_format($this->tax_rate, 2) . '%';
    }
    
    /**
     * Scope a query to only include items with a specific itemable type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('itemable_type', $type);
    }
    
    /**
     * Scope a query to only include items with tax applied.
     */
    public function scopeWithTax($query)
    {
        return $query->where('tax_amount', '>', 0);
    }
}
