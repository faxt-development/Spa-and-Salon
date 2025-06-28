<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\Service;
use App\Models\Product;
use App\Models\CommissionStructure;
use App\Models\CommissionRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionService
{
    /**
     * Calculate commission for a transaction line item
     *
     * @param Staff $staff
     * @param float $amount
     * @param string $itemType
     * @param int|null $itemId
     * @param array $context
     * @return array
     */
    public function calculateCommission(Staff $staff, float $amount, string $itemType = 'service', ?int $itemId = null, array $context = []): array
    {
        $context = array_merge([
            'applicable_type' => 'staff',
            'applicable_id' => $staff->id,
            'item_type' => $itemType,
            'item_id' => $itemId,
        ], $context);

        // Check for item-specific commission rules first
        if ($itemId) {
            $itemRule = $this->findApplicableRule($itemType, $itemId, $context);
            if ($itemRule) {
                return $this->applyRule($itemRule, $amount, $context);
            }
        }

        // Check staff's commission structure
        if ($staff->commissionStructure) {
            $rule = $staff->commissionStructure->getApplicableRule($context);
            if ($rule) {
                return $this->applyRule($rule, $amount, $context);
            }
        }

        // Fall back to staff's default commission rate
        return [
            'amount' => $amount * ($staff->commission_rate / 100),
            'rate' => $staff->commission_rate,
            'rule' => null,
            'type' => 'percentage',
            'applied_to' => 'staff',
            'applied_id' => $staff->id,
        ];
    }

    /**
     * Calculate commissions for multiple line items
     *
     * @param Staff $staff
     * @param array $items Array of [amount, item_type, item_id, ...]
     * @return Collection
     */
    public function calculateCommissions(Staff $staff, array $items): Collection
    {
        return collect($items)->map(function ($item) use ($staff) {
            return $this->calculateCommission(
                $staff,
                $item['amount'],
                $item['item_type'] ?? 'service',
                $item['item_id'] ?? null,
                $item['context'] ?? []
            );
        });
    }

    /**
     * Generate commission report for a date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function generateCommissionReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = DB::table('revenue_events as re')
            ->join('staff', 're.staff_id', '=', 'staff.id')
            ->leftJoin('services', function ($join) {
                $join->where('re.item_type', 'service')
                    ->whereColumn('re.item_id', 'services.id');
            })
            ->leftJoin('products', function ($join) {
                $join->where('re.item_type', 'product')
                    ->whereColumn('re.item_id', 'products.id');
            })
            ->select([
                're.staff_id',
                'staff.first_name',
                'staff.last_name',
                're.item_type',
                're.item_id',
                DB::raw('CASE 
                    WHEN re.item_type = "service" THEN services.name
                    WHEN re.item_type = "product" THEN products.name
                    ELSE NULL
                END as item_name'),
                DB::raw('SUM(re.amount) as total_sales'),
                DB::raw('SUM(re.commission_amount) as total_commission'),
                DB::raw('COUNT(DISTINCT re.transaction_id) as transaction_count'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('AVG(re.commission_rate * 100) as average_commission_rate')
            ])
            ->whereBetween('re.recognized_at', [$startDate, $endDate])
            ->where('re.commission_amount', '>', 0)
            ->groupBy(['re.staff_id', 're.item_type', 're.item_id'])
            ->orderBy('staff.last_name')
            ->orderBy('staff.first_name')
            ->orderBy('item_name');

        // Apply filters
        if (!empty($filters['staff_id'])) {
            $query->where('re.staff_id', $filters['staff_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('re.location_id', $filters['location_id']);
        }

        if (!empty($filters['item_type'])) {
            $query->where('re.item_type', $filters['item_type']);
        }

        return $query->get();
    }

    /**
     * Find applicable commission rule
     */
    protected function findApplicableRule(string $type, int $id, array $context): ?CommissionRule
    {
        // This is a simplified version - in a real app, you'd want to cache this
        return CommissionRule::query()
            ->where('applicable_type', $type)
            ->where('applicable_id', $id)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();
    }

    /**
     * Apply a commission rule to an amount
     */
    protected function applyRule(CommissionRule $rule, float $amount, array $context): array
    {
        $rate = $rule->rate / 100; // Convert percentage to decimal
        
        return [
            'amount' => $amount * $rate,
            'rate' => $rule->rate,
            'rule' => $rule,
            'type' => $rule->structure->type,
            'applied_to' => $rule->applicable_type,
            'applied_id' => $rule->applicable_id,
        ];
    }

    /**
     * Sync staff commissions for a given period
     */
    public function syncStaffCommissions(Carbon $startDate, Carbon $endDate, ?int $staffId = null): void
    {
        $query = Staff::query();
        
        if ($staffId) {
            $query->where('id', $staffId);
        }
        
        $staffMembers = $query->get();
        
        foreach ($staffMembers as $staff) {
            $this->calculateStaffCommissions($staff, $startDate, $endDate);
        }
    }
    
    /**
     * Calculate and update commissions for a staff member
     */
    protected function calculateStaffCommissions(Staff $staff, Carbon $startDate, Carbon $endDate): void
    {
        // This would be implemented to update the revenue_events table
        // with calculated commissions for the given period
        // Implementation depends on your specific business logic
    }
}
