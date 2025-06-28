<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use App\Models\Order;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
        $this->middleware('auth:sanctum');
    }

    public function getAccount()
    {
        $account = LoyaltyAccount::firstOrCreate(
            ['client_id' => Auth::id()],
            ['loyalty_program_id' => 1] // Default program
        );

        return response()->json([
            'points_balance' => $account->points_balance,
            'tier' => $account->currentTier(),
            'transactions' => $account->transactions()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
        ]);
    }

    public function redeemPoints(Request $request, Order $order)
    {
        $request->validate([
            'points' => 'required|integer|min:1'
        ]);

        $account = LoyaltyAccount::where('client_id', Auth::id())->firstOrFail();
        
        if ($order->client_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Cannot apply points to this order'], 400);
        }

        try {
            $this->loyaltyService->redeemPoints(
                $account,
                $request->points,
                $order
            );

            return response()->json([
                'message' => 'Points redeemed successfully',
                'order_total' => $order->fresh()->total_amount
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getEligiblePromotions(Order $order)
    {
        $promotions = \App\Models\Promotion::where('is_active', true)
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
            ->get()
            ->filter(function ($promotion) use ($order) {
                // Additional eligibility checks can be added here
                return true;
            });

        return response()->json($promotions);
    }
}
