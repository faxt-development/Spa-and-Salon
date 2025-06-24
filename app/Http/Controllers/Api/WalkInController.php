<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalkIn;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WalkInController extends Controller
{
    /**
     * Get walk-in queue statistics
     * 
     * @return JsonResponse
     */
    public function getQueueStats(): JsonResponse
    {
        try {
            Log::info('Fetching walk-in queue stats');
            
            // Get count of waiting walk-ins
            $waitingCount = WalkIn::where('status', 'waiting')->count();
            Log::debug("Found {$waitingCount} waiting walk-ins");
            
            // Calculate estimated wait time (simplified: 15 minutes per waiting party)
            $estimatedWaitMinutes = $waitingCount * 15;
            
            // Format wait time as "~X min" or "~Y hr X min" if over 60 minutes
            $waitTimeFormatted = $this->formatWaitTime($estimatedWaitMinutes);
            
            $response = [
                'waiting_count' => $waitingCount,
                'estimated_wait_minutes' => $estimatedWaitMinutes,
                'wait_time_formatted' => $waitTimeFormatted,
            ];
            
            Log::debug('Walk-in queue stats response', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            $errorMessage = 'Error fetching walk-in queue stats: ' . $e->getMessage();
            Log::error($errorMessage, [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'waiting_count' => 0,
                'estimated_wait_minutes' => 0,
                'wait_time_formatted' => 'N/A',
                'error' => 'Failed to fetch walk-in queue statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Format wait time in minutes to a human-readable string
     * 
     * @param int $minutes
     * @return string
     */
    private function formatWaitTime(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'Ready';
        }
        
        if ($minutes < 60) {
            return "~{$minutes} min";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes === 0) {
            return "~{$hours} hr";
        }
        
        return "~{$hours} hr {$remainingMinutes} min";
    }
}
