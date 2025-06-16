<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            // Hair Services
            [
                'name' => 'Women\'s Haircut',
                'description' => 'Professional haircut with shampoo, cut, and style',
                'price' => 65.00,
                'duration' => 60,
                'category' => 'Hair',
                'active' => true,
            ],
            [
                'name' => 'Men\'s Haircut',
                'description' => 'Classic men\'s haircut with clipper and scissor work',
                'price' => 40.00,
                'duration' => 30,
                'category' => 'Hair',
                'active' => true,
            ],
            [
                'name' => 'Root Touch Up',
                'description' => 'Color touch up for roots with single process color',
                'price' => 75.00,
                'duration' => 90,
                'category' => 'Color',
                'active' => true,
            ],
            [
                'name' => 'Full Highlight',
                'description' => 'Full head highlights with foils',
                'price' => 120.00,
                'duration' => 150,
                'category' => 'Color',
                'active' => true,
            ],
            
            // Spa Services
            [
                'name' => 'Swedish Massage',
                'description' => '60-minute full body relaxation massage',
                'price' => 95.00,
                'duration' => 60,
                'category' => 'Spa',
                'active' => true,
            ],
            [
                'name' => 'Deep Tissue Massage',
                'description' => 'Therapeutic deep tissue massage for muscle tension',
                'price' => 110.00,
                'duration' => 60,
                'category' => 'Spa',
                'active' => true,
            ],
            [
                'name' => 'Classic Facial',
                'description' => 'Cleansing, exfoliation, extractions, and hydration',
                'price' => 85.00,
                'duration' => 60,
                'category' => 'Spa',
                'active' => true,
            ],
            
            // Nail Services
            [
                'name' => 'Classic Manicure',
                'description' => 'Basic manicure with nail shaping, cuticle care, and polish',
                'price' => 35.00,
                'duration' => 45,
                'category' => 'Nails',
                'active' => true,
            ],
            [
                'name' => 'Spa Pedicure',
                'description' => 'Luxurious pedicure with exfoliation, mask, and massage',
                'price' => 55.00,
                'duration' => 60,
                'category' => 'Nails',
                'active' => true,
            ],
            
            // Waxing Services
            [
                'name' => 'Eyebrow Wax',
                'description' => 'Precise eyebrow shaping with warm wax',
                'price' => 25.00,
                'duration' => 20,
                'category' => 'Waxing',
                'active' => true,
            ],
            [
                'name' => 'Brazilian Wax',
                'description' => 'Complete waxing service',
                'price' => 65.00,
                'duration' => 45,
                'category' => 'Waxing',
                'active' => true,
            ],
            
            // Specialty Services
            [
                'name' => 'Bridal Package',
                'description' => 'Hair, makeup, and trial session',
                'price' => 350.00,
                'duration' => 240,
                'category' => 'Specialty',
                'active' => true,
            ],
            [
                'name' => 'Teen Makeup Lesson',
                'description' => 'Personalized makeup application lesson',
                'price' => 85.00,
                'duration' => 90,
                'category' => 'Specialty',
                'active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
