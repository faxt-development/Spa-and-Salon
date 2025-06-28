<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionStructure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'default_rate',
        'is_active'
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(CommissionRule::class)->orderBy('priority');
    }

    public function getApplicableRule($context = []): ?CommissionRule
    {
        $rules = $this->rules()
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->isApplicable($context)) {
                return $rule;
            }
        }

        return null;
    }
}
