<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommissionPaymentResource;
use App\Http\Resources\StaffPerformanceMetricResource;
use App\Models\CommissionPayment;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommissionPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'staff_id' => 'sometimes|exists:staff,id',
            'status' => 'sometimes|in:pending,processing,paid,cancelled',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = CommissionPayment::with(['staff', 'processor'])
            ->latest('created_at');

        if (isset($validated['staff_id'])) {
            $query->where('staff_id', $validated['staff_id']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['start_date'])) {
            $query->where('end_date', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->where('start_date', '<=', $validated['end_date']);
        }

        $perPage = $validated['per_page'] ?? 15;
        $payments = $query->paginate($perPage);

        return CommissionPaymentResource::collection($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'period_name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check for overlapping payments
        $exists = CommissionPayment::where('staff_id', $validated['staff_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A commission payment already exists for this staff member during the specified period',
            ], 422);
        }

        $payment = CommissionPayment::create([
            'staff_id' => $validated['staff_id'],
            'period_name' => $validated['period_name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'amount' => $validated['amount'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'processed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Commission payment created successfully',
            'data' => new CommissionPaymentResource($payment->load(['staff', 'processor'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CommissionPayment $commissionPayment): CommissionPaymentResource
    {
        return new CommissionPaymentResource(
            $commissionPayment->load(['staff', 'processor', 'performanceMetrics'])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommissionPayment $commissionPayment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'processing', 'paid', 'cancelled'])],
            'notes' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        // If marking as paid, set the paid_at timestamp
        if (isset($validated['status']) && $validated['status'] === 'paid' && !$commissionPayment->paid_at) {
            $validated['paid_at'] = now();
            $validated['processed_by'] = $request->user()->id;
        }

        $commissionPayment->update($validated);

        return response()->json([
            'message' => 'Commission payment updated successfully',
            'data' => new CommissionPaymentResource($commissionPayment->fresh(['staff', 'processor'])),
        ]);
    }

    /**
     * Get the performance metrics for a commission payment.
     */
    public function metrics(CommissionPayment $commissionPayment): AnonymousResourceCollection
    {
        $metrics = $commissionPayment->performanceMetrics()
            ->with('staff')
            ->orderBy('date')
            ->get();

        return StaffPerformanceMetricResource::collection($metrics);
    }

    /**
     * Get the summary of commission payments by status.
     */
    public function summary(): JsonResponse
    {
        $summary = CommissionPayment::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $pending = $summary['pending']->total ?? 0;
        $processing = $summary['processing']->total ?? 0;
        $paid = $summary['paid']->total ?? 0;
        $cancelled = $summary['cancelled']->total ?? 0;

        return response()->json([
            'data' => [
                'total_payments' => $summary->sum('count'),
                'total_amount' => $summary->sum('total'),
                'pending' => [
                    'count' => $summary['pending']->count ?? 0,
                    'amount' => $pending,
                ],
                'processing' => [
                    'count' => $summary['processing']->count ?? 0,
                    'amount' => $processing,
                ],
                'paid' => [
                    'count' => $summary['paid']->count ?? 0,
                    'amount' => $paid,
                ],
                'cancelled' => [
                    'count' => $summary['cancelled']->count ?? 0,
                    'amount' => $cancelled,
                ],
                'outstanding' => $pending + $processing,
                'paid_to_date' => $paid,
            ],
        ]);
    }

    /**
     * Get the commission payment history for a staff member.
     */
    public function staffHistory(Staff $staff): AnonymousResourceCollection
    {
        $payments = $staff->commissionPayments()
            ->with('processor')
            ->latest('end_date')
            ->paginate(12);

        return CommissionPaymentResource::collection($payments);
    }
}
