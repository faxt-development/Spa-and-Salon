<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffPerformanceSummaryResource extends JsonResource
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
            'total_revenue' => (float) $this->total_revenue,
            'total_hours' => (float) $this->total_hours,
            'revenue_per_hour' => (float) $this->revenue_per_hour,
            'avg_utilization' => (float) $this->avg_utilization,
            'total_appointments' => (int) $this->total_appointments,
            'avg_ticket_value' => (float) $this->avg_ticket_value,
        ];
    }
}
