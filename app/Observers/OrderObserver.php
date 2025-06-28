<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\LoyaltyService;

class OrderObserver
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    public function updated(Order $order)
    {
        $originalStatus = $order->getOriginal('status');
        $newStatus = $order->status;

        // If order was just marked as completed
        if ($originalStatus !== 'completed' && $newStatus === 'completed') {
            $this->loyaltyService->earnPoints($order);
        }
        
        // If order was just cancelled, handle point reversal if needed
        if ($originalStatus !== 'cancelled' && $newStatus === 'cancelled') {
            $this->handleOrderCancellation($order);
        }
    }
    
    protected function handleOrderCancellation(Order $order): void
    {
        if ($order->loyalty_points_earned > 0) {
            // Reverse earned points
            $this->loyaltyService->createTransaction(
                $order->client->loyaltyAccount,
                'adjust',
                -$order->loyalty_points_earned,
                'Reversal for cancelled order #' . $order->id,
                $order
            );
        }
        
        if ($order->loyalty_points_redeemed > 0) {
            // Return redeemed points
            $this->loyaltyService->createTransaction(
                $order->client->loyaltyAccount,
                'adjust',
                $order->loyalty_points_redeemed,
                'Points returned for cancelled order #' . $order->id,
                $order
            );
        }
    }
}
