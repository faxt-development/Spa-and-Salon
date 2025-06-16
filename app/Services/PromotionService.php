<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    /**
     * Find a valid promotion by code.
     */
    public function findValidPromotion(string $code, ?User $user = null): ?Promotion
    {
        $promotion = Promotion::where('code', $code)
            ->where('is_active', true)
            ->where('is_public', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->first();

        if (!$promotion) {
            return null;
        }

        // Check usage limits
        if ($promotion->usage_limit) {
            $usageCount = $promotion->usages()->count();
            if ($usageCount >= $promotion->usage_limit) {
                return null;
            }
        }

        // Check per-user usage limits
        if ($user && $promotion->restrictions && isset($promotion->restrictions['per_user_limit'])) {
            $userUsageCount = $promotion->usages()
                ->where('user_id', $user->id)
                ->count();
                
            if ($userUsageCount >= $promotion->restrictions['per_user_limit']) {
                return null;
            }
        }

        return $promotion;
    }

    /**
     * Apply a promotion to a cart or booking.
     *
     * @param string $code
     * @param array $items Array of items with service_id and price
     * @param User|null $user
     * @return array
     */
    public function applyPromotion(string $code, array $items, ?User $user = null): array
    {
        $promotion = $this->findValidPromotion($code, $user);
        
        if (!$promotion) {
            throw ValidationException::withMessages([
                'promo_code' => ['Invalid or expired promotion code.']
            ]);
        }

        $total = collect($items)->sum('price');
        $context = [
            'items' => $items,
            'user' => $user,
            'total' => $total,
        ];

        $result = $promotion->applyToAmount($total, $context);

        if (!$result['is_valid']) {
            throw ValidationException::withMessages([
                'promo_code' => [$result['message']]
            ]);
        }

        return [
            'promotion' => $promotion,
            'discount' => $result['discount'],
            'final_amount' => $result['final_amount'],
        ];
    }

    /**
     * Record that a promotion was used.
     */
    public function recordPromotionUsage(
        Promotion $promotion,
        ?User $user = null,
        ?int $bookingId = null,
        array $appliedTo = []
    ): PromotionUsage {
        return $promotion->recordUsage(
            $user?->id,
            $bookingId,
            $appliedTo
        );
    }

    /**
     * Get promotion statistics.
     */
    public function getPromotionStats(Promotion $promotion, array $filters = []): array
    {
        $query = $promotion->usages();

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date']));
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        $usageCount = $query->count();
        $totalDiscount = $query->sum('discount_amount');
        $usageByUser = $query->selectRaw('user_id, COUNT(*) as usage_count')
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('usage_count', 'desc')
            ->get();

        return [
            'total_usage' => $usageCount,
            'total_discount' => $totalDiscount,
            'average_discount' => $usageCount > 0 ? $totalDiscount / $usageCount : 0,
            'usage_by_user' => $usageByUser,
            'remaining_uses' => $promotion->usage_limit ? $promotion->usage_limit - $usageCount : null,
        ];
    }

    /**
     * Get all active promotions.
     */
    public function getActivePromotions(): Collection
    {
        return Promotion::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get();
    }
}
