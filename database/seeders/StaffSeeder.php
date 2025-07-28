<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Staff;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get services to assign to staff
        $services = Service::all();

        // Get the test company to associate staff with
        $company = Company::where('id', 1)->first();

        if (!$company) {
            $this->command->warn('No test company found. Staff will not be associated with any company.');
            return;
        }

        // Ensure all expected staff members exist
        $expectedStaff = [
            'alex.johnson@example.com' => 'Alex Johnson',
            'taylor.smith@example.com' => 'Taylor Smith',
            'jordan.williams@example.com' => 'Jordan Williams',
        ];

        $staffMembers = [];
        foreach ($expectedStaff as $email => $name) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'onboarding_completed' => true,
                ]);
                $user->assignRole('staff');
            }

            $staff = Staff::updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => $user->id,
                    'first_name' => $name,
                    'last_name' => '',
                    'phone' => '555-01' . (array_search($email, array_keys($expectedStaff)) + 1),
                    'position' => array_search($email, array_keys($expectedStaff)) === 0 ? 'Senior Stylist' : 
                                 (array_search($email, array_keys($expectedStaff)) === 1 ? 'Color Specialist' : 'Massage Therapist'),
                    'bio' => 'Experienced professional',
                    'active' => true,
                ]
            );
            $staffMembers[] = $staff;
        }

        $this->command->info('Ensured ' . count($staffMembers) . ' staff members exist. Assigning services...');

        // Assign services to staff based on their expertise
        $serviceAssignments = [
            'alex.johnson@example.com' => ['Women\'s Haircut', 'Men\'s Haircut', 'Blowout', 'Updo'],
            'taylor.smith@example.com' => ['Full Color', 'Highlights', 'Balayage', 'Color Correction'],
            'jordan.williams@example.com' => ['Women\'s Haircut', 'Men\'s Haircut', 'Blowout', 'Updo', 'Extensions'],
            'testadmin@example.com' => ['Women\'s Haircut', 'Men\'s Haircut', 'Consultation'],
        ];

        foreach ($staffMembers as $staff) {
            $staffUser = User::where('email', $staff->email)->first();
            if (!$staffUser) continue;

            // Get appropriate services for this staff member
            $assignedServices = [];
            if (isset($serviceAssignments[$staff->email])) {
                $serviceNames = $serviceAssignments[$staff->email];
                $assignedServices = $services->filter(function ($service) use ($serviceNames) {
                    return in_array($service->name, $serviceNames);
                });
            } else {
                // Default assignment for any other staff
                $assignedServices = $services->random(min(3, $services->count()));
            }

            if ($assignedServices->isNotEmpty()) {
                $staff->services()->sync($assignedServices->pluck('id')->toArray());
                $this->command->info("Assigned {$assignedServices->count()} services to {$staff->first_name} {$staff->last_name}");
            }
        }
    }
}
