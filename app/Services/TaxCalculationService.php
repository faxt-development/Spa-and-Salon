<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TaxRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaxCalculationService
{
    /**
     * Calculate and apply taxes to an order.
     *
     * @param Order $order
     * @param string $calculationMode Either 'order_level' or 'item_level'
     * @return Order
     * @throws \Exception
     */
    public function calculateOrderTaxes(Order $order, string $calculationMode = Order::TAX_CALCULATION_ITEM_LEVEL): Order
    {
        return DB::transaction(function () use ($order, $calculationMode) {
            $order->loadMissing('items.itemable');
            
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = $order->discount_amount ?? 0;
            
            // Reset all tax amounts
            $order->items->each(function (OrderItem $item) {
                $item->update([
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                ]);
            });
            
            if ($calculationMode === Order::TAX_CALCULATION_ITEM_LEVEL) {
                // Calculate taxes at item level
                foreach ($order->items as $item) {
                    $itemSubtotal = $item->quantity * $item->unit_price;
                    $itemDiscount = min($item->discount, $itemSubtotal);
                    $taxableAmount = $itemSubtotal - $itemDiscount;
                    
                    // Get applicable tax rates for this item
                    $taxRates = $this->getApplicableTaxRatesForItem($item);
                    
                    $itemTaxRate = 0;
                    $itemTax = 0;
                    
                    foreach ($taxRates as $taxRate) {
                        $itemTaxRate += $taxRate->rate;
                        $itemTax += $taxRate->calculateTax($taxableAmount);
                    }
                    
                    // Update item with calculated values
                    $item->update([
                        'tax_rate' => $itemTaxRate,
                        'tax_amount' => $itemTax,
                        'subtotal' => $itemSubtotal,
                        'total' => $taxableAmount + $itemTax,
                    ]);
                    
                    $subtotal += $itemSubtotal;
                    $totalTax += $itemTax;
                }
            } else {
                // Calculate taxes at order level
                // First calculate subtotal
                foreach ($order->items as $item) {
                    $itemSubtotal = $item->quantity * $item->unit_price;
                    $itemDiscount = min($item->discount, $itemSubtotal);
                    $itemTotal = $itemSubtotal - $itemDiscount;
                    
                    $item->update([
                        'subtotal' => $itemSubtotal,
                        'total' => $itemTotal,
                    ]);
                    
                    $subtotal += $itemSubtotal;
                }
                
                // Get applicable tax rates for the order
                $taxRates = $this->getApplicableTaxRatesForOrder($order);
                $orderTaxRate = 0;
                
                foreach ($taxRates as $taxRate) {
                    $orderTaxRate += $taxRate->rate;
                    $totalTax += $taxRate->calculateTax($subtotal - $totalDiscount);
                }
                
                // Distribute tax proportionally to items
                if ($subtotal > 0) {
                    foreach ($order->items as $item) {
                        $itemRatio = $item->subtotal / $subtotal;
                        $itemTax = $totalTax * $itemRatio;
                        
                        $item->update([
                            'tax_rate' => $orderTaxRate,
                            'tax_amount' => $itemTax,
                            'total' => $item->total + $itemTax,
                        ]);
                    }
                }
            }
            
            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal + $totalTax - $totalDiscount,
            ]);
            
            return $order->refresh();
        });
    }
    
    /**
     * Get applicable tax rates for an order item.
     */
    protected function getApplicableTaxRatesForItem(OrderItem $item): Collection
    {
        $query = TaxRate::query()->active();
        
        // Get tax rates that apply to this specific item
        $productId = $item->itemable_type === 'App\\Models\\Product' ? $item->itemable_id : null;
        $categoryId = $productId ? ($item->itemable->category_id ?? null) : null;
        
        return $query->get()->filter(function($taxRate) use ($productId, $categoryId) {
            return $taxRate->appliesTo($productId, $categoryId);
        });
    }
    
    /**
     * Get applicable tax rates for an order.
     */
    protected function getApplicableTaxRatesForOrder(Order $order): Collection
    {
        // For order-level taxes, return all active tax rates that aren't item-specific
        return TaxRate::query()
            ->active()
            ->whereNull('applies_to')
            ->get();
    }
    
    /**
     * Get the tax breakdown for an order.
     */
    public function getTaxBreakdown(Order $order): array
    {
        $order->loadMissing('items.itemable');
        
        $taxRates = collect();
        
        // Group tax rates by rate and type
        foreach ($order->items as $item) {
            $itemRates = $this->getApplicableTaxRatesForItem($item);
            
            foreach ($itemRates as $rate) {
                $key = $rate->id . '-' . $rate->rate . '-' . $rate->type;
                
                if (!$taxRates->has($key)) {
                    $taxRates->put($key, [
                        'tax_rate' => $rate,
                        'taxable_amount' => 0,
                        'tax_amount' => 0,
                    ]);
                }
                
                $taxableAmount = $item->calculateTaxableAmount();
                
                $taxRates[$key]['taxable_amount'] += $taxableAmount;
                $taxRates[$key]['tax_amount'] += $rate->calculateTax($taxableAmount);
            }
        }
        
        return $taxRates->values()->toArray();
    }
}
