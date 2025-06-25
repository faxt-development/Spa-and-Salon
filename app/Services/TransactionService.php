<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionLineItem;
use App\Models\RevenueEvent;
use App\Models\Service;
use App\Models\Product;
use App\Models\TaxRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TransactionService
{
    /**
     * Create a transaction from an appointment.
     *
     * @param Appointment $appointment
     * @return Transaction
     */
    public function createFromAppointment(Appointment $appointment): Transaction
    {
        return DB::transaction(function () use ($appointment) {
            // Create the transaction
            $transaction = new Transaction([
                'client_id' => $appointment->client_id,
                'staff_id' => $appointment->staff_id,
                'room_id' => $appointment->room_id ?? null,
                'transaction_type' => Transaction::TYPE_APPOINTMENT,
                'reference_type' => get_class($appointment),
                'reference_id' => $appointment->id,
                'subtotal' => 0, // Will be calculated from line items
                'tax_amount' => 0, // Will be calculated from line items
                'tip_amount' => 0, // Will be added if applicable
                'discount_amount' => 0, // Will be calculated from line items
                'total_amount' => 0, // Will be calculated
                'status' => Transaction::STATUS_PENDING,
                'transaction_date' => Carbon::now(),
                'notes' => "Appointment #{$appointment->id}",
            ]);
            
            $transaction->save();
            
            // Add services as line items
            foreach ($appointment->services as $service) {
                $price = $service->pivot->price ?? $service->price;
                $this->addServiceLineItem($transaction, $service, $price);
            }
            
            // Add products as line items
            foreach ($appointment->products as $product) {
                $price = $product->pivot->price ?? $product->price;
                $quantity = $product->pivot->quantity ?? 1;
                $this->addProductLineItem($transaction, $product, $quantity, $price);
            }
            
            // Add tax line items
            $this->calculateAndAddTaxes($transaction);
            
            // Update transaction totals
            $this->updateTransactionTotals($transaction);
            
            return $transaction;
        });
    }
    
    /**
     * Create a transaction from an order.
     *
     * @param Order $order
     * @return Transaction
     */
    public function createFromOrder(Order $order): Transaction
    {
        return DB::transaction(function () use ($order) {
            // Create the transaction
            $transaction = new Transaction([
                'client_id' => $order->client_id,
                'staff_id' => $order->staff_id,
                'transaction_type' => Transaction::TYPE_RETAIL,
                'reference_type' => get_class($order),
                'reference_id' => $order->id,
                'subtotal' => 0, // Will be calculated from line items
                'tax_amount' => 0, // Will be calculated from line items
                'discount_amount' => 0, // Will be calculated from line items
                'total_amount' => 0, // Will be calculated
                'status' => Transaction::STATUS_PENDING,
                'transaction_date' => Carbon::now(),
                'notes' => "Order #{$order->id}",
            ]);
            
            $transaction->save();
            
            // Add order items as line items
            foreach ($order->items as $item) {
                if ($item->itemable_type === Product::class) {
                    $this->addProductLineItem(
                        $transaction, 
                        $item->itemable, 
                        $item->quantity, 
                        $item->unit_price
                    );
                } elseif ($item->itemable_type === Service::class) {
                    $this->addServiceLineItem(
                        $transaction, 
                        $item->itemable, 
                        $item->unit_price
                    );
                }
                
                // Add discount if applicable
                if ($item->discount > 0) {
                    $this->addDiscountLineItem(
                        $transaction,
                        "Discount on {$item->name}",
                        $item->discount
                    );
                }
            }
            
            // Add tax line items
            $this->calculateAndAddTaxes($transaction);
            
            // Update transaction totals
            $this->updateTransactionTotals($transaction);
            
            return $transaction;
        });
    }
    
    /**
     * Add a service line item to a transaction.
     *
     * @param Transaction $transaction
     * @param Service $service
     * @param float $price
     * @return TransactionLineItem
     */
    public function addServiceLineItem(Transaction $transaction, Service $service, float $price): TransactionLineItem
    {
        $lineItem = new TransactionLineItem([
            'transaction_id' => $transaction->id,
            'item_type' => TransactionLineItem::TYPE_SERVICE,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 1,
            'unit_price' => $price,
            'amount' => $price,
            'itemable_type' => get_class($service),
            'itemable_id' => $service->id,
            'staff_id' => $transaction->staff_id,
        ]);
        
        $lineItem->save();
        return $lineItem;
    }
    
    /**
     * Add a product line item to a transaction.
     *
     * @param Transaction $transaction
     * @param Product $product
     * @param float $quantity
     * @param float $price
     * @return TransactionLineItem
     */
    public function addProductLineItem(Transaction $transaction, Product $product, float $quantity, float $price): TransactionLineItem
    {
        $lineItem = new TransactionLineItem([
            'transaction_id' => $transaction->id,
            'item_type' => TransactionLineItem::TYPE_PRODUCT,
            'name' => $product->name,
            'description' => $product->description,
            'quantity' => $quantity,
            'unit_price' => $price,
            'amount' => $quantity * $price,
            'itemable_type' => get_class($product),
            'itemable_id' => $product->id,
            'staff_id' => $transaction->staff_id,
        ]);
        
        $lineItem->save();
        return $lineItem;
    }
    
    /**
     * Add a tax line item to a transaction.
     *
     * @param Transaction $transaction
     * @param string $name
     * @param float $rate
     * @param float $amount
     * @return TransactionLineItem
     */
    public function addTaxLineItem(Transaction $transaction, string $name, float $rate, float $amount): TransactionLineItem
    {
        $lineItem = new TransactionLineItem([
            'transaction_id' => $transaction->id,
            'item_type' => TransactionLineItem::TYPE_TAX,
            'name' => $name,
            'description' => "Tax rate: " . number_format($rate, 2) . "%",
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
            'tax_rate' => $rate,
        ]);
        
        $lineItem->save();
        return $lineItem;
    }
    
    /**
     * Add a tip line item to a transaction.
     *
     * @param Transaction $transaction
     * @param float $amount
     * @param int|null $staffId
     * @return TransactionLineItem
     */
    public function addTipLineItem(Transaction $transaction, float $amount, ?int $staffId = null): TransactionLineItem
    {
        $staffId = $staffId ?? $transaction->staff_id;
        
        $lineItem = new TransactionLineItem([
            'transaction_id' => $transaction->id,
            'item_type' => TransactionLineItem::TYPE_TIP,
            'name' => 'Tip',
            'description' => 'Gratuity',
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
            'staff_id' => $staffId,
        ]);
        
        $lineItem->save();
        
        // Update the transaction's tip amount
        $transaction->tip_amount = $transaction->lineItems()
            ->where('item_type', TransactionLineItem::TYPE_TIP)
            ->sum('amount');
        $transaction->save();
        
        return $lineItem;
    }
    
    /**
     * Add a discount line item to a transaction.
     *
     * @param Transaction $transaction
     * @param string $name
     * @param float $amount
     * @return TransactionLineItem
     */
    public function addDiscountLineItem(Transaction $transaction, string $name, float $amount): TransactionLineItem
    {
        $lineItem = new TransactionLineItem([
            'transaction_id' => $transaction->id,
            'item_type' => TransactionLineItem::TYPE_DISCOUNT,
            'name' => $name,
            'description' => 'Discount',
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => -abs($amount), // Discounts are stored as negative values
        ]);
        
        $lineItem->save();
        
        // Update the transaction's discount amount
        $transaction->discount_amount = abs($transaction->lineItems()
            ->where('item_type', TransactionLineItem::TYPE_DISCOUNT)
            ->sum('amount'));
        $transaction->save();
        
        return $lineItem;
    }
    
    /**
     * Calculate and add tax line items to a transaction.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function calculateAndAddTaxes(Transaction $transaction): void
    {
        // Get all taxable line items
        $taxableLineItems = $transaction->lineItems()
            ->whereIn('item_type', [
                TransactionLineItem::TYPE_SERVICE,
                TransactionLineItem::TYPE_PRODUCT,
            ])
            ->get();
        
        // Get applicable tax rates
        $taxRates = TaxRate::where('is_active', true)->get();
        
        // Calculate taxes for each line item
        foreach ($taxableLineItems as $lineItem) {
            $taxableAmount = $lineItem->calculateTaxableAmount();
            
            foreach ($taxRates as $taxRate) {
                // Check if this item is taxable by this tax rate
                $isTaxable = true; // This logic can be expanded based on business rules
                
                if ($isTaxable) {
                    $taxAmount = $taxableAmount * ($taxRate->rate / 100);
                    
                    if ($taxAmount > 0) {
                        $this->addTaxLineItem(
                            $transaction,
                            $taxRate->name,
                            $taxRate->rate,
                            $taxAmount
                        );
                    }
                }
            }
        }
    }
    
    /**
     * Update the transaction totals based on line items.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function updateTransactionTotals(Transaction $transaction): void
    {
        // Refresh the transaction to ensure we have the latest line items
        $transaction->refresh();
        
        // Calculate subtotal (services + products)
        $subtotal = $transaction->lineItems()
            ->whereIn('item_type', [
                TransactionLineItem::TYPE_SERVICE,
                TransactionLineItem::TYPE_PRODUCT,
                TransactionLineItem::TYPE_GIFT_CARD,
            ])
            ->sum('amount');
        
        // Calculate tax amount
        $taxAmount = $transaction->lineItems()
            ->where('item_type', TransactionLineItem::TYPE_TAX)
            ->sum('amount');
        
        // Calculate discount amount (stored as negative values in line items)
        $discountAmount = abs($transaction->lineItems()
            ->where('item_type', TransactionLineItem::TYPE_DISCOUNT)
            ->sum('amount'));
        
        // Calculate tip amount
        $tipAmount = $transaction->lineItems()
            ->where('item_type', TransactionLineItem::TYPE_TIP)
            ->sum('amount');
        
        // Calculate total amount
        $totalAmount = $subtotal + $taxAmount + $tipAmount - $discountAmount;
        
        // Update the transaction
        $transaction->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'tip_amount' => $tipAmount,
            'total_amount' => $totalAmount,
        ]);
    }
    
    /**
     * Complete a transaction and create a revenue event.
     *
     * @param Transaction $transaction
     * @return RevenueEvent
     */
    public function completeTransaction(Transaction $transaction): RevenueEvent
    {
        return DB::transaction(function () use ($transaction) {
            // Update transaction status
            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
            ]);
            
            // Determine the event type based on transaction type
            $eventType = match($transaction->transaction_type) {
                Transaction::TYPE_APPOINTMENT => RevenueEvent::TYPE_APPOINTMENT_COMPLETED,
                Transaction::TYPE_RETAIL => RevenueEvent::TYPE_RETAIL_SALE,
                Transaction::TYPE_GIFT_CARD => RevenueEvent::TYPE_GIFT_CARD_REDEMPTION,
                Transaction::TYPE_REFUND => RevenueEvent::TYPE_REFUND,
                default => RevenueEvent::TYPE_OTHER,
            };
            
            // Create a revenue event
            $revenueEvent = new RevenueEvent([
                'transaction_id' => $transaction->id,
                'event_type' => $eventType,
                'event_date' => Carbon::now(),
                'amount' => $transaction->total_amount,
                'description' => "Revenue from {$transaction->transaction_type} transaction #{$transaction->id}",
                'source_type' => $transaction->reference_type,
                'source_id' => $transaction->reference_id,
                'staff_id' => $transaction->staff_id,
            ]);
            
            $revenueEvent->save();
            
            return $revenueEvent;
        });
    }
    
    /**
     * Process a refund for a transaction.
     *
     * @param Transaction $transaction
     * @param float $amount
     * @param string $reason
     * @return Transaction
     */
    public function processRefund(Transaction $transaction, float $amount, string $reason = ''): Transaction
    {
        if (!$transaction->isRefundable() || $amount > $transaction->getRefundableAmount()) {
            throw new \Exception('Transaction is not refundable or refund amount exceeds refundable amount.');
        }
        
        return DB::transaction(function () use ($transaction, $amount, $reason) {
            // Create a refund transaction
            $refundTransaction = new Transaction([
                'client_id' => $transaction->client_id,
                'staff_id' => $transaction->staff_id,
                'room_id' => $transaction->room_id,
                'payment_method' => $transaction->payment_method,
                'transaction_type' => Transaction::TYPE_REFUND,
                'reference_type' => get_class($transaction),
                'reference_id' => $transaction->id,
                'subtotal' => -$amount,
                'total_amount' => -$amount,
                'status' => Transaction::STATUS_COMPLETED,
                'transaction_date' => Carbon::now(),
                'notes' => $reason ?: "Refund for transaction #{$transaction->id}",
                'payment_gateway' => $transaction->payment_gateway,
                'card_last_four' => $transaction->card_last_four,
                'card_brand' => $transaction->card_brand,
                'parent_transaction_id' => $transaction->id,
            ]);
            
            $refundTransaction->save();
            
            // Create a refund line item
            $lineItem = new TransactionLineItem([
                'transaction_id' => $refundTransaction->id,
                'item_type' => TransactionLineItem::TYPE_OTHER,
                'name' => 'Refund',
                'description' => $reason ?: "Refund for transaction #{$transaction->id}",
                'quantity' => 1,
                'unit_price' => -$amount,
                'amount' => -$amount,
            ]);
            
            $lineItem->save();
            
            // Create a revenue event for the refund
            $revenueEvent = new RevenueEvent([
                'transaction_id' => $refundTransaction->id,
                'event_type' => RevenueEvent::TYPE_REFUND,
                'event_date' => Carbon::now(),
                'amount' => -$amount,
                'description' => $reason ?: "Refund for transaction #{$transaction->id}",
                'source_type' => get_class($transaction),
                'source_id' => $transaction->id,
                'staff_id' => $refundTransaction->staff_id,
            ]);
            
            $revenueEvent->save();
            
            // Update the original transaction status if fully refunded
            $totalRefunded = $transaction->refundTransactions()->sum('total_amount');
            $totalRefunded = abs($totalRefunded);
            
            if ($totalRefunded >= $transaction->total_amount) {
                $transaction->update(['status' => Transaction::STATUS_REFUNDED]);
            } elseif ($totalRefunded > 0) {
                $transaction->update(['status' => Transaction::STATUS_PARTIALLY_REFUNDED]);
            }
            
            return $refundTransaction;
        });
    }
}
