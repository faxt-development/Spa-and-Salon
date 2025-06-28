<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionStructureResource extends JsonResource
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
            'type' => $this->type,
            'default_rate' => (float) $this->default_rate,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'rules' => CommissionRuleResource::collection($this->whenLoaded('rules')),
            'staff_count' => $this->whenCounted('staff'),
            'links' => [
                'self' => route('api.commission-structures.show', $this->id),
            ],
        ];
    }
}
