<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories first
        $categories = ServiceCategory::all()->keyBy('name');

        // Define services grouped by category
        $servicesByCategory = [
            'Hair Services' => [
                [
                    'name' => 'Women\'s Haircut',
                    'description' => 'Professional haircut with shampoo, cut, and style',
                    'price' => 65.00,
                    'duration' => 60,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Men\'s Haircut',
                    'description' => 'Classic men\'s haircut with clipper and scissor work',
                    'price' => 40.00,
                    'duration' => 30,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Hair Color' => [
                [
                    'name' => 'Root Touch Up',
                    'description' => 'Color touch up for roots with single process color',
                    'price' => 75.00,
                    'duration' => 90,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Full Highlight',
                    'description' => 'Full head highlights with foils',
                    'price' => 120.00,
                    'duration' => 150,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Massage' => [
                [
                    'name' => 'Swedish Massage',
                    'description' => '60-minute relaxing full body massage',
                    'price' => 90.00,
                    'duration' => 60,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Deep Tissue Massage',
                    'description' => '60-minute therapeutic massage targeting deep muscle tension',
                    'price' => 110.00,
                    'duration' => 60,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Hot Stone Massage',
                    'description' => '90-minute massage with heated stones for deep relaxation',
                    'price' => 135.00,
                    'duration' => 90,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Facials' => [
                [
                    'name' => 'Classic Facial',
                    'description' => 'Deep cleansing facial with steam, extraction, and mask',
                    'price' => 85.00,
                    'duration' => 60,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Anti-Aging Facial',
                    'description' => 'Advanced facial targeting fine lines and wrinkles',
                    'price' => 120.00,
                    'duration' => 75,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Body Treatments' => [
                [
                    'name' => 'Body Scrub',
                    'description' => 'Full body exfoliation treatment',
                    'price' => 95.00,
                    'duration' => 60,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Detox Body Wrap',
                    'description' => 'Purifying and detoxifying full body wrap treatment',
                    'price' => 110.00,
                    'duration' => 75,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Nails' => [
                [
                    'name' => 'Manicure',
                    'description' => 'Classic nail shaping, cuticle care, and polish',
                    'price' => 35.00,
                    'duration' => 30,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Pedicure',
                    'description' => 'Foot soak, exfoliation, nail care, and polish',
                    'price' => 45.00,
                    'duration' => 45,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Gel Manicure',
                    'description' => 'Long-lasting gel polish manicure',
                    'price' => 50.00,
                    'duration' => 45,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Waxing' => [
                [
                    'name' => 'Eyebrow Wax',
                    'description' => 'Eyebrow shaping with wax',
                    'price' => 20.00,
                    'duration' => 15,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Lip Wax',
                    'description' => 'Upper lip hair removal with wax',
                    'price' => 15.00,
                    'duration' => 10,
                    'active' => true,
                    'template' => true,
                ],
                [
                    'name' => 'Full Leg Wax',
                    'description' => 'Hair removal for entire legs',
                    'price' => 65.00,
                    'duration' => 45,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Makeup' => [
                [
                    'name' => 'Teen Makeup Lesson',
                    'description' => 'Personalized makeup application lesson',
                    'price' => 85.00,
                    'duration' => 90,
                    'active' => true,
                    'template' => true,
                ],
            ],
            'Specialty' => [
                [
                    'name' => 'Bridal Package',
                    'description' => 'Hair, makeup, and trial session',
                    'price' => 350.00,
                    'duration' => 240,
                    'active' => true,
                    'template' => true,
                ],
            ],
        ];

        // Create services and attach categories
        foreach ($servicesByCategory as $categoryName => $services) {
            // Find the category
            $category = $categories->get($categoryName);

            if (!$category) {
                continue; // Skip if category doesn't exist
            }

            // Create each service in this category
            foreach ($services as $serviceData) {
                // Create the service
                $service = Service::create($serviceData);

                // Attach the category
                $service->categories()->attach($category->id);
            }
        }
    }
}
