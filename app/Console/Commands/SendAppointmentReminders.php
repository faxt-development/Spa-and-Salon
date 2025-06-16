<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for upcoming appointments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();
        
        // Define the time windows for reminders (in hours)
        $reminderWindows = [24, 48]; // 24h and 48h before appointment
        
        foreach ($reminderWindows as $hours) {
            $windowStart = $now->copy()->addHours($hours);
            $windowEnd = $windowStart->copy()->addHour();
            
            $appointments = Appointment::with(['client', 'service', 'staff'])
                ->where('scheduled_at', '>=', $windowStart)
                ->where('scheduled_at', '<=', $windowEnd)
                ->where('is_cancelled', false)
                ->whereDoesntHave('notifications', function($query) use ($hours) {
                    $query->where('type', 'reminder')
                          ->where('data->hours_before', $hours);
                })
                ->get();
            
            foreach ($appointments as $appointment) {
                // Dispatch a job to send the reminder
                dispatch(new \App\Jobs\SendAppointmentReminder($appointment, $hours))
                    ->onQueue('emails');
                
                // Log that we've sent this reminder
                $appointment->notifications()->create([
                    'type' => 'reminder',
                    'data' => [
                        'hours_before' => $hours,
                        'sent_at' => now()->toDateTimeString(),
                    ],
                ]);
                
                $this->info("Scheduled {$hours}h reminder for appointment #{$appointment->id} with {$appointment->client->email}");
            }
        }
        
        return Command::SUCCESS;
    }
}
