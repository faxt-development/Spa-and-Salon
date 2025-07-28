<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Staff;
use App\Models\BusinessHour;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;


class TestCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a test company with a custom domain and an admin user.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }

        // Create a test admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'onboarding_completed' => true,
            ]
        );

        // Assign admin role
        $user->assignRole('admin');

        // Create a test company with custom domain
        $company = Company::firstOrCreate(
            ['domain' => 'localhost,127.0.0.1'],
            [
                'name' => 'Test Spa & Salon',
                'address' => '123 Test Street',
                'city' => 'Test City',
                'state' => 'TS',
                'zip' => '12345',
                'phone' => '(555) 123-4567',
                'website' => 'https://test-spa.localhost',
                'logo' => 'images/test-spa-logo.jpg',
                'description' => 'A test spa and salon company for demonstrating custom domains.',
                'is_primary_domain' => true,
                'homepage_content' => [
                    'heroTitle' => 'Welcome to Test Spa & Salon',
                    'heroSubtitle' => 'Your premier destination for luxury beauty and wellness services',
                    'heroImage' => 'images/spa-hero.jpg',
                    'servicesTagline' => 'Experience our premium services tailored just for you',
                    'services' => [
                        [
                            'title' => 'Premium Massage',
                            'description' => 'Relax and rejuvenate with our signature massage treatments.',
                            'price' => 'From $85',
                            'icon' => 'fas fa-spa'
                        ],
                        [
                            'title' => 'Hair Styling',
                            'description' => 'Get a fresh new look with our expert hair stylists.',
                            'price' => 'From $55',
                            'icon' => 'fas fa-cut'
                        ],
                        [
                            'title' => 'Facial Treatments',
                            'description' => 'Revitalize your skin with our premium facial treatments.',
                            'price' => 'From $75',
                            'icon' => 'fas fa-smile'
                        ]
                    ],
                    'aboutTitle' => 'About Test Spa & Salon',
                    'aboutContent' => 'Test Spa & Salon is a premier beauty and wellness destination dedicated to providing exceptional service and a relaxing experience for all our clients.',
                    'aboutImage' => 'images/about-spa.jpg',
                    'testimonials' => [
                        [
                            'name' => 'Jane Doe',
                            'content' => 'Absolutely love this place! The staff is amazing and the services are top-notch.',
                            'rating' => 5
                        ],
                        [
                            'name' => 'John Smith',
                            'content' => 'Best spa experience I\'ve ever had. Will definitely be coming back!',
                            'rating' => 5
                        ],
                        [
                            'name' => 'Emily Johnson',
                            'content' => 'The massage was incredible and the atmosphere is so relaxing.',
                            'rating' => 4
                        ]
                    ]
                ],
                'theme_settings' => [
                    'primaryColor' => '#4f46e5',
                    'secondaryColor' => '#10b981',
                    'accentColor' => '#f59e0b'
                ]
            ]
        );

        // Associate the user with the company through the pivot table
        $company->users()->syncWithoutDetaching([
            $user->id => [
                'is_primary' => true,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Create a primary location for the company
        $location = \App\Models\Location::firstOrCreate(
            ['name' => 'Main Location'],
            [
                'company_id' => $company->id,
                'code' => 'MAIN',
                'description' => 'Primary location for Test Spa & Salon',
                'contact_name' => 'Jane Smith',
                'contact_email' => 'contact@test-spa.localhost',
                'contact_phone' => '(555) 123-4567',
                'address_line_1' => '123 Test Street',
                'address_line_2' => 'Suite 100',
                'city' => 'Test City',
                'state' => 'TS',
                'postal_code' => '12345',
                'country' => 'US',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'is_active' => true,
                'is_primary' => true,
                'settings' => [
                    'online_booking_enabled' => true,
                    'online_booking_lead_time' => 60, // minutes
                    'online_booking_interval' => 15, // minutes
                ],
            ]
        );

        // Create business hours for the company and location
        // First delete any existing hours for this company and location
        BusinessHour::where('company_id', $company->id)
            ->where(function($query) use ($location) {
                $query->where('location_id', $location->id)
                      ->orWhereNull('location_id');
            })
            ->delete();

        // Define business hours for each day
        $businessHours = [
            // Monday (1)
            ['day_of_week' => 1, 'open_time' => '09:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            // Tuesday (2)
            ['day_of_week' => 2, 'open_time' => '09:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            // Wednesday (3)
            ['day_of_week' => 3, 'open_time' => '09:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            // Thursday (4)
            ['day_of_week' => 4, 'open_time' => '09:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            // Friday (5)
            ['day_of_week' => 5, 'open_time' => '09:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            // Saturday (6)
            ['day_of_week' => 6, 'open_time' => '10:00:00', 'close_time' => '18:00:00', 'is_closed' => false],
            // Sunday (7)
            ['day_of_week' => 7, 'open_time' => '11:00:00', 'close_time' => '17:00:00', 'is_closed' => false],
        ];

        // Create business hours records for the company (global)
        foreach ($businessHours as $hours) {
            // Create company-level business hours
            BusinessHour::create([
                'company_id' => $company->id,
                'location_id' => null, // Global company hours
                'day_of_week' => $hours['day_of_week'],
                'open_time' => $hours['open_time'],
                'close_time' => $hours['close_time'],
                'is_closed' => $hours['is_closed'],
            ]);

            // Create location-specific business hours
            BusinessHour::create([
                'company_id' => $company->id,
                'location_id' => $location->id,
                'day_of_week' => $hours['day_of_week'],
                'open_time' => $hours['open_time'],
                'close_time' => $hours['close_time'],
                'is_closed' => $hours['is_closed'],
            ]);
        }

        // Associate some template service categories with the company
        $this->associateTemplateCategories($company);

        // Attach all staff to the company
        $this->attachStaffToCompany($company);

        // Create default services for the company
        $this->createDefaultServices($company, $location);

        $this->attachStaffToServices($company);
        $this->command->info('Test company created with domain: localhost,127.0.0.1');
        $this->command->info('Admin login: admin@example.com / password');
    }

    /**
     * Associate template service categories with the company
     *
     * @param \App\Models\Company $company
     * @return void
     */
    protected function associateTemplateCategories($company)
    {
        // Get template service categories
        $templateCategories = \App\Models\ServiceCategory::where('template', true)
            ->take(5) // Take first 5 template categories
            ->get();

        if ($templateCategories->isEmpty()) {
            $this->command->info('No template service categories found to associate with company.');
            return $templateCategories;
        }

        // Associate template categories with the company
        $company->serviceCategories()->sync($templateCategories->pluck('id')->toArray());

        $this->command->info('Associated ' . $templateCategories->count() . ' template service categories with the company.');

        // Create a custom non-template category for the company
        $customCategory = \App\Models\ServiceCategory::create([
            'name' => 'Custom ' . $company->name . ' Services',
            'description' => 'Custom services specific to ' . $company->name,
            'slug' => 'custom-' . \Illuminate\Support\Str::slug($company->name) . '-services-' . $this->unique_number(),
            'active' => true,
            'template' => false,
            'display_order' => 100, // Put at the end
        ]);

        // Associate the custom category with the company
        $company->serviceCategories()->attach($customCategory->id);

        $this->command->info('Created and associated custom service category with the company.');

        return $templateCategories->push($customCategory);
    }

    /**
     * Generate a unique number for slugs
     *
     * @return int
     */
    protected function unique_number()
    {
        return mt_rand(1000, 9999);
    }

    /**
     * Create default services for the company
     *
     * @param \App\Models\Company $company
     * @param \App\Models\Location $location
     * @return void
     */
    /**
     * Attach all staff to the company
     *
     * @param \App\Models\Company $company
     * @return void
     */
    protected function attachStaffToCompany($company)
    {
        // Get all users with staff role
        $staffRole = \Spatie\Permission\Models\Role::where('name', 'staff')->first();

        if (!$staffRole) {
            $this->command->info('No staff role found. Creating staff role...');
            $staffRole = \Spatie\Permission\Models\Role::create(['name' => 'staff']);
        }

        // Get or create some staff members with the exact emails expected by AppointmentSeeder
        $staffMembers = [];
        $staffEmails = [
            'alex.johnson@example.com' => 'Alex Johnson',
            'taylor.smith@example.com' => 'Taylor Smith',
            'jordan.williams@example.com' => 'Jordan Williams',
        ];

        foreach ($staffEmails as $email => $name) {
            $staff = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'onboarding_completed' => true,
                ]
            );

            // Assign staff role if not already assigned
            if (!$staff->hasRole('staff')) {
                $staff->assignRole('staff');
            }

            $staffMembers[] = $staff;
        }

        // Attach staff to company with appropriate roles
        foreach ($staffMembers as $index => $staff) {
            $company->users()->syncWithoutDetaching([
                $staff->id => [
                    'is_primary' => $index === 0, // First staff is primary
                    'role' => 'staff',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            // Create actual staff records in the staff table
            // Always ensure staff records exist for the expected emails
            $staffRecord = Staff::updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => $staff->id,
                    'first_name' => $name,
                    'last_name' => '',
                    'phone' => '555-01' . ($index + 1),
                    'position' => $index === 0 ? 'Senior Stylist' : ($index === 1 ? 'Color Specialist' : 'Massage Therapist'),
                    'bio' => 'Experienced professional specializing in ' . ($index === 0 ? 'hair styling' : ($index === 1 ? 'hair coloring' : 'massage therapy')),
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Attached ' . count($staffMembers) . ' staff members to the company');
    }

    protected function createDefaultServices($company, $location)
    {
        // First ensure we have categories
        $categories = $this->associateTemplateCategories($company);

        if ($categories === null || $categories->isEmpty()) {
            // Create a default category if no template categories exist
            $category = \App\Models\ServiceCategory::create([
                'name' => 'Spa Services',
                'description' => 'Relaxing and rejuvenating spa treatments',
                'slug' => 'spa-services-' . uniqid(),
                'active' => true,
                'template' => false,
                'display_order' => 1,
            ]);
            $company->serviceCategories()->attach($category->id);
            $categories = collect([$category]);
        }

        $defaultServices = [
            [
                'name' => 'Swedish Massage',
                'description' => 'A gentle, relaxing massage that uses long strokes, kneading, and circular movements to help relax and energize you.',
                'price' => 85.00,
                'duration' => 60,
                'active' => true,
                'max_capacity' => 1,
                'min_staff_required' => 1,
                'color' => '#4f46e5',
                'is_featured' => true,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Deep Tissue Massage',
                'description' => 'A therapeutic massage that targets the deeper layers of muscle and connective tissue to release chronic muscle tension.',
                'price' => 95.00,
                'duration' => 60,
                'active' => true,
                'max_capacity' => 1,
                'min_staff_required' => 1,
                'color' => '#10b981',
                'is_featured' => true,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Facial Treatment',
                'description' => 'A deep cleansing facial that includes exfoliation, extractions, and a custom mask to leave your skin glowing.',
                'price' => 75.00,
                'duration' => 45,
                'active' => true,
                'max_capacity' => 1,
                'min_staff_required' => 1,
                'color' => '#f59e0b',
                'is_featured' => true,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Manicure & Pedicure Combo',
                'description' => 'A complete nail care package including shaping, cuticle care, exfoliation, and polish application for both hands and feet.',
                'price' => 65.00,
                'duration' => 90,
                'active' => true,
                'max_capacity' => 1,
                'min_staff_required' => 1,
                'color' => '#ec4899',
                'is_featured' => true,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Hot Stone Massage',
                'description' => 'A deeply relaxing massage that uses smooth, heated stones to melt away tension and ease muscle stiffness.',
                'price' => 110.00,
                'duration' => 75,
                'active' => true,
                'max_capacity' => 1,
                'min_staff_required' => 1,
                'color' => '#8b5cf6',
                'is_featured' => true,
                'tax_rate' => 8.25,
            ],
        ];

        $createdServices = [];

        foreach ($defaultServices as $index => $serviceData) {
            try {
                // Use the category in round-robin fashion
                $category = $categories[$index % $categories->count()];

                // First create the service
                $service = new \App\Models\Service();
                $service->fill($serviceData);
                $service->save();

                // Attach to category through the category_service pivot
                $service->categories()->attach($category->id);

                // Associate service with the company through company_service pivot
                $company->services()->syncWithoutDetaching([$service->id]);

                $createdServices[] = $service->name;

            } catch (\Exception $e) {
                $this->command->error('Failed to create service ' . ($serviceData['name'] ?? 'unknown') . ': ' . $e->getMessage());
            }
        }

        $this->command->info('Created ' . count($createdServices) . ' default services: ' . implode(', ', $createdServices));
    }

    /**
     * Attach staff to services at the specified location
     *
     * @param \App\Models\Company $company
     * @return void
     */
    protected function attachStaffToServices($company)
    {
        if (!$company) {
            $this->command->warn('No company provided to attach staff to services');
            return;
        }

        // Get all users assigned to the company as staff
        $userStaff = $company->userStaff()->get();

        if ($userStaff->isEmpty()) {
            $this->command->warn('No staff members found to attach to services');
            return;
        }

        // Get all services at this location
        $services = $company->services()->get();

        if ($services->isEmpty()) {
            $this->command->warn('No services found at this location to attach staff to');
            return;
        }

        // Map user IDs to staff IDs
        $staffData = [];
        foreach ($userStaff as $user) {
            // Find the corresponding staff record for this user
            $staff = \App\Models\Staff::where('user_id', $user->id)->first();
            if ($staff) {
                $staffData[$staff->id] = [
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($staffData)) {
            $this->command->warn('No valid staff records found for the assigned users');
            return;
        }

// Use a transaction for data consistency
DB::beginTransaction();
try {
    foreach ($services as $service) {
        $service->staff()->syncWithoutDetaching($staffData);
    }
    DB::commit();
    $this->command->info("Successfully associated " . count($staffData) . " staff members with {$services->count()} services");
} catch (\Exception $e) {
    DB::rollBack();
    $this->command->error("Failed to associate staff with services: " . $e->getMessage());
}

        $this->command->info("Attached " . count($staffData) . " staff members to {$services->count()} services at company '{$company->name}'", 'info');
    }
}
