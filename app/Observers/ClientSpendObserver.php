<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\Payment;
use Carbon\Carbon;

class ClientSpendObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $this->updateClientSpend($payment);
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Only update if amount or status changed
        if ($payment->isDirty(['amount', 'status'])) {
            $this->updateClientSpend($payment);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        $this->updateClientSpend($payment);
    }

    /**
     * Update client's spend metrics
     */
    protected function updateClientSpend(Payment $payment): void
    {
        if (!$payment->client_id) return;

        $client = Client::find($payment->client_id);
        if (!$client) return;

        // Recalculate total spent from all completed payments
        $totalSpent = Payment::where('client_id', $client->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Get visit count from completed appointments
        $visitCount = $client->appointments()
            ->where('status', 'completed')
            ->count();

        // Get first and last visit dates
        $firstVisit = $client->appointments()
            ->where('status', 'completed')
            ->orderBy('start_time')
            ->first();

        $lastVisit = $client->appointments()
            ->where('status', 'completed')
            ->orderBy('start_time', 'desc')
            ->first();

        // Update client metrics
        $client->update([
            'total_spent' => $totalSpent,
            'visit_count' => $visitCount,
            'first_visit_at' => $firstVisit ? $firstVisit->start_time : null,
            'last_visit' => $lastVisit ? $lastVisit->start_time : null,
        ]);
    }
}
