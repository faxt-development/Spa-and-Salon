<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the client user
        $user = \App\Models\User::where('email', 'client@example.com')->first();
        
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => 'Sample Client',
                'email' => 'client@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('client');
        }

        // Get or create a sample client linked to the user
        $client = Client::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => 'Sample',
                'last_name' => 'Client',
                'phone' => '555-0000',
                'email' => 'client@example.com',
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Get the staff members
        $alex = Staff::where('email', 'alex.johnson@example.com')->first();
        $taylor = Staff::where('email', 'taylor.smith@example.com')->first();

        if (!$alex || !$taylor) {
            $this->call(StaffSeeder::class);
            $alex = Staff::where('email', 'alex.johnson@example.com')->first();
            $taylor = Staff::where('email', 'taylor.smith@example.com')->first();
        }

        // Get or create services
        $haircutService = Service::firstOrCreate(
            ['name' => 'Haircut & Styling'],
            [
                'name' => 'Haircut & Styling',
                'description' => 'Professional haircut and styling',
                'duration' => 60,
                'price' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $colorService = Service::firstOrCreate(
            ['name' => 'Hair Color'],
            [
                'name' => 'Hair Color',
                'description' => 'Professional hair coloring service',
                'duration' => 120,
                'price' => 90.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $appointments = [
            [
                'client_id' => $client->id,
                'staff_id' => $alex->id,
                'start_time' => Carbon::now()->addDays(2)->setTime(14, 30),
                'end_time' => Carbon::now()->addDays(2)->setTime(15, 30),
                'status' => 'scheduled',
                'notes' => 'Please arrive 10 minutes early for a consultation.',
                'total_price' => 50.00,
                'is_paid' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $client->id,
                'staff_id' => $taylor->id,
                'start_time' => Carbon::now()->addDays(5)->setTime(11, 0),
                'end_time' => Carbon::now()->addDays(5)->setTime(13, 0),
                'status' => 'scheduled',
                'notes' => 'Bring reference photos if you have any.',
                'total_price' => 90.00,
                'is_paid' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($appointments as $appointment) {
            $newAppointment = Appointment::updateOrCreate(
                [
                    'client_id' => $appointment['client_id'],
                    'staff_id' => $appointment['staff_id'],
                    'start_time' => $appointment['start_time'],
                ],
                $appointment
            );

            // Attach services to appointments with price and duration
            if ($appointment['staff_id'] === $alex->id) {
                $newAppointment->services()->syncWithoutDetaching([
                    $haircutService->id => [
                        'price' => $haircutService->price,
                        'duration' => $haircutService->duration,
                        'staff_id' => $alex->id,
                        'notes' => $appointment['notes']
                    ]
                ]);
            } else {
                $newAppointment->services()->syncWithoutDetaching([
                    $colorService->id => [
                        'price' => $colorService->price,
                        'duration' => $colorService->duration,
                        'staff_id' => $taylor->id,
                        'notes' => $appointment['notes']
                    ]
                ]);
            }
        }
    }
}
