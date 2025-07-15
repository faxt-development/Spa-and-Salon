<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index()
    {
        $user = auth()->user();
        $company = $user->primaryCompany();

        $services = Service::with('categories')
            ->when($company, function ($query) use ($company) {
                return $query->whereHas('companies', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('admin.services.index', [
            'services' => $services,
            'categories' => ServiceCategory::active()->get()
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
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')->ignore($service->id),
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
        // Check if service has appointments
        if ($service->appointments()->count() > 0) {
            return redirect()->route('admin.services')
                ->with('error', 'Cannot delete service that has appointments. Please delete or reassign the appointments first.');
        }

        $service->delete();

        return redirect()->route('admin.services')
            ->with('success', 'Service deleted successfully.');
    }
}
