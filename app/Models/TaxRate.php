<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'is_active',
        'is_inclusive',
        'description',
        'applies_to',
        'effective_from',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'is_inclusive' => 'boolean',
        'applies_to' => 'array',
        'effective_from' => 'datetime',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
        'is_inclusive' => false,
        'type' => 'sales',
    ];

    /**
     * Scope a query to only include active tax rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $now = now();
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', $now);
            })
            ->where(function($q) {
                $now = now();
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', $now);
            });
    }

    /**
     * Scope a query to only include tax rates of a given type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Calculate tax for a given amount.
     */
    public function calculateTax(float $amount): float
    {
        return round($amount * ($this->rate / 100), 2);
    }

    /**
     * Get the tax amount for a given amount (alias for calculateTax).
     */
    public function getTaxAmount(float $amount): float
    {
        return $this->calculateTax($amount);
    }

    /**
     * Check if this tax rate applies to a specific product or service.
     */
    public function appliesTo($productId, $categoryId = null): bool
    {
        if (empty($this->applies_to)) {
            return true; // Applies to all if no specific products/categories are specified
        }

        $appliesTo = $this->applies_to;
        
        // Check if product ID is in the applies_to array
        if (in_array('product_' . $productId, $appliesTo)) {
            return true;
        }
        
        // Check if category ID is in the applies_to array
        if ($categoryId && in_array('category_' . $categoryId, $appliesTo)) {
            return true;
        }
        
        return false;
    }
}
