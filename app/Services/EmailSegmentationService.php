<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmailSegmentationService
{
    /**
     * Get clients who have made appointments in the last X days
     *
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentClients(int $days = 30)
    {
        return Client::whereHas('appointments', function($query) use ($days) {
            $query->where('scheduled_at', '>=', now()->subDays($days));
        })->get();
    }

    /**
     * Get clients who haven't booked in X days
     *
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInactiveClients(int $days = 60)
    {
        return Client::whereDoesntHave('appointments', function($query) use ($days) {
            $query->where('scheduled_at', '>=', now()->subDays($days));
        })->get();
    }

    /**
     * Get clients who have spent over a certain amount
     * 
     * @param float $amount
     * @param string $timeframe (all_time, this_year, this_month, last_90_days)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighValueClients(float $amount = 500, string $timeframe = 'all_time')
    {
        $query = Client::query();
        
        // Add timeframe filter
        if ($timeframe !== 'all_time') {
            $date = match($timeframe) {
                'this_year' => now()->startOfYear(),
                'this_month' => now()->startOfMonth(),
                'last_90_days' => now()->subDays(90),
                default => now()->subYears(100), // All time
            };
            
            $query->whereHas('appointments', function($q) use ($date) {
                $q->where('scheduled_at', '>=', $date);
            });
        }
        
        // Get clients with total spending over the amount
        return $query->withSum(['appointments as total_spent' => function($q) use ($timeframe) {
            $q->select(\DB::raw('COALESCE(SUM(price), 0)'));
            
            if ($timeframe !== 'all_time') {
                $date = match($timeframe) {
                    'this_year' => now()->startOfYear(),
                    'this_month' => now()->startOfMonth(),
                    'last_90_days' => now()->subDays(90),
                    default => now()->subYears(100),
                };
                
                $q->where('scheduled_at', '>=', $date);
            }
        }], 'appointments.price')
        ->having('total_spent', '>=', $amount)
        ->get();
    }

    /**
     * Get clients who have shown interest in specific services
     * 
     * @param array $serviceIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClientsByServiceInterest(array $serviceIds)
    {
        return Client::whereHas('appointments', function($query) use ($serviceIds) {
            $query->whereHas('services', function($q) use ($serviceIds) {
                $q->whereIn('services.id', $serviceIds);
            });
        })->get();
    }

    /**
     * Get clients with upcoming birthdays
     * 
     * @param int $daysAhead How many days in advance to check
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClientsWithUpcomingBirthdays(int $daysAhead = 30)
    {
        $clients = Client::whereNotNull('date_of_birth')
            ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                now()->format('m-d'),
                now()->addDays($daysAhead)->format('m-d')
            ])
            ->get();
            
        return $clients;
    }

    /**
     * Get clients who have never made a purchase
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProspects()
    {
        return Client::doesntHave('appointments')->get();
    }

    /**
     * Get clients who have canceled appointments
     * 
     * @param int $days Look back period in days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClientsWithCancellations(int $days = 90)
    {
        return Client::whereHas('appointments', function($query) use ($days) {
            $query->where('is_cancelled', true)
                  ->where('updated_at', '>=', now()->subDays($days));
        })->get();
    }
}
