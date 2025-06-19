<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            // Get services and categories directly from the models
            $services = Service::with(['categories'])
                ->where('active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($service) {
                    // Get the primary category (first one) for grouping purposes
                    $primaryCategory = $service->categories->first();
                    
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'description' => $service->description,
                        'price' => $service->price,
                        'formatted_price' => $service->formatted_price,
                        'duration' => $service->duration,
                        'formatted_duration' => $service->formatted_duration,
                        'category' => $primaryCategory ? [
                            'id' => $primaryCategory->id,
                            'name' => $primaryCategory->name,
                            'slug' => $primaryCategory->slug,
                        ] : null,
                        'categories' => $service->categories->map(function($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->name,
                                'slug' => $category->slug,
                            ];
                        }),
                        'image_url' => $service->image_url,
                        'is_featured' => $service->is_featured,
                    ];
                });

            $categories = ServiceCategory::with(['services' => function ($query) {
                    $query->where('active', true);
                }])
                ->where('active', true)
                ->orderBy('display_order')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'slug' => $category->slug,
                        'image_url' => $category->image_url,
                        'services_count' => $category->services->count(),
                        'parent_id' => $category->parent_id,
                    ];
                });
            
            // Group services by their primary category
            $groupedServices = collect($services)->groupBy(function($service) {
                // Use the first category as the primary category for grouping
                return $service['category'] ? $service['category']['name'] : 'Uncategorized';
            })->sortBy(function($items, $key) {
                // Define the order of categories
                $order = [
                    'Hair Services' => 1,
                    'Hair Color' => 2,
                    'Hair Treatments' => 3,
                    'Hair Extensions' => 4,
                    'Makeup' => 5,
                    'Waxing' => 6,
                    'Nails' => 7,
                    'Massage' => 8,
                    'Facials' => 9,
                    'Body Treatments' => 10,
                ];
                
                return $order[$key] ?? 999;
            });
            
            return view('services', [
                'groupedServices' => $groupedServices,
                'allServices' => $services,
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error fetching services: ' . $e->getMessage());
            
            return view('services', [
                'groupedServices' => collect([]),
                'allServices' => [],
                'categories' => [],
                'error' => 'Unable to fetch services at this time. Please try again later.'
            ]);
        }
    }
}