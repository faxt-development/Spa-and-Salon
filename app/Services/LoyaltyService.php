<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function createAccount(int $clientId, int $programId): LoyaltyAccount
    {
        return DB::transaction(function () use ($clientId, $programId) {
            $program = LoyaltyProgram::findOrFail($programId);
            
            $account = LoyaltyAccount::create([
                'client_id' => $clientId,
                'loyalty_program_id' => $program->id,
                'points_balance' => $program->signup_points,
                'points_earned_lifetime' => $program->signup_points,
            ]);

            if ($program->signup_points > 0) {
                $this->createTransaction(
                    $account,
                    LoyaltyTransaction::TYPE_EARN,
                    $program->signup_points,
                    'Signup bonus',
                    null
                );
            }

            return $account;
        });
    }

    public function earnPoints(Order $order): void
    {
        if (!$order->client_id) {
            return;
        }

        $account = LoyaltyAccount::firstOrCreate(
            ['client_id' => $order->client_id],
            ['loyalty_program_id' => 1] // Default program
        );

        $pointsEarned = $this->calculatePointsEarned($order, $account);
        
        if ($pointsEarned <= 0) {
            return;
        }

        DB::transaction(function () use ($account, $pointsEarned, $order) {
            $account->increment('points_balance', $pointsEarned);
            $account->increment('points_earned_lifetime', $pointsEarned);
            $account->update(['last_activity_at' => now()]);

            $this->createTransaction(
                $account,
                LoyaltyTransaction::TYPE_EARN,
                $pointsEarned,
                'Purchase #' . $order->id,
                $order
            );

            $order->update(['loyalty_points_earned' => $pointsEarned]);
        });
    }

    public function redeemPoints(LoyaltyAccount $account, int $points, Order $order): void
    {
        if ($points <= 0 || $account->points_balance < $points) {
            throw new \InvalidArgumentException('Insufficient points');
        }

        $program = $account->program;
        $discountAmount = $points * $program->currency_per_point;

        DB::transaction(function () use ($account, $points, $discountAmount, $order, $program) {
            $account->decrement('points_balance', $points);
            $account->increment('points_redeemed_lifetime', $points);
            $account->update(['last_activity_at' => now()]);

            $this->createTransaction(
                $account,
                LoyaltyTransaction::TYPE_REDEEM,
                -$points,
                'Redemption for order #' . $order->id,
                $order
            );

            $order->update([
                'loyalty_points_redeemed' => $points,
                'loyalty_discount_amount' => $discountAmount,
                'discount_amount' => DB::raw("discount_amount + $discountAmount"),
                'total_amount' => DB::raw("GREATEST(0, total_amount - $discountAmount)")
            ]);
        });
    }

    protected function calculatePointsEarned(Order $order, LoyaltyAccount $account): int
    {
        $program = $account->program;
        $tier = $account->currentTier();
        $multiplier = $tier ? $tier->multiplier : 1.0;
        
        $points = (int) round(($order->total_amount - $order->loyalty_discount_amount) 
            * $program->points_per_currency 
            * $multiplier);
            
        return max(0, $points);
    }

    protected function createTransaction(
        LoyaltyAccount $account,
        string $type,
        int $points,
        string $description,
        $reference = null
    ): LoyaltyTransaction {
        return $account->transactions()->create([
            'type' => $type,
            'points' => $points,
            'points_value' => $points * $account->program->currency_per_point,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'expires_at' => $type === LoyaltyTransaction::TYPE_EARN 
                ? now()->addYear() 
                : null,
        ]);
    }
}
