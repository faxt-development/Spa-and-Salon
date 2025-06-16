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

    /**
     * Store a newly created gift card in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000',
            'recipient_name' => 'required|string|max:255',
            'recipient_email' => 'required|email|max:255',
            'sender_name' => 'required|string|max:255',
            'message' => 'nullable|string',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $giftCard = new GiftCard();
        $giftCard->code = $this->generateUniqueCode();
        $giftCard->amount = $validated['amount'];
        $giftCard->balance = $validated['amount']; // Initial balance equals the amount
        $giftCard->recipient_name = $validated['recipient_name'];
        $giftCard->recipient_email = $validated['recipient_email'];
        $giftCard->sender_name = $validated['sender_name'];
        $giftCard->message = $validated['message'] ?? null;
        $giftCard->expires_at = $validated['expires_at'] ?? now()->addYear();
        $giftCard->is_active = true;
        $giftCard->save();

        return response()->json([
            'message' => 'Gift card created successfully',
            'data' => $giftCard
        ], 201);
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
