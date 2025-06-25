<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
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
     * Display a listing of the transactions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['client', 'staff', 'lineItems', 'revenueEvents']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
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
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Sort transactions
        $sortField = $request->input('sort_field', 'transaction_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $transactions = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Display the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = Transaction::with(['client', 'staff', 'lineItems', 'revenueEvents'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Process payment for a transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function processPayment(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'tip_amount' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string',
            'card_last_four' => 'nullable|string|size:4',
            'card_brand' => 'nullable|string',
            'external_transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if transaction is already completed
        if ($transaction->status === Transaction::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is already completed',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update transaction with payment details
            $transaction->update([
                'payment_method' => $request->payment_method,
                'payment_gateway' => $request->payment_gateway,
                'card_last_four' => $request->card_last_four,
                'card_brand' => $request->card_brand,
                'external_transaction_id' => $request->external_transaction_id,
                'tip_amount' => $request->tip_amount ?? 0,
            ]);

            // If a tip was provided, add it as a line item
            if (!empty($request->tip_amount) && $request->tip_amount > 0) {
                $this->transactionService->addTipLineItem($transaction, $request->tip_amount);
            }

            // Update transaction totals
            $this->transactionService->updateTransactionTotals($transaction);

            // Complete the transaction and create revenue event
            $revenueEvent = $this->transactionService->completeTransaction($transaction);

            // Create a Payment record for backward compatibility
            $payment = new Payment([
                'client_id' => $transaction->client_id,
                'staff_id' => $transaction->staff_id,
                'payment_method' => $transaction->payment_method,
                'amount' => $request->amount,
                'transaction_id' => $transaction->external_transaction_id,
                'status' => 'completed',
                'payment_date' => now(),
                'tip_amount' => $transaction->tip_amount,
                'tax_amount' => $transaction->tax_amount,
                'discount_amount' => $transaction->discount_amount,
                'payment_gateway' => $transaction->payment_gateway,
                'card_last_four' => $transaction->card_last_four,
                'card_brand' => $transaction->card_brand,
            ]);

            // Associate payment with appointment or order based on transaction reference
            if ($transaction->reference_type === 'App\\Models\\Appointment') {
                $payment->appointment_id = $transaction->reference_id;
            } elseif ($transaction->reference_type === 'App\\Models\\Order') {
                $payment->order_id = $transaction->reference_id;
            }

            $payment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'transaction' => $transaction->fresh()->load(['lineItems', 'revenueEvents']),
                    'revenue_event' => $revenueEvent,
                    'payment' => $payment,
                ],
            ]);
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
     * Process a refund for a transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function processRefund(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if transaction is refundable
        if (!$transaction->isRefundable()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not refundable',
            ], 422);
        }

        // Check if refund amount is valid
        if ($request->amount > $transaction->getRefundableAmount()) {
            return response()->json([
                'success' => false,
                'message' => 'Refund amount exceeds refundable amount',
                'refundable_amount' => $transaction->getRefundableAmount(),
            ], 422);
        }

        try {
            // Process the refund
            $refundTransaction = $this->transactionService->processRefund(
                $transaction,
                $request->amount,
                $request->reason ?? 'Refund requested'
            );

            // Create a Payment record for backward compatibility (as a refund)
            $payment = new Payment([
                'client_id' => $refundTransaction->client_id,
                'staff_id' => $refundTransaction->staff_id,
                'payment_method' => $refundTransaction->payment_method,
                'amount' => -$request->amount, // Negative amount for refunds
                'transaction_id' => $refundTransaction->external_transaction_id,
                'status' => 'refunded',
                'payment_date' => now(),
                'is_refunded' => true,
                'refunded_amount' => $request->amount,
                'refunded_at' => now(),
                'payment_gateway' => $refundTransaction->payment_gateway,
                'card_last_four' => $refundTransaction->card_last_four,
                'card_brand' => $refundTransaction->card_brand,
            ]);

            // Associate payment with appointment or order based on original transaction reference
            if ($transaction->reference_type === 'App\\Models\\Appointment') {
                $payment->appointment_id = $transaction->reference_id;
            } elseif ($transaction->reference_type === 'App\\Models\\Order') {
                $payment->order_id = $transaction->reference_id;
            }

            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund_transaction' => $refundTransaction->load(['lineItems', 'revenueEvents']),
                    'original_transaction' => $transaction->fresh(),
                    'payment' => $payment,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
