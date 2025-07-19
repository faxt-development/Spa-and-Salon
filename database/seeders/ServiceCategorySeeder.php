<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hair Services',
                'description' => 'Professional hair services including cuts, styling, and treatments',
                'slug' => 'hair-services',
                'active' => true,
                'display_order' => 1,
                'template' => true,
            ],
            [
                'name' => 'Hair Color',
                'description' => 'Professional hair coloring services',
                'slug' => 'hair-color',
                'active' => true,
                'display_order' => 2,
                'template' => true,
            ],
            [
                'name' => 'Hair Treatments',
                'description' => 'Specialized hair treatments for damaged or specific hair types',
                'slug' => 'hair-treatments',
                'active' => true,
                'display_order' => 3,
                'template' => true,
            ],
            [
                'name' => 'Hair Extensions',
                'description' => 'Professional hair extension services',
                'slug' => 'hair-extensions',
                'active' => true,
                'display_order' => 4,
                'template' => true,
            ],
            [
                'name' => 'Makeup',
                'description' => 'Professional makeup services for all occasions',
                'slug' => 'makeup',
                'active' => true,
                'display_order' => 5,
                'template' => true,
            ],
            [
                'name' => 'Waxing',
                'description' => 'Professional hair removal services',
                'slug' => 'waxing',
                'active' => true,
                'display_order' => 6,
                'template' => true,
            ],
            [
                'name' => 'Nails',
                'description' => 'Professional nail care services',
                'slug' => 'nails',
                'active' => true,
                'display_order' => 7,
                'template' => true,
            ],
            [
                'name' => 'Massage',
                'description' => 'Professional massage therapy services',
                'slug' => 'massage',
                'active' => true,
                'display_order' => 8,
                'template' => true,
            ],
            [
                'name' => 'Facials',
                'description' => 'Professional facial treatments',
                'slug' => 'facials',
                'active' => true,
                'display_order' => 9,
                'template' => true,
            ],
            [
                'name' => 'Body Treatments',
                'description' => 'Professional body treatments and therapies',
                'slug' => 'body-treatments',
                'active' => true,
                'display_order' => 10,
                'template' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }
    }
}
