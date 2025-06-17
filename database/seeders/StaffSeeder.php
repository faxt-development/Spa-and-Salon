<?php

namespace Database\Seeders;

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
        // Get or create the staff user
        $staffUser = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        
        // Ensure the user has the staff role
        if (!$staffUser->hasRole('staff')) {
            $staffUser->assignRole('staff');
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
                'user_id' => $staffUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Taylor',
                'last_name' => 'Smith',
                'email' => 'taylor.smith@example.com',
                'phone' => '555-0102',
                'position' => 'Color Specialist',
                'bio' => 'Expert in hair coloring and treatments.',
                'active' => true,
                'user_id' => $staffUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($staffMembers as $staff) {
            Staff::updateOrCreate(
                ['email' => $staff['email']],
                $staff
            );
        }
    }
}
