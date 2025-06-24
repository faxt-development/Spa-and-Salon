<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    //use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'category_id',
        'brand',
        'cost_price',
        'selling_price',
        'quantity_in_stock',
        'minimum_stock_level',
        'is_active',
        'is_taxable',
        'tax_rate',
        'weight',
        'dimensions',
        'supplier_id',
        'reorder_quantity',
        'image_url',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity_in_stock' => 'integer',
        'minimum_stock_level' => 'integer',
        'is_active' => 'boolean',
        'is_taxable' => 'boolean',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:2',
        'reorder_quantity' => 'integer',
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
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the supplier for the product.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the inventory records for the product.
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the appointments that include this product.
     */
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_product')
            ->withPivot('quantity', 'price_per_unit', 'discount_amount')
            ->withTimestamps();
    }

    /**
     * Check if the product is low in stock.
     *
     * @return bool
     */
    public function isLowInStock(): bool
    {
        return $this->quantity_in_stock <= $this->minimum_stock_level;
    }

    /**
     * Calculate the total value of the current stock.
     *
     * @return float
     */
    public function getStockValue(): float
    {
        return $this->quantity_in_stock * $this->cost_price;
    }

    /**
     * Get the markup percentage.
     *
     * @return float
     */
    public function getMarkupPercentage(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }
        
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }
}
