<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionPaymentResource extends JsonResource
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
            'period_name' => $this->period_name,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'staff' => $this->whenLoaded('staff', function () {
                return [
                    'id' => $this->staff->id,
                    'name' => $this->staff->full_name,
                    'email' => $this->staff->email,
                ];
            }),
            'processor' => $this->whenLoaded('processor', function () {
                return $this->processor ? [
                    'id' => $this->processor->id,
                    'name' => $this->processor->name,
                ] : null;
            }),
            'metrics_count' => $this->whenCounted('performanceMetrics'),
            'links' => [
                'self' => route('api.commission-payments.show', $this->id),
                'metrics' => route('api.commission-payments.metrics', $this->id),
            ],
        ];
    }
}
