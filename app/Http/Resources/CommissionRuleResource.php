<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'applicable_type' => $this->applicable_type,
            'applicable_id' => $this->applicable_id,
            'condition_type' => $this->condition_type,
            'min_value' => $this->min_value !== null ? (float) $this->min_value : null,
            'max_value' => $this->max_value !== null ? (float) $this->max_value : null,
            'rate' => (float) $this->rate,
            'is_active' => (bool) $this->is_active,
            'priority' => (int) $this->priority,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
