<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get services to assign to staff
        $services = Service::all();
        
        $staffMembers = [
            [
                'first_name' => 'Alex',
                'last_name' => 'Johnson',
                'email' => 'alex.johnson@example.com',
                'phone' => '555-0101',
                'position' => 'Senior Stylist',
                'bio' => 'Specializes in modern cuts and styling.',
                'active' => true,
                'password' => 'password',
                'service_types' => ['Women\'s Haircut', 'Men\'s Haircut', 'Blowout', 'Updo']
            ],
            [
                'first_name' => 'Taylor',
                'last_name' => 'Smith',
                'email' => 'taylor.smith@example.com',
                'phone' => '555-0102',
                'position' => 'Color Specialist',
                'bio' => 'Expert in hair coloring and treatments.',
                'active' => true,
                'password' => 'password',
                'service_types' => ['Full Color', 'Highlights', 'Balayage', 'Color Correction']
            ],
            [
                'first_name' => 'Jordan',
                'last_name' => 'Williams',
                'email' => 'jordan.williams@example.com',
                'phone' => '555-0103',
                'position' => 'Master Stylist',
                'bio' => 'Specializes in precision cuts and styling.',
                'active' => true,
                'password' => 'password',
                'service_types' => ['Women\'s Haircut', 'Men\'s Haircut', 'Blowout', 'Updo', 'Extensions']
            ]
        ];

        foreach ($staffMembers as $staffData) {
            // Create or get user
            $user = User::firstOrCreate(
                ['email' => $staffData['email']],
                [
                    'name' => $staffData['first_name'] . ' ' . $staffData['last_name'],
                    'password' => Hash::make($staffData['password']),
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ]
            );
            
            // Ensure the user has the staff role
            if (!$user->hasRole('staff')) {
                $user->assignRole('staff');
            }

            // Create or update staff member
            $staff = Staff::updateOrCreate(
                ['email' => $staffData['email']],
                [
                    'first_name' => $staffData['first_name'],
                    'last_name' => $staffData['last_name'],
                    'email' => $staffData['email'],
                    'phone' => $staffData['phone'],
                    'position' => $staffData['position'],
                    'bio' => $staffData['bio'],
                    'active' => $staffData['active'],
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Attach services to staff member
            if (isset($staffData['service_types']) && !empty($staffData['service_types'])) {
                $serviceIds = $services->whereIn('name', $staffData['service_types'])
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($serviceIds)) {
                    $staff->services()->syncWithoutDetaching($serviceIds);
                }
            }
        }
    }
}
