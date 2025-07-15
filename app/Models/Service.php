<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'category_id',
        'is_active',
        'max_capacity',
        'min_staff_required',
        'color',
        'image_url',
        'is_featured',
        'requires_approval',
        'cancellation_policy_hours',
        'tax_rate',
        'commission_rate',
        'resource_requirements',
        'pre_requisites',
        'aftercare_instructions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requires_approval' => 'boolean',
        'max_capacity' => 'integer',
        'min_staff_required' => 'integer',
        'tax_rate' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'resource_requirements' => 'array',
        'pre_requisites' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The categories that this service belongs to.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_service_category');
    }

    /**
     * The staff members who can perform this service.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'service_staff')
            ->withPivot('price_override', 'duration_override', 'is_primary')
            ->withTimestamps();
    }

    /**
     * The appointments that include this service.
     */
    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_service')
            ->withPivot('staff_id', 'price', 'duration', 'notes')
            ->withTimestamps();
    }

    /**
     * The promotions that include this service.
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_service')
            ->withTimestamps();
    }

    /**
     * The rooms where this service can be performed.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_services')
            ->withTimestamps();
    }

    /**
     * Get the products used in this service.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'service_products')
            ->withPivot('quantity_used', 'notes')
            ->withTimestamps();
    }

    /**
     * The companies that offer this service.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_service')
            ->withTimestamps();
    }

    /**
     * Get the service's formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return '\$' . number_format($this->price, 2);
    }

    /**
     * Get the service's duration in a human-readable format.
     *
     * @return string
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ' . Str::plural('hr', $hours);
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . Str::plural('min', $minutes);
        }

        return implode(' ', $parts) ?: '0 min';
    }

    /**
     * Calculate the final price after applying tax.
     *
     * @return float
     */
    public function getPriceWithTaxAttribute(): float
    {
        return $this->price * (1 + ($this->tax_rate / 100));
    }

    /**
     * Scope a query to only include active services.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured services.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include services in a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function($q) use ($categoryId) {
            $q->where('service_categories.id', $categoryId);
        });
    }
}
