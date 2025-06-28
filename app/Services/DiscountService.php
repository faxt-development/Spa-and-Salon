<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    public function applyDiscounts(Order $order, array $itemDiscounts = []): Order
    {
        return DB::transaction(function () use ($order, $itemDiscounts) {
            // Reset all discounts first
            $order->load('items');
            
            foreach ($order->items as $item) {
                $item->update([
                    'discount' => 0,
                    'discount_type' => null,
                    'promotion_id' => null,
                ]);
            }
            
            // Apply item-level discounts
            foreach ($itemDiscounts as $itemId => $discountData) {
                $item = $order->items->firstWhere('id', $itemId);
                if (!$item) continue;
                
                $this->applyItemDiscount(
                    $item,
                    $discountData['amount'],
                    $discountData['type'] ?? 'fixed',
                    $discountData['promotion_id'] ?? null
                );
            }
            
            // Recalculate order totals
            return $this->recalculateOrder($order);
        });
    }
    
    public function applyPromoCode(Order $order, string $code): array
    {
        $promotion = Promotion::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            })
            ->first();
            
        if (!$promotion) {
            return [
                'success' => false,
                'message' => 'Invalid or expired promo code',
            ];
        }
        
        return $this->applyPromotion($order, $promotion);
    }
    
    protected function applyPromotion(Order $order, Promotion $promotion): array
    {
        $order->load('items');
        
        return DB::transaction(function () use ($order, $promotion) {
            $discountApplied = 0;
            $items = $order->items;
            
            switch ($promotion->type) {
                case 'percentage':
                    foreach ($items as $item) {
                        $discount = $item->original_price * ($promotion->value / 100);
                        $this->applyItemDiscount($item, $discount, 'percentage', $promotion->id);
                        $discountApplied += $discount * $item->quantity;
                    }
                    break;
                    
                case 'fixed':
                    $perItemDiscount = $promotion->value / $items->count();
                    foreach ($items as $item) {
                        $this->applyItemDiscount($item, $perItemDiscount, 'fixed', $promotion->id);
                        $discountApplied += $perItemDiscount * $item->quantity;
                    }
                    break;
                    
                case 'bogo':
                    // Implement BOGO logic
                    break;
                    
                case 'package':
                    // Implement package deal logic
                    break;
            }
            
            // Update promotion usage
            $promotion->increment('usage_count');
            
            // Create promotion usage record
            $order->promotionUsages()->create([
                'promotion_id' => $promotion->id,
                'discount_amount' => $discountApplied,
            ]);
            
            // Recalculate order
            $order = $this->recalculateOrder($order);
            
            return [
                'success' => true,
                'message' => 'Promotion applied successfully',
                'discount_amount' => $discountApplied,
                'order_total' => $order->total_amount,
            ];
        });
    }
    
    protected function applyItemDiscount(OrderItem $item, float $amount, string $type, ?int $promotionId = null): void
    {
        $discount = $type === 'percentage'
            ? $item->original_price * ($amount / 100)
            : $amount;
            
        // Don't allow discount to make price negative
        $discount = min($discount, $item->original_price);
        
        $item->update([
            'discount' => $discount,
            'discount_type' => $type,
            'promotion_id' => $promotionId,
            'unit_price' => $item->original_price - $discount,
        ]);
    }
    
    protected function recalculateOrder(Order $order): Order
    {
        $order->load('items');
        
        $subtotal = $order->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });
        
        $discount = $order->items->sum(function ($item) {
            return $item->discount * $item->quantity;
        });
        
        // Recalculate tax on discounted amount if needed
        $tax = $order->tax_rate * $subtotal;
        
        $total = $subtotal + $tax - $order->loyalty_discount_amount;
        
        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => max(0, $total),
        ]);
        
        return $order->fresh();
    }
}
