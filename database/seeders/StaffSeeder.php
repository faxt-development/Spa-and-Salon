<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Company;
use App\Models\Staff;
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
        $company = Company::where('domain', 'test-spa.localhost')->first();

        if (!$company) {
         dd('no company found');   $this->command->warn('No test company found. Staff will not be associated with any company.');
            return;
        }

        // Get the admin user and add them as a staff member
        $admin = User::where('email', 'testadmin@example.com')->first();

        if ($admin) {
            // Create staff record for admin if it doesn't exist
            $adminStaff = Staff::firstOrCreate(
                ['user_id' => $admin->id],
                [
                    'first_name' => $admin->name,
                    'last_name' => '',
                    'email' => $admin->email,
                    'phone' => '555-0100',
                    'position' => 'Owner/Manager',
                    'bio' => 'Salon owner and manager.',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->info('Admin user added as staff member.');

            // Assign some services to admin
            if ($services->count() > 0) {
                $adminStaff->services()->sync($services->random(min(3, $services->count()))->pluck('id')->toArray());
            }
        }

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
            $staffData = [
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
            ];

            // Associate user with company via pivot table if a company exists
            if ($company) {
                // Check if relationship already exists
                $exists = DB::table('company_user')
                    ->where('company_id', $company->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$exists) {
                    // Create the relationship in the pivot table
                    $company->users()->attach($user->id, [
                        'is_primary' => true,
                        'role' => 'staff',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $staff = Staff::updateOrCreate(
                ['email' => $staffData['email']],
                $staffData
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
