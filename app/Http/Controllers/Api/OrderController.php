<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * The transaction service instance.
     *
     * @var \App\Services\TransactionService
     */
    protected $transactionService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\TransactionService $transactionService
     * @return void
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    /**
     * Display a listing of the orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Order::with(['client', 'staff', 'items', 'payments']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by staff
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort orders
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $orders = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable|exists:clients,id',
            'staff_id' => 'nullable|exists:staff,id',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:product,service',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate order totals
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            // Create the order
            $order = Order::create([
                'client_id' => $request->client_id,
                'staff_id' => $request->staff_id,
                'subtotal' => 0, // Will update after adding items
                'tax_amount' => 0, // Will update after adding items
                'discount_amount' => 0, // Will update after adding items
                'total_amount' => 0, // Will update after adding items
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Process order items
            foreach ($request->items as $item) {
                $itemModel = null;
                $itemType = null;

                if ($item['type'] === 'product') {
                    $itemModel = Product::findOrFail($item['id']);
                    $itemType = Product::class;

                    // Get current quantity before updating
                    $previousQuantity = $itemModel->quantity_in_stock;
                    
                    // Update product inventory
                    $itemModel->quantity_in_stock -= $item['quantity'];
                    $itemModel->save();
                    
                    // Log inventory transaction for this sale
                    $this->logInventoryTransaction(
                        $itemModel->id,
                        InventoryTransaction::TYPE_SALE,
                        -$item['quantity'], // Negative quantity for sales
                        $previousQuantity,
                        $itemModel->quantity_in_stock,
                        Order::class,
                        $order->id,
                        'Product sold in Order #' . $order->id
                    );
                } elseif ($item['type'] === 'service') {
                    $itemModel = Service::findOrFail($item['id']);
                    $itemType = Service::class;
                }

                $unitPrice = $item['unit_price'] ?? $itemModel->selling_price ?? $itemModel->price;
                $discount = $item['discount'] ?? 0;
                $taxRate = $itemModel->tax_rate ?? 0;

                $itemSubtotal = $unitPrice * $item['quantity'];
                $itemDiscount = $discount * $item['quantity'];
                $itemTaxableAmount = $itemSubtotal - $itemDiscount;
                $itemTaxAmount = $itemTaxableAmount * ($taxRate / 100);
                $itemTotal = $itemTaxableAmount + $itemTaxAmount;

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'itemable_id' => $itemModel->id,
                    'itemable_type' => $itemType,
                    'name' => $itemModel->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTaxAmount,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ]);

                // Update order totals
                $subtotal += $itemSubtotal;
                $discountAmount += $itemDiscount;
                $taxAmount += $itemTaxAmount;
            }

            // Update order with calculated totals
            $totalAmount = $subtotal - $discountAmount + $taxAmount;
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);
            
            // Create a pending transaction for this order
            $transaction = $this->transactionService->createFromOrder($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order->load(['items', 'client', 'staff']),
                    'transaction' => $transaction
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::with(['client', 'staff', 'items', 'payments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $order->update($request->only(['status', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order->load(['items', 'client', 'staff']),
        ]);
    }

    /**
     * Remove the specified order from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        // Check if order can be deleted (e.g., not completed)
        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed orders cannot be deleted',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Restore product quantities if order is being deleted
            foreach ($order->items as $item) {
                if ($item->itemable_type === Product::class) {
                    $product = Product::find($item->itemable_id);
                    if ($product) {
                        // Get current quantity before updating
                        $previousQuantity = $product->quantity_in_stock;
                        
                        // Restore inventory
                        $product->quantity_in_stock += $item->quantity;
                        $product->save();
                        
                        // Log inventory transaction for this return
                        $this->logInventoryTransaction(
                            $product->id,
                            InventoryTransaction::TYPE_RETURN,
                            $item->quantity, // Positive quantity for returns
                            $previousQuantity,
                            $product->quantity_in_stock,
                            Order::class,
                            $order->id,
                            'Product returned from cancelled Order #' . $order->id
                        );
                    }
                }
            }

            // Delete order items and payments
            $order->items()->delete();
            $order->payments()->delete();
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Log an inventory transaction.
     *
     * @param int $productId
     * @param string $type
     * @param int $quantity
     * @param int $previousQuantity
     * @param int $newQuantity
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     * @param int|null $supplierId
     * @return \App\Models\InventoryTransaction
     */
    private function logInventoryTransaction(
        $productId,
        $type,
        $quantity,
        $previousQuantity,
        $newQuantity,
        $referenceType = null,
        $referenceId = null,
        $notes = null,
        $supplierId = null
    ) {
        // Get the current authenticated staff member if available
        $staffId = null;
        if (Auth::check() && Auth::user()->staff) {
            $staffId = Auth::user()->staff->id;
        }
        
        $transaction = InventoryTransaction::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'staff_id' => $staffId,
            'supplier_id' => $supplierId,
        ]);
        
        return $transaction;
    }
}
