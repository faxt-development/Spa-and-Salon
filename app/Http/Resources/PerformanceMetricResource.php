<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceMetricResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->period,
            'staff' => [
                'id' => $this->staff_id,
                'name' => $this->staff_name,
            ],
            'metrics' => [
                'available_hours' => (float) $this->available_hours,
                'booked_hours' => (float) $this->booked_hours,
                'utilization_rate' => (float) $this->utilization_rate,
                'total_revenue' => (float) $this->total_revenue,
                'revenue_per_hour' => (float) $this->revenue_per_hour,
                'appointments_completed' => (int) $this->appointments_completed,
                'average_ticket_value' => (float) $this->average_ticket_value,
                'total_commission' => (float) $this->total_commission,
                'average_commission_rate' => (float) $this->average_commission_rate,
            ],
        ];
    }
}
