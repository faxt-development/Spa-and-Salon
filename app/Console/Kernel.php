<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send appointment reminders every hour
        $schedule->command('appointments:send-reminders')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/appointment-reminders.log'));
                 
        // Prune old notifications weekly
        $schedule->command('model:prune', [
            '--model' => [
                \App\Models\Notification::class,
            ]
        ])->weekly();
        
        // Welcome series drip campaign - run daily at 9:00 AM
        $schedule->command('email:send-welcome-series')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/welcome-series.log'));
        
        // Birthday promotion drip campaign - run daily at 10:00 AM
        $schedule->command('email:send-birthday-promotion --days=30')
                 ->dailyAt('10:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/birthday-promotions.log'));
        
        // Re-engagement drip campaign - run weekly on Mondays at 11:00 AM
        $schedule->command('email:send-reengagement-campaign --min-days=90 --max-days=365')
                 ->weeklyOn(1, '11:00') // Monday at 11:00 AM
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/reengagement-campaigns.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
