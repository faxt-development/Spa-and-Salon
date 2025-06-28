<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilizationReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'staff_id' => $this->staff_id,
            'staff_name' => $this->staff_name,
            'available_hours' => (float) $this->available_hours,
            'booked_hours' => (float) $this->booked_hours,
            'utilization_rate' => (float) $this->utilization_rate,
            'total_revenue' => (float) $this->total_revenue,
            'revenue_per_hour' => (float) $this->revenue_per_hour,
        ];
    }
}
