<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\Transaction;
use App\Models\TipDistribution;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TipDistributionService
{
    /**
     * Distribute tips for a transaction.
     *
     * @param Transaction $transaction
     * @param string $method The distribution method (individual, pooled, split)
     * @param array $distributionData Array of staff_id => amount/percentage
     * @param string|null $notes
     * @return Collection Collection of TipDistribution models
     * @throws \Exception
     */
    public function distributeTips(
        Transaction $transaction, 
        string $method,
        array $distributionData = [],
        ?string $notes = null
    ): Collection {
        if ($transaction->tip_amount <= 0) {
            throw new \InvalidArgumentException('Transaction has no tips to distribute.');
        }

        if ($transaction->tips_distributed) {
            throw new \RuntimeException('Tips have already been distributed for this transaction.');
        }

        return DB::transaction(function () use ($transaction, $method, $distributionData, $notes) {
            $distributions = collect();
            $totalDistributed = 0;
            $tipAmount = $transaction->tip_amount;

            switch ($method) {
                case 'individual':
                    // All tips go to the staff member who handled the transaction
                    $staff = $transaction->staff;
                    if (!$staff) {
                        throw new \RuntimeException('No staff member associated with this transaction.');
                    }
                    
                    $distributions->push($this->createTipDistribution(
                        $transaction,
                        $staff,
                        $tipAmount,
                        100,
                        $notes
                    ));
                    $totalDistributed = $tipAmount;
                    break;

                case 'pooled':
                    // Tips are pooled and distributed based on predefined rules
                    // This is a simplified example - you might want to implement more complex logic
                    $staffMembers = $this->getStaffForTipPool($transaction);
                    $staffCount = $staffMembers->count();
                    
                    if ($staffCount === 0) {
                        throw new \RuntimeException('No staff members found for tip pooling.');
                    }
                    
                    $amountPerStaff = round($tipAmount / $staffCount, 2);
                    
                    // Adjust the last amount to account for rounding errors
                    $remaining = $tipAmount;
                    
                    foreach ($staffMembers as $index => $staff) {
                        $amount = ($index === $staffCount - 1) 
                            ? $remaining 
                            : $amountPerStaff;
                            
                        $percentage = ($amount / $tipAmount) * 100;
                        
                        $distributions->push($this->createTipDistribution(
                            $transaction,
                            $staff,
                            $amount,
                            $percentage,
                            $notes
                        ));
                        
                        $remaining -= $amount;
                        $totalDistributed += $amount;
                    }
                    break;

                case 'split':
                    // Custom split based on provided distribution data
                    if (empty($distributionData)) {
                        throw new \InvalidArgumentException('Distribution data is required for split method.');
                    }
                    
                    $totalPercentage = 0;
                    $staffIds = array_keys($distributionData);
                    $staffMembers = Staff::whereIn('id', $staffIds)->get()->keyBy('id');
                    
                    // Validate staff members and calculate total percentage
                    foreach ($distributionData as $staffId => $percentage) {
                        if (!$staffMembers->has($staffId)) {
                            throw new \InvalidArgumentException("Invalid staff ID: $staffId");
                        }
                        $totalPercentage += $percentage;
                    }
                    
                    if (abs($totalPercentage - 100) > 0.01) {
                        throw new \InvalidArgumentException('Total percentage must equal 100%.');
                    }
                    
                    $remaining = $tipAmount;
                    $index = 0;
                    
                    foreach ($distributionData as $staffId => $percentage) {
                        $staff = $staffMembers[$staffId];
                        $amount = ($index === count($distributionData) - 1)
                            ? $remaining
                            : round(($percentage / 100) * $tipAmount, 2);
                            
                        $distributions->push($this->createTipDistribution(
                            $transaction,
                            $staff,
                            $amount,
                            $percentage,
                            $notes
                        ));
                        
                        $remaining -= $amount;
                        $totalDistributed += $amount;
                        $index++;
                    }
                    break;

                default:
                    throw new \InvalidArgumentException("Invalid tip distribution method: $method");
            }

            // Verify the total distributed matches the tip amount (account for floating point precision)
            if (abs($totalDistributed - $tipAmount) > 0.01) {
                throw new \RuntimeException("Total distributed amount ($totalDistributed) does not match tip amount ($tipAmount).");
            }

            // Update the transaction
            $transaction->update([
                'tip_distribution_method' => $method,
                'tips_distributed' => true,
                'tips_distributed_at' => now(),
            ]);

            return $distributions;
        });
    }

    /**
     * Get staff members who should receive tips from the pool.
     * 
     * @param Transaction $transaction
     * @return Collection
     */
    protected function getStaffForTipPool(Transaction $transaction): Collection
    {
        // This is a basic implementation. You might want to customize this based on your business rules.
        // For example, you might want to include all staff who worked during the transaction time,
        // or only staff who provided services.
        
        // Get staff who provided services in this transaction
        $staffIds = $transaction->lineItems()
            ->where('item_type', 'service')
            ->whereNotNull('staff_id')
            ->distinct('staff_id')
            ->pluck('staff_id');
            
        return Staff::whereIn('id', $staffIds)->get();
    }
    
    /**
     * Create a tip distribution record.
     * 
     * @param Transaction $transaction
     * @param Staff $staff
     * @param float $amount
     * @param float|null $percentage
     * @param string|null $notes
     * @return TipDistribution
     */
    protected function createTipDistribution(
        Transaction $transaction,
        Staff $staff,
        float $amount,
        ?float $percentage = null,
        ?string $notes = null
    ): TipDistribution {
        return TipDistribution::create([
            'transaction_id' => $transaction->id,
            'staff_id' => $staff->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'is_processed' => false,
            'notes' => $notes,
        ]);
    }
    
    /**
     * Get tip distribution summary for reporting.
     * 
     * @param array $filters
     * @return Collection
     */
    public function getTipDistributionSummary(array $filters = []): Collection
    {
        $query = TipDistribution::query()
            ->select([
                'staff_id',
                DB::raw('SUM(amount) as total_tips'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('AVG(percentage) as avg_percentage'),
            ])
            ->with('staff')
            ->groupBy('staff_id');
            
        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        
        return $query->get();
    }
}
