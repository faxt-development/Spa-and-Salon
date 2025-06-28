<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionLineItem;
use App\Models\RevenueEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueRecognitionService
{
    /**
     * Recognize revenue for a transaction based on the recognition rules
     * 
     * @param Transaction $transaction
     * @param Carbon|null $recognitionDate
     * @return array Array of created revenue events
     */
    public function recognizeRevenueForTransaction(Transaction $transaction, ?Carbon $recognitionDate = null): array
    {
        $recognitionDate = $recognitionDate ?: now();
        $revenueEvents = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($transaction->lineItems as $lineItem) {
                $events = $this->recognizeRevenueForLineItem($lineItem, $transaction, $recognitionDate);
                $revenueEvents = array_merge($revenueEvents, $events);
            }
            
            // Update the transaction status to indicate revenue has been recognized
            if (!empty($revenueEvents)) {
                $transaction->update(['revenue_recognized_at' => $recognitionDate]);
            }
            
            DB::commit();
            
            return $revenueEvents;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to recognize revenue', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Recognize revenue for a specific line item based on its type
     * 
     * @param TransactionLineItem $lineItem
     * @param Transaction $transaction
     * @param Carbon $recognitionDate
     * @return array Array of created revenue events
     */
    protected function recognizeRevenueForLineItem(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        $method = 'recognize' . ucfirst(camel_case($lineItem->line_item_type)) . 'Revenue';
        
        if (method_exists($this, $method)) {
            return $this->$method($lineItem, $transaction, $recognitionDate);
        }
        
        // Default recognition for unknown types - recognize immediately
        return $this->recognizeImmediately($lineItem, $transaction, $recognitionDate);
    }
    
    /**
     * Recognize service revenue based on service completion
     */
    protected function recognizeServiceRevenue(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        // For services, recognize revenue when the service is marked as completed
        $service = $lineItem->lineItem;
        
        // If the service has a specific completion date, use that
        $recognizedAt = $transaction->completed_at ?: $recognitionDate;
        
        // If the service has a duration, we might recognize revenue over time
        if ($service && $service->duration_days > 0) {
            return $this->recognizeOverTime($lineItem, $transaction, $recognizedAt, $service->duration_days);
        }
        
        // Otherwise recognize immediately
        return $this->recognizeImmediately($lineItem, $transaction, $recognizedAt);
    }
    
    /**
     * Recognize product revenue at the point of sale
     */
    protected function recognizeProductRevenue(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        // For products, recognize revenue immediately at the point of sale
        return $this->recognizeImmediately($lineItem, $transaction, $recognitionDate);
    }
    
    /**
     * Recognize membership revenue over the membership period
     */
    protected function recognizeMembershipRevenue(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        $membership = $lineItem->lineItem;
        $durationDays = 30; // Default to 30 days if not specified
        
        if ($membership && $membership->duration_days) {
            $durationDays = $membership->duration_days;
        }
        
        return $this->recognizeOverTime($lineItem, $transaction, $recognitionDate, $durationDays);
    }
    
    /**
     * Recognize package revenue over the package validity period
     */
    protected function recognizePackageRevenue(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        $package = $lineItem->lineItem;
        $durationDays = 90; // Default to 90 days if not specified
        
        if ($package && $package->validity_days) {
            $durationDays = $package->validity_days;
        }
        
        return $this->recognizeOverTime($lineItem, $transaction, $recognitionDate, $durationDays);
    }
    
    /**
     * Recognize gift card revenue when the gift card is redeemed
     */
    protected function recognizeGiftCardRevenue(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        // For gift cards, recognize revenue when the gift card is redeemed, not when sold
        // We'll create a deferred revenue event that will be recognized upon redemption
        return [
            $this->createRevenueEvent(
                $lineItem,
                $transaction,
                0, // No revenue recognized at sale time
                $lineItem->amount, // Deferred revenue
                $recognitionDate,
                'deferred',
                'Revenue deferred until gift card redemption'
            )
        ];
    }
    
    /**
     * Recognize gift card redemption
     */
    public function recognizeGiftCardRedemption(
        TransactionLineItem $redemptionLineItem,
        Transaction $redemptionTransaction
    ): array {
        // Find the original gift card sale line item
        $giftCard = $redemptionLineItem->lineItem;
        
        if (!$giftCard) {
            throw new \InvalidArgumentException('Invalid gift card for redemption');
        }
        
        $saleLineItem = TransactionLineItem::where('line_item_type', 'gift_card')
            ->where('line_item_id', $giftCard->id)
            ->where('amount', '>', 0)
            ->first();
            
        if (!$saleLineItem) {
            throw new \InvalidArgumentException('No corresponding gift card sale found');
        }
        
        // Create a revenue event for the redemption
        $event = $this->createRevenueEvent(
            $redemptionLineItem,
            $redemptionTransaction,
            $redemptionLineItem->amount, // Recognized revenue
            0, // No deferred amount
            now(),
            'recognized',
            'Gift card redemption',
            $saleLineItem->id // Link to original sale line item
        );
        
        return [$event];
    }
    
    /**
     * Recognize revenue immediately in full
     */
    protected function recognizeImmediately(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $recognitionDate
    ): array {
        return [
            $this->createRevenueEvent(
                $lineItem,
                $transaction,
                $lineItem->amount, // Full amount recognized
                0, // No deferred amount
                $recognitionDate,
                'recognized',
                'Revenue recognized immediately'
            )
        ];
    }
    
    /**
     * Recognize revenue over a period of time
     */
    protected function recognizeOverTime(
        TransactionLineItem $lineItem, 
        Transaction $transaction, 
        Carbon $startDate, 
        int $durationDays
    ): array {
        $events = [];
        $dailyAmount = $lineItem->amount / $durationDays;
        $remainingAmount = $lineItem->amount;
        
        for ($day = 0; $day < $durationDays; $day++) {
            $recognizedAt = (clone $startDate)->addDays($day);
            $amount = $day === $durationDays - 1 
                ? $remainingAmount 
                : round($dailyAmount, 2);
                
            $remainingAmount -= $amount;
            
            $events[] = $this->createRevenueEvent(
                $lineItem,
                $transaction,
                $amount,
                0, // No deferred amount for daily recognition
                $recognizedAt,
                'recognized',
                "Revenue recognized for day " . ($day + 1) . " of $durationDays"
            );
        }
        
        return $events;
    }
    
    /**
     * Create a revenue event record
     */
    protected function createRevenueEvent(
        TransactionLineItem $lineItem,
        Transaction $transaction,
        float $amount,
        float $deferredAmount,
        Carbon $recognizedAt,
        string $status,
        string $description = '',
        ?int $relatedLineItemId = null
    ): RevenueEvent {
        return RevenueEvent::create([
            'transaction_id' => $transaction->id,
            'line_item_id' => $lineItem->id,
            'related_line_item_id' => $relatedLineItemId,
            'line_item_type' => $lineItem->line_item_type,
            'amount' => $amount,
            'deferred_amount' => $deferredAmount,
            'status' => $status,
            'recognized_at' => $recognizedAt,
            'description' => $description,
            'staff_id' => $transaction->staff_id,
            'location_id' => $transaction->location_id,
            'client_id' => $transaction->client_id,
        ]);
    }
    
    /**
     * Get recognized revenue for a given period
     */
    public function getRecognizedRevenue(
        Carbon $startDate, 
        Carbon $endDate, 
        array $filters = []
    ): Collection {
        $query = RevenueEvent::where('status', 'recognized')
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->select([
                '*',
                DB::raw('SUM(amount) as recognized_amount'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
            ])
            ->groupBy('line_item_type');
            
        $this->applyFilters($query, $filters);
        
        return $query->get();
    }
    
    /**
     * Get deferred revenue as of a specific date
     */
    public function getDeferredRevenue(
        Carbon $asOfDate, 
        array $filters = []
    ): Collection {
        $query = RevenueEvent::where('status', 'deferred')
            ->where('recognized_at', '<=', $asOfDate)
            ->select([
                '*',
                DB::raw('SUM(deferred_amount) as deferred_amount'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
            ])
            ->groupBy('line_item_type');
            
        $this->applyFilters($query, $filters);
        
        return $query->get();
    }
    
    /**
     * Apply common filters to a query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['line_item_type'])) {
            $query->where('line_item_type', $filters['line_item_type']);
        }
        
        if (!empty($filters['line_item_id'])) {
            $query->where('line_item_id', $filters['line_item_id']);
        }
    }
    
    /**
     * Process revenue recognition for all eligible transactions
     */
    public function processRevenueRecognition(Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now();
        $results = [
            'processed' => 0,
            'revenue_recognized' => 0,
            'errors' => [],
        ];
        
        // Find all transactions that need revenue recognition
        $transactions = Transaction::where('status', 'completed')
            ->whereNull('revenue_recognized_at')
            ->where('completed_at', '<=', $asOfDate)
            ->with('lineItems')
            ->get();
            
        foreach ($transactions as $transaction) {
            try {
                $events = $this->recognizeRevenueForTransaction($transaction, $asOfDate);
                $results['processed']++;
                $results['revenue_recognized'] += count($events);
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ];
                
                Log::error('Failed to process revenue recognition', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        return $results;
    }
}
