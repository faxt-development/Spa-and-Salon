<?php

namespace App\Listeners;

use App\Events\ModelStateChanged;
use Illuminate\Support\Facades\Log;

class LogModelStateChange
{
    /**
     * Handle the event.
     */
    public function handle(ModelStateChanged $event): void
    {
        $stateChange = $event->stateChange;
        
        // Log the state change
        Log::channel('state_changes')->info('Model state changed', [
            'model' => $stateChange['model_type'] . ':' . $stateChange['model_id'],
            'event' => $stateChange['event_type'],
            'user_id' => $stateChange['user_id'],
            'ip' => $stateChange['ip_address'],
            'changes' => $stateChange['changes'] ?? null,
        ]);

        // Here you could also:
        // 1. Send notifications
        // 2. Update analytics
        // 3. Trigger other business processes
    }
}
