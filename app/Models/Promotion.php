<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'start_date',
        'end_date',
        'is_active',
        'usage_limit',
        'usage_count',
        'code',
        'is_public',
        'applies_to_all_services',
        'applies_to_all_products',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'is_public' => 'boolean',
        'applies_to_all_services' => 'boolean',
        'applies_to_all_products' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_date',
        'end_date',
        'deleted_at',
    ];

    /**
     * Discount type constants.
     */
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed_amount';
    public const TYPE_BOGO = 'buy_one_get_one';
    public const TYPE_PACKAGE = 'package_deal';

    /**
     * The services that this promotion applies to.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * The usages of this promotion.
     */
    public function usages()
    {
        return $this->hasMany(PromotionUsage::class)->with('user');
    }
    
    /**
     * Scope a query to only include active promotions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $now = now();
        
        return $query->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
            })
            ->where(function($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('(SELECT COUNT(*) FROM promotion_usages WHERE promotion_id = promotions.id) < usage_limit');
            });
    }
    
    /**
     * Scope a query to only include public promotions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Check if the promotion is currently active.
     */
    public function isActive(): bool
    {
        return $this->status() === 'active';
    }
    
    /**
     * Get the current status of the promotion
     * 
     * @return string 'active'|'scheduled'|'expired'|'inactive'
     */
    public function status(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = now();
        
        if ($this->start_date && $this->start_date->gt($now)) {
            return 'scheduled';
        }
        
        if ($this->end_date && $this->end_date->lt($now)) {
            return 'expired';
        }
        
        if ($this->usage_limit !== null && $this->usages()->count() >= $this->usage_limit) {
            return 'expired';
        }
        
        return 'active';
    }

    /**
     * Calculate the discount amount for a given price.
     *
     * @param float $price
     * @return float
     */
    public function calculateDiscount(float $price): float
    {
        if (!$this->isActive()) {
            return 0.0;
        }

        if ($this->min_purchase_amount > 0 && $price < $this->min_purchase_amount) {
            return 0.0;
        }


        $discount = 0.0;

        switch ($this->discount_type) {
            case self::DISCOUNT_TYPE_PERCENTAGE:
                $discount = $price * ($this->discount_value / 100);
                break;
            case self::DISCOUNT_TYPE_FIXED:
                $discount = $this->discount_value;
                break;
            // Add more discount types as needed
        }

        // Apply maximum discount limit if set
        if ($this->max_discount_amount > 0 && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        // Ensure discount doesn't exceed the price
        return min($discount, $price);
    }

    /**
     * Increment the usage count of the promotion.
     *
     * @return bool
     */
    public function incrementUsage(): bool
    {
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        $this->increment('usage_count');
        return true;
    }
}
