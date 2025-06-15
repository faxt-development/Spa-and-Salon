<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments with optional filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Payment::with(['client', 'staff', 'order']);

        // Filter by order
        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        // Filter by appointment
        if ($request->has('appointment_id')) {
            $query->where('appointment_id', $request->appointment_id);
        }

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by staff
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        // Sort payments
        $sortField = $request->input('sort_field', 'payment_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $payments = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|exists:orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'client_id' => 'nullable|exists:clients,id',
            'staff_id' => 'nullable|exists:staff,id',
            'payment_method' => 'required|string|in:cash,credit_card,debit_card,gift_card,check,bank_transfer',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'nullable|string',
            'card_last_four' => 'nullable|string|size:4',
            'card_brand' => 'nullable|string',
            'notes' => 'nullable|string',
            'tip_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $paymentData = $request->all();
            $paymentData['status'] = Payment::STATUS_COMPLETED;
            $paymentData['payment_date'] = now();

            $payment = Payment::create($paymentData);

            // If this is for an order, update the order status if fully paid
            if ($request->has('order_id')) {
                $order = Order::findOrFail($request->order_id);
                $totalPaid = $order->payments()->sum('amount');
                
                if ($totalPaid >= $order->total_amount) {
                    $order->update(['status' => 'completed']);
                } else if ($totalPaid > 0) {
                    $order->update(['status' => 'partially_paid']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $payment->load(['client', 'staff', 'order']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = Payment::with(['client', 'staff', 'order'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    /**
     * Update the specified payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        // Only allow updates to notes and status
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,completed,failed,refunded,partially_refunded',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment->update($request->only(['status', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment->fresh(['client', 'staff', 'order']),
        ]);
    }

    /**
     * Process a refund for the specified payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refund(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if (!$payment->isRefundable()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment is not eligible for refund',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:' . $payment->getRefundableAmount(),
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create refund record
            $refund = Payment::create([
                'parent_payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'appointment_id' => $payment->appointment_id,
                'client_id' => $payment->client_id,
                'staff_id' => $payment->staff_id,
                'payment_method' => $payment->payment_method,
                'amount' => -$request->amount, // Negative amount for refund
                'transaction_id' => null, // Will be updated if using payment gateway
                'status' => Payment::STATUS_COMPLETED,
                'payment_date' => now(),
                'notes' => $request->reason ?? 'Refund for payment #' . $payment->id,
                'is_refunded' => true,
                'refunded_amount' => $request->amount,
                'refunded_at' => now(),
            ]);

            // Update original payment
            $payment->is_refunded = true;
            $payment->refunded_amount = ($payment->refunded_amount ?? 0) + $request->amount;
            $payment->refunded_at = now();
            
            // If fully refunded, update status
            if ($payment->refunded_amount >= $payment->amount) {
                $payment->status = Payment::STATUS_REFUNDED;
            } else {
                $payment->status = Payment::STATUS_PARTIALLY_REFUNDED;
            }
            
            $payment->save();

            // If this was for an order, update order status
            if ($payment->order_id) {
                $order = Order::findOrFail($payment->order_id);
                $totalPaid = $order->payments()->sum('amount');
                
                if ($totalPaid <= 0) {
                    $order->update(['status' => 'refunded']);
                } else if ($totalPaid < $order->total_amount) {
                    $order->update(['status' => 'partially_paid']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund' => $refund,
                    'original_payment' => $payment->fresh(),
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified payment from storage (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        // Only allow deletion of pending payments
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payments can be deleted',
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }
}
