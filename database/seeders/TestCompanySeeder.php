<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
            ['email' => 'testadmin@example.com'],
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
            ['domain' => 'test-spa.localhost'],
            [
                'user_id' => $user->id,
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
                    ],
                    'businessHours' => "Monday - Friday: 9:00 AM - 8:00 PM\nSaturday: 10:00 AM - 6:00 PM\nSunday: 11:00 AM - 5:00 PM"
                ],
                'theme_settings' => [
                    'primaryColor' => '#4f46e5',
                    'secondaryColor' => '#10b981',
                    'accentColor' => '#f59e0b'
                ]
            ]
        );
        
        $this->command->info('Test company created with domain: test-spa.localhost');
        $this->command->info('Admin login: testadmin@example.com / password');
    }
}
