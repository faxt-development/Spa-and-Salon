<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\EmailSegmentationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestEmailSegmentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-segmentation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the email segmentation service';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(EmailSegmentationService $segmentationService)
    {
        $this->info('Testing Email Segmentation Service');
        $this->newLine();
        
        // Test recent clients
        $this->info('Recent Clients (last 30 days):');
        $recentClients = $segmentationService->getRecentClients(30);
        $this->displayClientList($recentClients);
        
        // Test inactive clients
        $this->info('Inactive Clients (no bookings in 60+ days):');
        $inactiveClients = $segmentationService->getInactiveClients(60);
        $this->displayClientList($inactiveClients);
        
        // Test high value clients
        $this->info('High Value Clients (spent $500+ this year):');
        $highValueClients = $segmentationService->getHighValueClients(500, 'this_year');
        $this->displayClientList($highValueClients);
        
        // Test upcoming birthdays
        $this->info('Clients with Upcoming Birthdays (next 30 days):');
        $birthdayClients = $segmentationService->getClientsWithUpcomingBirthdays(30);
        $this->displayClientList($birthdayClients);
        
        // Test prospects
        $this->info('Prospects (no appointments):');
        $prospects = $segmentationService->getProspects();
        $this->displayClientList($prospects);
        
        return Command::SUCCESS;
    }
    
    /**
     * Display a list of clients in a table
     * 
     * @param \Illuminate\Database\Eloquent\Collection $clients
     * @return void
     */
    protected function displayClientList($clients)
    {
        if ($clients->isEmpty()) {
            $this->line('  No clients found');
            $this->newLine();
            return;
        }
        
        $this->table(
            ['ID', 'Name', 'Email', 'Phone', 'Last Visit'],
            $clients->map(function($client) {
                $lastAppointment = $client->appointments()->latest('scheduled_at')->first();
                
                return [
                    'id' => $client->id,
                    'name' => $client->full_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'last_visit' => $lastAppointment ? $lastAppointment->scheduled_at->format('M j, Y') : 'Never',
                ];
            })
        );
        $this->line("Total: " . $clients->count() . " clients");
        $this->newLine();
    }
}
