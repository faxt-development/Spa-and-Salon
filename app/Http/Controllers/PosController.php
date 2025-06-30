<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Mail\ReceiptMailable;

class PosController extends Controller
{
    /**
     * Display the POS interface.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pos.index');
    }

    /**
     * Display the receipt for an order.
     *
     * @param  string  $orderId
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function receipt($orderId)
    {
        $order = Order::with(['items', 'client', 'items.serviceEmployee'])
            ->where('order_number', $orderId)
            ->orWhere('id', $orderId)
            ->firstOrFail();

        // If the request wants JSON (for AJAX requests)
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'receipt_url' => route('pos.receipt', $order->order_number),
                'print_url' => route('pos.receipt.print', $order->order_number)
            ]);
        }

        return view('pos.receipt', compact('order'));
    }

    /**
     * Print the receipt for an order.
     *
     * @param  string  $orderId
     * @return \Illuminate\View\View
     */
    public function printReceipt($orderId)
    {
        $order = Order::with(['items', 'client', 'items.serviceEmployee'])
            ->where('order_number', $orderId)
            ->orWhere('id', $orderId)
            ->firstOrFail();

        return view('pos.receipt', [
            'order' => $order,
            'print' => true
        ]);
    }

    /**
     * Get products for POS.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProducts()
    {
        $products = Product::where('is_active', true)
            ->where('quantity_in_stock', '>', 0)
            ->select(['id', 'name', 'description', 'selling_price', 'quantity_in_stock', 'image_url'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->selling_price,
                    'stock' => (int) $product->quantity_in_stock,
                    'image' => $product->image_url,
                    'type' => 'product'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get services for POS.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServices()
    {
        $services = Service::where('is_active', true)
            ->select(['id', 'name', 'description', 'price', 'duration'])
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => (float) $service->price,
                    'duration' => (int) $service->duration,
                    'type' => 'service'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Get customers for POS.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomers()
    {
        $customers = Client::select(['id', 'first_name', 'last_name', 'email', 'phone'])
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => trim($client->first_name . ' ' . $client->last_name),
                    'email' => $client->email,
                    'phone' => $client->phone
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Process a payment and create an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required',
            'items.*.type' => ['required', Rule::in(['product', 'service'])],
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'card', 'gift_card', 'other'])],
            'amount_paid' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'send_email' => 'sometimes|boolean',
            'customer_email' => 'required_if:send_email,true|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = new Order();
            $order->client_id = $request->customer_id;
            $order->staff_id = auth()->id();
            $order->subtotal = $request->total_amount - $request->tax_amount + $request->discount_amount;
            $order->tax_amount = $request->tax_amount;
            $order->discount_amount = $request->discount_amount ?? 0;
            $order->total_amount = $request->total_amount;
            $order->status = 'completed';
            $order->notes = $request->notes;
            $order->save();

            // Send email receipt if requested
            $emailSent = false;
            if ($request->boolean('send_email') && $request->filled('customer_email')) {
                try {
                    Mail::to($request->customer_email)
                        ->send(new ReceiptMailable($order));
                    
                    $emailSent = true;
                    
                    Log::info('Receipt email sent', [
                        'order_id' => $order->id,
                        'email' => $request->customer_email,
                        'status' => 'sent'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send receipt email', [
                        'order_id' => $order->id,
                        'email' => $request->customer_email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Continue with the order even if email fails
                    $emailSent = false;
                }
            }

            // Update order with email status
            if ($emailSent) {
                $order->update([
                    'email_sent' => true,
                    'email_sent_at' => now(),
                    'customer_email' => $request->customer_email
                ]);
            }

            // Add order items
            foreach ($request->items as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->itemable_type = $item['type'] === 'product' ? Product::class : Service::class;
                $orderItem->itemable_id = $item['id'];
                
                // Set service category if this is a service
                if ($item['type'] === 'service') {
                    $service = Service::with('categories')->find($item['id']);
                    if ($service && $service->categories->isNotEmpty()) {
                        // Use the first category as the primary category
                        $orderItem->service_category_id = $service->categories->first()->id;
                    }
                }
                
                $orderItem->name = $item['name'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = $item['price'];
                $orderItem->discount = $item['discount'] ?? 0;
                $orderItem->tax_rate = $item['tax_rate'] ?? 0;
                $orderItem->tax_amount = $item['tax_amount'] ?? 0;
                $orderItem->subtotal = $item['subtotal'];
                $orderItem->total = $item['total'];
                $orderItem->save();

                // Update product stock if it's a product
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->decrement('quantity_in_stock', $item['quantity']);
                    }
                }
            }

            // Record the payment
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->client_id = $request->customer_id;
            $payment->staff_id = auth()->id();
            $payment->payment_method = $request->payment_method;
            $payment->amount = $request->amount_paid;
            $payment->status = 'completed';
            $payment->payment_date = now();
            $payment->save();

            DB::commit();

            // Generate receipt URL
            $receiptUrl = route('pos.receipt', $order->id);
            $printUrl = route('pos.receipt.print', $order->id);

            return response()->json([
                'success' => true,
                'message' => 'Order processed successfully',
                'receipt_url' => $receiptUrl,
                'print_url' => $printUrl,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->id, // In a real app, you might have an order number
                    'total_paid' => $request->amount_paid,
                    'change_due' => max(0, $request->amount_paid - $request->total_amount)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('POS Payment Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a receipt for an order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function getReceipt(Order $order)
    {
        $order->load(['client', 'items', 'payments']);
        return view('pos.receipt', compact('order'));
    }
}
