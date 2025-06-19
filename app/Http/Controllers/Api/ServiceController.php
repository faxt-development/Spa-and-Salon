<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all active services with their categories
        $services = Service::with('categories')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $service = Service::with(['categories', 'products'])
            ->where('id', $id)
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display a comprehensive listing of services for public access.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicServices(): JsonResponse
    {
        // Get all active services with their categories
        $services = Service::with(['categories'])
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($service) {
                // Get the primary category (first one) for display purposes
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

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Display all active service categories for public access.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicCategories(): JsonResponse
    {
        // Get all active categories with their services count
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

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display all active services in a specific category for public access.
     *
     * @param string $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function servicesByCategory(string $categoryId): JsonResponse
    {
        $category = ServiceCategory::find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Get all active services in this category using many-to-many relationship
        $services = $category->services()
            ->where('services.active', true)
            ->orderBy('services.name')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'duration' => $service->duration,
                    'formatted_duration' => $service->formatted_duration,
                    'image_url' => $service->image_url,
                    'is_featured' => $service->is_featured,
                ];
            });

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'slug' => $category->slug,
                'image_url' => $category->image_url,
            ],
            'data' => $services,
        ]);
    }
}
