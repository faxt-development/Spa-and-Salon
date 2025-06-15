<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the product categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ProductCategory::query();

        // Filter by parent category
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort categories
        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $categories = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created product category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $categoryData = $request->all();
        $categoryData['slug'] = Str::slug($request->name);

        $category = ProductCategory::create($categoryData);

        return response()->json([
            'success' => true,
            'message' => 'Product category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified product category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = ProductCategory::with(['parent', 'children'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Update the specified product category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prevent category from being its own parent
        if ($request->has('parent_id') && $request->parent_id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'A category cannot be its own parent',
            ], 422);
        }

        $categoryData = $request->all();
        
        // Update slug if name is changed
        if ($request->has('name')) {
            $categoryData['slug'] = Str::slug($request->name);
        }

        $category->update($categoryData);

        return response()->json([
            'success' => true,
            'message' => 'Product category updated successfully',
            'data' => $category->fresh(['parent', 'children']),
        ]);
    }

    /**
     * Remove the specified product category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated products',
            ], 422);
        }

        // Check if category has child categories
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with child categories',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product category deleted successfully',
        ]);
    }

    /**
     * Get a hierarchical list of all categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function hierarchy()
    {
        $rootCategories = ProductCategory::whereNull('parent_id')
            ->with('children')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rootCategories,
        ]);
    }
}
