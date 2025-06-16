<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * @var TaxCalculationService
     */
    protected $taxService;

    /**
     * @param TaxCalculationService $taxService
     */
    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Create a new order with items.
     *
     * @param array $data
     * @param array $items
     * @param string $calculationMode
     * @return Order
     * @throws \Throwable
     */
    public function createOrder(array $data, array $items, string $calculationMode = Order::TAX_CALCULATION_ITEM_LEVEL): Order
    {
        return DB::transaction(function () use ($data, $items, $calculationMode) {
            // Create the order
            $order = Order::create($data);
            
            // Add items to the order
            $this->syncOrderItems($order, $items);
            
            // Calculate taxes and update order totals
            $order = $this->taxService->calculateOrderTaxes($order, $calculationMode);
            
            return $order->refresh();
        });
    }

    /**
     * Update an existing order.
     *
     * @param Order $order
     * @param array $data
     * @param array $items
     * @param string $calculationMode
     * @return Order
     * @throws \Throwable
     */
    public function updateOrder(Order $order, array $data, array $items, string $calculationMode = Order::TAX_CALCULATION_ITEM_LEVEL): Order
    {
        return DB::transaction(function () use ($order, $data, $items, $calculationMode) {
            // Update order attributes
            $order->update($data);
            
            // Sync order items
            $this->syncOrderItems($order, $items);
            
            // Recalculate taxes and update order totals
            $order = $this->taxService->calculateOrderTaxes($order, $calculationMode);
            
            return $order->refresh();
        });
    }

    /**
     * Sync order items with the order.
     *
     * @param Order $order
     * @param array $items
     * @return void
     */
    protected function syncOrderItems(Order $order, array $items): void
    {
        $existingItems = $order->items->keyBy('id');
        $newItems = collect($items)->keyBy('id');
        
        // Remove items not in the new items array
        $itemsToDelete = $existingItems->diffKeys($newItems);
        if ($itemsToDelete->isNotEmpty()) {
            $order->items()->whereIn('id', $itemsToDelete->pluck('id'))->delete();
        }
        
        // Update or create items
        foreach ($newItems as $itemData) {
            $itemData = $this->prepareItemData($itemData);
            
            if (isset($itemData['id']) && $existingItems->has($itemData['id'])) {
                // Update existing item
                $order->items()->find($itemData['id'])->update($itemData);
            } else {
                // Create new item
                unset($itemData['id']);
                $order->items()->create($itemData);
            }
        }
    }

    /**
     * Prepare order item data for creation/update.
     *
     * @param array $itemData
     * @return array
     */
    protected function prepareItemData(array $itemData): array
    {
        // Determine the itemable type and ensure it exists
        $itemableType = $itemData['itemable_type'] ?? null;
        $itemableId = $itemData['itemable_id'] ?? null;
        
        if ($itemableType && $itemableId) {
            $itemableClass = $itemableType === 'product' ? Product::class : Service::class;
            $itemable = $itemableClass::findOrFail($itemableId);
            
            $itemData['itemable_type'] = $itemableClass;
            $itemData['itemable_id'] = $itemable->id;
            
            // Set default values if not provided
            $itemData['name'] = $itemData['name'] ?? $itemable->name;
            $itemData['unit_price'] = $itemData['unit_price'] ?? $itemable->price;
        }
        
        // Ensure required fields are set
        $itemData['quantity'] = (int) ($itemData['quantity'] ?? 1);
        $itemData['discount'] = (float) ($itemData['discount'] ?? 0);
        
        return $itemData;
    }

    /**
     * Recalculate order taxes.
     *
     * @param Order $order
     * @param string $calculationMode
     * @return Order
     */
    public function recalculateOrderTaxes(Order $order, string $calculationMode = Order::TAX_CALCULATION_ITEM_LEVEL): Order
    {
        return $this->taxService->calculateOrderTaxes($order, $calculationMode);
    }

    /**
     * Get the tax breakdown for an order.
     *
     * @param Order $order
     * @return array
     */
    public function getTaxBreakdown(Order $order): array
    {
        return $this->taxService->getTaxBreakdown($order);
    }
}
