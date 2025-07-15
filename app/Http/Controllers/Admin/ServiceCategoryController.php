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
        $search = $request->input('search');

        $query = ServiceCategory::query()
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('display_order')
            ->orderBy('name');

        $categories = $query->paginate(15)->withQueryString();

        return view('admin.services.categories.index', [
            'categories' => $categories,
            'search' => $search
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
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
        $validated['is_active'] = $validated['is_active'] ?? true;

        ServiceCategory::create($validated);

        return redirect()->route('admin.services.categories')
            ->with('success', 'Service category created successfully.');
    }

    /**
     * Show the form for editing the specified service category.
     */
    public function edit(ServiceCategory $category)
    {
        return view('admin.services.categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified service category in storage.
     */
    public function update(Request $request, ServiceCategory $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories')->ignore($category->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default active status if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        $category->update($validated);

        return redirect()->route('admin.services.categories')
            ->with('success', 'Service category updated successfully.');
    }

    /**
     * Remove the specified service category from storage.
     */
    public function destroy(ServiceCategory $category)
    {
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
}
