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
    public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    public const DISCOUNT_TYPE_FIXED = 'fixed_amount';
    public const DISCOUNT_TYPE_FREE_SERVICE = 'free_service';
    public const DISCOUNT_TYPE_BUY_X_GET_Y = 'buy_x_get_y';

    /**
     * The services that the promotion applies to.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'promotion_service');
    }

    /**
     * The products that the promotion applies to.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    /**
     * The appointments that used this promotion.
     */
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_promotion')
            ->withPivot('discount_amount', 'applied_at')
            ->withTimestamps();
    }

    /**
     * Check if the promotion is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        return $this->start_date <= $now && 
               ($this->end_date === null || $this->end_date >= $now) &&
               ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
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
