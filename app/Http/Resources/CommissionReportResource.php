<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionReportResource extends JsonResource
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
            'staff_name' => $this->first_name . ' ' . $this->last_name,
            'item_type' => $this->item_type,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'total_sales' => (float) $this->total_sales,
            'total_commission' => (float) $this->total_commission,
            'transaction_count' => (int) $this->transaction_count,
            'item_count' => (int) $this->item_count,
            'average_commission_rate' => (float) $this->average_commission_rate,
        ];
    }
}
