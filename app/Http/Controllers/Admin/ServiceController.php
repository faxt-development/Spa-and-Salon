<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        // Get search and filter parameters
        $searchTerm = $request->input('search', '');
        $selectedCategory = $request->input('category', 'All');

        // Base query for services
        $query = Service::with(['categories', 'companies'])
            ->where('template', true)
            ->orderBy('name');

        // Apply search filter if provided
        if (!empty($searchTerm)) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Get all services for the current company
        $companyServiceIds = $company ? $company->services()->pluck('services.id')->toArray() : [];

        // Get all services with their categories
        $allServices = $query->get()->map(function ($service) use ($companyServiceIds) {
            $service->is_company_service = in_array($service->id, $companyServiceIds);
            return $service;
        });

        // Filter services by category if not 'All'
        if ($selectedCategory !== 'All') {
            $allServices = $allServices->filter(function ($service) use ($selectedCategory) {
                return $service->categories->contains('name', $selectedCategory);
            });
        }

        // Get unique categories for the filter dropdown
        $categories = ServiceCategory::active()
            ->pluck('name')
            ->unique()
            ->values()
            ->prepend('All')
            ->toArray();

        // Get the company's selected services with their categories
        $companyServices = $company ? $company->services()->with('categories')->get() : collect();

        return view('admin.services.services', [
            'company' => $company,
            'services' => $allServices,
            'companyServices' => $companyServices,
            'searchTerm' => $searchTerm,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'selectedServices' => $request->old('selected_services', [])
        ]);
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        return view('admin.services.create', [
            'categories' => ServiceCategory::active()->get()
        ]);
    }

    /**
     * Store a newly created service in storage or associate an existing service.
     */
    /**
     * Remove a service from the company (soft delete the pivot record).
     */
    public function removeFromCompany(Request $request, Service $service)
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.'
            ], 404);
        }

        // Check if this is a template service that shouldn't be removed
        if ($service->template) {
            return response()->json([
                'success' => false,
                'message' => 'Template services cannot be removed.'
            ], 403);
        }

        try {
            // Soft delete the pivot record
            $company->services()->updateExistingPivot($service->id, [
                'deleted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service removed from company successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error removing service from company: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the service.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        // Validate based on whether we're creating a new service or using an existing one
        if ($request->has('existing_service_id')) {
            $validated = $request->validate([
                'existing_service_id' => 'required|exists:services,id',
            ]);
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:services',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:1',
                'category_ids' => 'required|array',
                'category_ids.*' => 'exists:service_categories,id',
                'image_url' => 'nullable|url',
                'is_featured' => 'boolean',
                'active' => 'boolean',
            ]);

            // Generate slug from name
            $validated['slug'] = Str::slug($validated['name']);

            // Set default active status if not provided
            $validated['active'] = $validated['active'] ?? true;

            // Create new service
            $service = Service::create($validated);

            // Attach categories
            $service->categories()->attach($validated['category_ids']);
        }

        // Associate service with company
        $service = $request->has('existing_service_id')
            ? Service::findOrFail($request->existing_service_id)
            : $service;

        // Attach the service to the company
        $company->services()->attach($service->id);

        return redirect()->route('admin.services')
            ->with('success', $request->has('existing_service_id')
                ? 'Service added successfully.'
                : 'Service created successfully.');
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        return view('admin.services.edit', [
            'service' => $service,
            'categories' => ServiceCategory::active()->get()
        ]);
    }
 /**
     * Show the form for editing the specified service.
     */
    public function show()
    {
        $packages = ServicePackage::with(['services', 'category'])
        ->orderBy('name')
        ->get();

    return view('admin.services.packages.index', compact('packages'));

    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:service_categories,id',
            'image_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'active' => 'boolean',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $service->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default active status if not provided
        $validated['active'] = $validated['active'] ?? true;

        // Update service
        $service->update($validated);

        // Sync categories
        $service->categories()->sync($validated['category_ids']);

        return redirect()->route('admin.services')
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        // Prevent deletion of template services
        if ($service->template) {
            return redirect()->back()
                ->with('error', 'Template services cannot be deleted.');
        }

        try {
            // Soft delete the service
            $service->delete();

            return redirect()->route('admin.services.index')
                ->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting service: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the service.');
        }
    }

    /**
     * Add services to the company
     */
    public function addToCompany(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id'
        ]);

        $user = auth()->user();
        $company = $user->primaryCompany();

        if (!$company) {
            return redirect()->back()
                ->with('error', 'No company found for this user.');
        }

        // Get the service IDs that aren't already attached to the company
        $newServiceIds = array_diff(
            $request->service_ids,
            $company->services()->pluck('services.id')->toArray()
        );

        if (empty($newServiceIds)) {
            return redirect()->back()
                ->with('error', 'All selected services are already added to your company.');
        }

        // Attach the new services to the company
        $company->services()->attach($newServiceIds);

        // Store the selected services in the session to keep them checked after redirect
        $request->session()->flash('selected_services', $request->service_ids);

        return redirect()->back()
            ->with('success', count($newServiceIds) . ' service(s) have been added to your company.');
    }

    /**
     * Copy a template service to create an editable version.
     *
     * @param Request $request
     * @param \App\Models\Company $company
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyTemplateService(Request $request, $company, Service $service)
    {
        // Check if the service is actually a template
        if (!$service->template) {
            return response()->json([
                'success' => false,
                'message' => 'Only template services can be copied.'
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

            // Create a copy of the service
            $newService = $service->replicate();
            $newService->template = false; // Make the copy non-template
            $newService->name = $service->name . ' (Copy)'; // Append (Copy) to name
            $newService->save();

            // Copy categories
            $categories = $service->categories;
            if ($categories->isNotEmpty()) {
                $newService->categories()->attach($categories->pluck('id')->toArray());
            }

            // If the company has the template service, switch to the copy
            if ($userCompany->services()->where('services.id', $service->id)->exists()) {
                // Soft delete the relationship with the template
                $userCompany->services()->updateExistingPivot($service->id, ['deleted_at' => now()]);

                // Add the new service to the company
                $userCompany->services()->attach($newService->id);
            }

            // Commit transaction
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template service copied successfully.',
                'service' => $newService
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error copying template service: ' . $e->getMessage()
            ], 500);
        }
    }
}
