<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommissionRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commission_structure_id',
        'name',
        'description',
        'applicable_type',
        'applicable_id',
        'condition_type',
        'min_value',
        'max_value',
        'rate',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function structure(): BelongsTo
    {
        return $this->belongsTo(CommissionStructure::class, 'commission_structure_id');
    }

    public function applicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isApplicable(array $context = []): bool
    {
        // Check if rule is active
        if (!$this->is_active) {
            return false;
        }

        // Check if rule applies to the context
        if ($this->applicable_type && $this->applicable_id) {
            if (!isset($context['applicable_type']) || 
                !isset($context['applicable_id']) ||
                $this->applicable_type !== $context['applicable_type'] ||
                $this->applicable_id != $context['applicable_id']) {
                return false;
            }
        }

        // Check conditions based on condition_type
        switch ($this->condition_type) {
            case 'sales_volume':
                $value = $context['sales_volume'] ?? 0;
                return $this->checkRangeCondition($value);
                
            case 'item_count':
                $value = $context['item_count'] ?? 0;
                return $this->checkRangeCondition($value);
                
            // Add more condition types as needed
                
            default:
                return true; // No specific condition
        }
    }

    protected function checkRangeCondition($value): bool
    {
        $min = $this->min_value !== null ? (float)$this->min_value : -INF;
        $max = $this->max_value !== null ? (float)$this->max_value : INF;
        
        return $value >= $min && ($this->max_value === null || $value <= $max);
    }
}
