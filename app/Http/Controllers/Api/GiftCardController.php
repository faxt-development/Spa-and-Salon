<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class GiftCardController extends Controller
{
    /**
     * Display a listing of gift cards with optional filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = GiftCard::query();

        // Filter by status
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true)
                    ->where('is_redeemed', false)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            } elseif ($status === 'redeemed') {
                $query->where('is_redeemed', true);
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<=', now());
            }
        }

        // Search by code, recipient, or sender
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_email', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%");
            });
        }

        $giftCards = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json($giftCards);
    }

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a payment intent for a gift card purchase
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:' . config('services.gift_cards.min_amount', 5),
                'max:' . config('services.gift_cards.max_amount', 1000),
                function ($attribute, $value, $fail) {
                    // Ensure amount has no more than 2 decimal places
                    if (preg_match('/\.\d{3,}/', (string)$value)) {
                        $fail('The ' . $attribute . ' must not have more than 2 decimal places.');
                    }
                    
                    // Ensure amount is a valid monetary value
                    if ($value <= 0) {
                        $fail('The ' . $attribute . ' must be greater than 0.');
                    }
                },
            ],
            'recipient_name' => 'required|string|max:255',
            'recipient_email' => 'required|email|max:255',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
            'expires_at' => [
                'nullable',
                'date',
                'after:today',
                function ($attribute, $value, $fail) {
                    $maxDate = now()->addYears(config('services.gift_cards.max_validity_years', 2));
                    if ($value && strtotime($value) > $maxDate->timestamp) {
                        $fail('The gift card cannot be valid for more than ' . config('services.gift_cards.max_validity_years', 2) . ' years.');
                    }
                },
            ],
        ]);
        
        // Format amount to exactly 2 decimal places
        $validated['amount'] = number_format((float)$validated['amount'], 2, '.', '');

        // Add authenticated user info if available
        $user = $request->user();
        if ($user) {
            $validated['user_id'] = $user->id;
            // Use user's email as sender if not provided
            if (empty($validated['sender_email']) && $user->email) {
                $validated['sender_email'] = $user->email;
            }
        }

        // Create payment intent
        $metadata = [
            'purchase_type' => 'gift_card',
            'recipient_name' => $validated['recipient_name'],
            'recipient_email' => $validated['recipient_email'],
            'sender_name' => $validated['sender_name'],
        ];

        // Add user ID to metadata if authenticated
        if (!empty($validated['user_id'])) {
            $metadata['user_id'] = $validated['user_id'];
        }

        if (!empty($validated['sender_email'])) {
            $metadata['sender_email'] = $validated['sender_email'];
        }

        if (isset($validated['message'])) {
            $metadata['message'] = mb_substr($validated['message'], 0, 100); // Truncate to 100 chars for metadata
        }

        if (isset($validated['expires_at'])) {
            $metadata['expires_at'] = $validated['expires_at'];
        }

        try {
            $paymentService = app(\App\Services\PaymentService::class);
            $paymentIntent = $paymentService->createGiftCardPaymentIntent(
                $validated['amount'],
                $metadata
            );

            // Store the gift card data in the session temporarily
            // Include user_id if authenticated
            $sessionData = $validated;
            if ($user) {
                $sessionData['user_id'] = $user->id;
            }

            session([
                'gift_card_data_' . $paymentIntent->id => $sessionData
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'publishable_key' => config('services.stripe.key'),
                'is_authenticated' => !is_null($user),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating payment intent: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to process payment. Please try again.'], 500);
        }
    }

    /**
     * Handle successful payment and create gift card
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleSuccessfulPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $paymentService = app(\App\Services\PaymentService::class);
        $paymentIntentId = $validated['payment_intent_id'];

        try {
            // Retrieve the payment intent from Stripe
            $paymentIntent = $paymentService->getPaymentIntent($paymentIntentId);
            
            if (!$paymentIntent) {
                throw new \Exception('Payment intent not found');
            }

            // Get the stored gift card data from the session
            $sessionKey = 'gift_card_data_' . $paymentIntentId;
            $giftCardData = session($sessionKey);
            
            if (!$giftCardData) {
                throw new \Exception('Invalid session data or session expired');
            }

            // Create the gift card
            $giftCard = $paymentService->handleSuccessfulGiftCardPayment(
                $paymentIntent,
                $giftCardData
            );

            // Clear the session data
            session()->forget($sessionKey);

            return response()->json([
                'message' => 'Gift card created successfully',
                'gift_card' => $giftCard,
                'gift_card_code' => $giftCard->code,
                'is_guest' => !auth()->check(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error handling successful payment: ' . $e->getMessage(), [
                'exception' => $e,
                'payment_intent_id' => $paymentIntentId ?? 'unknown',
                'user_id' => auth()->id()
            ]);
            
            // Attempt to refund the payment if we have a valid payment intent
            if (isset($paymentIntent) && $paymentIntent) {
                try {
                    $paymentService->refundPayment($paymentIntent->id);
                } catch (\Exception $refundException) {
                    \Log::error('Error refunding payment: ' . $refundException->getMessage());
                }
            }
            
            return response()->json([
                'message' => 'Failed to process gift card. Your payment has been refunded.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your gift card.',
            ], 500);
        }
    }

    /**
     * Display the specified gift card.
     *
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($code)
    {
        $giftCard = GiftCard::where('code', $code)->firstOrFail();
        
        return response()->json([
            'data' => $giftCard,
            'status' => $this->getGiftCardStatus($giftCard)
        ]);
    }

    /**
     * Check gift card balance.
     *
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkBalance($code)
    {
        $giftCard = GiftCard::where('code', $code)->firstOrFail();
        
        return response()->json([
            'code' => $giftCard->code,
            'balance' => $giftCard->balance,
            'currency' => 'USD',
            'status' => $this->getGiftCardStatus($giftCard),
            'expires_at' => $giftCard->expires_at?->toDateString(),
        ]);
    }

    /**
     * Redeem a gift card.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function redeem(Request $request, $code)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'user_id' => 'required|exists:users,id',
        ]);

        $giftCard = GiftCard::where('code', $code)->firstOrFail();
        
        if ($giftCard->is_redeemed) {
            return response()->json([
                'message' => 'This gift card has already been redeemed.',
            ], 400);
        }
        
        if ($giftCard->isExpired()) {
            return response()->json([
                'message' => 'This gift card has expired.',
            ], 400);
        }
        
        if (!$giftCard->is_active) {
            return response()->json([
                'message' => 'This gift card is not active.',
            ], 400);
        }
        
        if ($validated['amount'] > $giftCard->balance) {
            return response()->json([
                'message' => 'Insufficient balance on gift card.',
                'balance' => $giftCard->balance,
            ], 400);
        }
        
        // Update gift card balance
        $giftCard->balance -= $validated['amount'];
        
        // Mark as redeemed if balance is zero or if full amount is being used
        if ($giftCard->balance <= 0 || $validated['amount'] >= $giftCard->amount) {
            $giftCard->is_redeemed = true;
            $giftCard->redeemed_at = now();
            $giftCard->redeemed_by = $validated['user_id'];
            $giftCard->balance = 0; // Ensure balance doesn't go negative
        }
        
        $giftCard->save();
        
        return response()->json([
            'message' => 'Gift card redeemed successfully',
            'remaining_balance' => $giftCard->balance,
            'is_fully_redeemed' => $giftCard->is_redeemed,
        ]);
    }

    /**
     * Update the specified gift card in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $giftCard = GiftCard::findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:1',
            'balance' => 'sometimes|numeric|min:0',
            'recipient_name' => 'sometimes|string|max:255',
            'recipient_email' => 'sometimes|email|max:255',
            'sender_name' => 'sometimes|string|max:255',
            'message' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'is_redeemed' => 'sometimes|boolean',
        ]);
        
        $giftCard->update($validated);
        
        return response()->json([
            'message' => 'Gift card updated successfully',
            'data' => $giftCard
        ]);
    }

    /**
     * Deactivate a gift card.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate($id)
    {
        $giftCard = GiftCard::findOrFail($id);
        $giftCard->is_active = false;
        $giftCard->save();
        
        return response()->json([
            'message' => 'Gift card deactivated successfully',
            'data' => $giftCard
        ]);
    }

    /**
     * Generate a unique gift card code.
     *
     * @return string
     */
    protected function generateUniqueCode()
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (GiftCard::where('code', $code)->exists());
        
        return $code;
    }
    
    /**
     * Get the status of a gift card.
     *
     * @param  \App\Models\GiftCard  $giftCard
     * @return string
     */
    protected function getGiftCardStatus($giftCard)
    {
        if ($giftCard->is_redeemed) {
            return 'redeemed';
        }
        
        if ($giftCard->isExpired()) {
            return 'expired';
        }
        
        if (!$giftCard->is_active) {
            return 'inactive';
        }
        
        return 'active';
    }
}
