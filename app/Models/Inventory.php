<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity',
        'reorder_level',
        'supplier_id',
        'cost_price',
        'selling_price',
        'location',
        'last_restocked_date',
        'expiry_date',
        'batch_number',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'last_restocked_date' => 'datetime',
        'expiry_date' => 'date',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_restocked_date',
        'expiry_date',
        'deleted_at',
    ];

    /**
     * Get the product that owns the inventory record.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier for the inventory item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Check if the inventory level is below the reorder level.
     *
     * @return bool
     */
    public function needsReorder(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    /**
     * Check if the inventory item is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
