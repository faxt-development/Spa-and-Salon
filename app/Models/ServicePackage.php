<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_percentage',
        'category_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the service package.
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * The services that belong to the package.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_package_items')
            ->withPivot('order')
            ->orderBy('service_package_items.order');
    }

    /**
     * Calculate the total price of all services in the package.
     *
     * @return float
     */
    public function getTotalServicesValueAttribute()
    {
        return $this->services->sum('price');
    }

    /**
     * Calculate the savings amount when buying the package.
     *
     * @return float
     */
    public function getSavingsAmountAttribute()
    {
        return $this->total_services_value - $this->price;
    }

    /**
     * Calculate the savings percentage when buying the package.
     *
     * @return float
     */
    public function getSavingsPercentageAttribute()
    {
        if ($this->total_services_value > 0) {
            return round(($this->savings_amount / $this->total_services_value) * 100, 2);
        }
        
        return 0;
    }
}
