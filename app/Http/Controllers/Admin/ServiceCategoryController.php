<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the service categories.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $user->primaryCompany();
        $search = $request->input('search');
        $showAssociated = $request->input('show_associated', 'all'); // 'all', 'company_only', 'templates_only'

        // Get all categories for the current company
        $companyCategoryIds = $company ? $company->serviceCategories()->pluck('service_categories.id')->toArray() : [];

        // Base query
        $query = ServiceCategory::with(['companies'])
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
            
        // Filter based on association preference
        if ($showAssociated === 'company_only' && $company) {
            // Only show categories associated with this company
            $query->whereHas('companies', function($q) use ($company) {
                $q->where('companies.id', $company->id);
            });
        } elseif ($showAssociated === 'templates_only') {
            // Only show template categories
            $query->where('template', true);
        }
        
        // Apply ordering
        $query->orderBy('display_order')->orderBy('name');

        // Paginate the results
        $allCategories = $query->paginate(10);
        
        // Add the is_company_category flag to each category
        $allCategories->getCollection()->transform(function ($category) use ($companyCategoryIds) {
            $category->is_company_category = in_array($category->id, $companyCategoryIds);
            return $category;
        });

        // Get the company's selected categories
        $companyCategories = $company ? $company->serviceCategories()->get() : collect();

        return view('admin.services.categories.index', [
            'company' => $company,
            'categories' => $allCategories,
            'companyCategories' => $companyCategories,
            'search' => $search,
            'showAssociated' => $showAssociated,
            'selectedCategories' => $request->old('selected_categories', [])
        ]);
    }

    /**
     * Show the form for creating a new service category.
     */
    public function create()
    {
        return view('admin.services.categories.create');
    }

    /**
     * Store a newly created service category in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        // Validate based on whether we're creating a new category or using an existing one
        if ($request->has('existing_category_id')) {
            $validated = $request->validate([
                'existing_category_id' => 'required|exists:service_categories,id',
            ]);
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:service_categories',
                'description' => 'nullable|string',
                'active' => 'boolean',
                'display_order' => 'nullable|integer',
            ]);

            // Generate slug from name
            $validated['slug'] = Str::slug($validated['name']);

            // Set default display order if not provided
            if (!isset($validated['display_order'])) {
                $maxOrder = ServiceCategory::max('display_order') ?? 0;
                $validated['display_order'] = $maxOrder + 1;
            }

            // Set default active status if not provided
            $validated['active'] = $validated['active'] ?? true;
            
            // Set template to false for user-created categories
            $validated['template'] = false;

            // Create new category
            $category = ServiceCategory::create($validated);
        }

        // Associate category with company
        $category = $request->has('existing_category_id')
            ? ServiceCategory::findOrFail($request->existing_category_id)
            : $category;

        // Attach the category to the company
        $company->serviceCategories()->attach($category->id);

        return redirect()->route('admin.services.categories')
            ->with('success', $request->has('existing_category_id')
                ? 'Service category added successfully.'
                : 'Service category created successfully.');
    }

    /**
     * Show the form for editing the specified service category.
     */
    public function edit(ServiceCategory $category)
    {
        // Prevent editing of template categories
        if ($category->template) {
            return redirect()->route('admin.services.categories')
                ->with('error', 'Template categories cannot be edited.');
        }
        
        return view('admin.services.categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified service category in storage.
     */
    public function update(Request $request, ServiceCategory $category)
    {
        // Prevent updating of template categories
        if ($category->template) {
            return redirect()->route('admin.services.categories')
                ->with('error', 'Template categories cannot be edited.');
        }
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories')->ignore($category->id),
            ],
            'description' => 'nullable|string',
            'active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default active status if not provided
        $validated['active'] = $validated['active'] ?? true;

        $category->update($validated);

        return redirect()->route('admin.services.categories')
            ->with('success', 'Service category updated successfully.');
    }

    /**
     * Remove the specified service category from storage.
     */
    public function destroy(ServiceCategory $category)
    {
        // Prevent deletion of template categories
        if ($category->template) {
            return redirect()->route('admin.services.categories')
                ->with('error', 'Template categories cannot be deleted.');
        }
        
        // Check if category has services
        if ($category->services()->count() > 0) {
            return redirect()->route('admin.services.categories')
                ->with('error', 'Cannot delete category that has services. Please reassign or delete the services first.');
        }

        $category->delete();

        return redirect()->route('admin.services.categories')
            ->with('success', 'Service category deleted successfully.');
    }

    /**
     * Reorder service categories.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:service_categories,id',
            'categories.*.display_order' => 'required|integer',
        ]);

        foreach ($validated['categories'] as $item) {
            ServiceCategory::where('id', $item['id'])
                ->update(['display_order' => $item['display_order']]);
        }

        return response()->json(['success' => true]);
    }
    
    /**
     * Add categories to the company
     */
    public function addToCompany(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:service_categories,id'
        ]);

        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->back()
                ->with('error', 'No company found for this user.');
        }

        // Get the category IDs that aren't already attached to the company
        $newCategoryIds = array_diff(
            $request->category_ids,
            $company->serviceCategories()->pluck('service_categories.id')->toArray()
        );

        if (empty($newCategoryIds)) {
            return redirect()->back()
                ->with('error', 'All selected categories are already added to your company.');
        }

        // Attach the new categories to the company
        $company->serviceCategories()->attach($newCategoryIds);

        // Store the selected categories in the session to keep them checked after redirect
        $request->session()->flash('selected_categories', $request->category_ids);

        return redirect()->back()
            ->with('success', count($newCategoryIds) . ' category(s) have been added to your company.');
    }

    /**
     * Remove a category from the company (soft delete the pivot record).
     */
    public function removeFromCompany(Request $request, ServiceCategory $category)
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.'
            ], 404);
        }

        try {
            // Soft delete the pivot record
            $company->serviceCategories()->updateExistingPivot($category->id, [
                'deleted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service category removed from company successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error removing service category from company: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the service category.'
            ], 500);
        }
    }

    /**
     * Copy a template category to create an editable version.
     *
     * @param Request $request
     * @param \App\Models\Company $company
     * @param ServiceCategory $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyTemplateCategory(Request $request, $company, ServiceCategory $category)
    {
        // Check if the category is actually a template
        if (!$category->template) {
            return response()->json([
                'success' => false,
                'message' => 'Only template categories can be copied.'
            ], 400);
        }

        // Get the authenticated user's company
        $user = auth()->user();
        $userCompany = $user->primaryCompany();

        // Verify company access
        if (!$userCompany || $userCompany->id != $company) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this company.'
            ], 403);
        }

        try {
            // Begin transaction
            \DB::beginTransaction();

            // Create a copy of the category
            $newCategory = $category->replicate();
            $newCategory->template = false; // Make the copy non-template
            $newCategory->name = $category->name . ' (Copy)'; // Append (Copy) to name
            
            // Generate a unique slug for the new category
            $baseSlug = Str::slug($newCategory->name);
            $slug = $baseSlug;
            $counter = 1;
            
            // Check if slug exists and generate a unique one by appending a number
            while (ServiceCategory::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            
            $newCategory->slug = $slug;
            $newCategory->save();

            // If the company has the template category, switch to the copy
            if ($userCompany->serviceCategories()->where('service_categories.id', $category->id)->exists()) {
                // Soft delete the relationship with the template
                $userCompany->serviceCategories()->updateExistingPivot($category->id, ['deleted_at' => now()]);

                // Add the new category to the company
                $userCompany->serviceCategories()->attach($newCategory->id);
            }

            // Commit transaction
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template category copied successfully.',
                'category' => $newCategory
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            \DB::rollBack();
            
            // Log the detailed error with stack trace
            \Log::error('Error copying template category: ' . $e->getMessage(), [
                'exception' => $e,
                'category_id' => $category->id,
                'company_id' => $company,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error copying template category: ' . $e->getMessage()
            ], 500);
        }
    }
}
