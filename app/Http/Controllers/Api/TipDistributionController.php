<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TipDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TipDistributionController extends Controller
{
    protected $tipDistributionService;

    public function __construct(TipDistributionService $tipDistributionService)
    {
        $this->tipDistributionService = $tipDistributionService;
    }

    /**
     * Distribute tips for a transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $transactionId
     * @return \Illuminate\Http\Response
     */
    public function distribute(Request $request, $transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        
        $validated = $request->validate([
            'distribution_method' => [
                'required', 
                'string', 
                Rule::in(['individual', 'pooled', 'split'])
            ],
            'distribution_data' => [
                'required_if:distribution_method,split',
                'array',
                'min:1',
            ],
            'distribution_data.*' => [
                'required_if:distribution_method,split',
                'numeric',
                'min:0',
                'max:100',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        
        try {
            $distributionData = $validated['distribution_method'] === 'split' 
                ? $validated['distribution_data'] 
                : [];
                
            $distributions = $this->tipDistributionService->distributeTips(
                $transaction,
                $validated['distribution_method'],
                $distributionData,
                $validated['notes'] ?? null
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Tips distributed successfully.',
                'data' => [
                    'distributions' => $distributions,
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to distribute tips: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to distribute tips: ' . $e->getMessage(),
            ], 422);
        }
    }
    
    /**
     * Get tip distribution summary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'staff_id' => ['nullable', 'exists:staff,id'],
        ]);
        
        try {
            $summary = $this->tipDistributionService->getTipDistributionSummary([
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'staff_id' => $validated['staff_id'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get tip distribution summary: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tip distribution summary.',
            ], 500);
        }
    }
}
