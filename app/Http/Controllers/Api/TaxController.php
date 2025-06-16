<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\TaxRate;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get all active tax rates.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $taxRates = TaxRate::active()
            ->orderBy('name')
            ->get()
            ->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'code' => $rate->code,
                    'rate' => (float) $rate->rate,
                    'type' => $rate->type,
                    'is_inclusive' => (bool) $rate->is_inclusive,
                    'description' => $rate->description,
                    'applies_to' => $rate->applies_to,
                    'effective_from' => $rate->effective_from?->toDateTimeString(),
                    'expires_at' => $rate->expires_at?->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $taxRates,
        ]);
    }

    /**
     * Calculate tax for a product or service.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'itemable_type' => 'required|in:product,service',
            'itemable_id' => 'required|integer|min:1',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $itemableType = $data['itemable_type'] === 'product' ? Product::class : Service::class;
        
        // Find the item to get its category for tax calculation
        $item = $itemableType::findOrFail($data['itemable_id']);
        
        // Create a temporary order item for calculation
        $orderItem = new \App\Models\OrderItem([
            'itemable_type' => $itemableType,
            'itemable_id' => $item->id,
            'name' => $item->name,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount' => $data['discount'] ?? 0,
        ]);
        
        // Calculate tax
        $taxableAmount = $orderItem->calculateTaxableAmount();
        $taxAmount = $orderItem->calculateTaxAmount(true);
        $total = $taxableAmount + $taxAmount;
        
        // Get tax breakdown
        $taxRates = $this->getApplicableTaxRates($orderItem);
        
        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $orderItem->quantity * $orderItem->unit_price,
                'discount' => $orderItem->discount,
                'taxable_amount' => $taxableAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'tax_rates' => $taxRates->map(function ($rate) use ($taxableAmount) {
                    return [
                        'id' => $rate->id,
                        'name' => $rate->name,
                        'rate' => (float) $rate->rate,
                        'amount' => $rate->calculateTax($taxableAmount),
                        'is_inclusive' => (bool) $rate->is_inclusive,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get tax breakdown for an order.
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function orderBreakdown(Order $order): JsonResponse
    {
        $breakdown = $this->orderService->getTaxBreakdown($order);
        
        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Get applicable tax rates for an order item.
     *
     * @param \App\Models\OrderItem $item
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getApplicableTaxRates(\App\Models\OrderItem $item)
    {
        $taxService = app(\App\Services\TaxCalculationService::class);
        return $taxService->getApplicableTaxRatesForItem($item);
    }
}
