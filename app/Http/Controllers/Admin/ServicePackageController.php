<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicePackageController extends Controller
{
    /**
     * Display a listing of the service packages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $packages = ServicePackage::with(['services', 'category'])
            ->orderBy('name')
            ->get();
            
        return view('admin.services.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new service package.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::orderBy('name')->get();
        $categories = ServiceCategory::orderBy('name')->get();
        
        return view('admin.services.packages.create', compact('services', 'categories'));
    }

    /**
     * Store a newly created service package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:service_categories,id',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            $package = ServicePackage::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'category_id' => $validated['category_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);
            
            $package->services()->attach($validated['services']);
            
            DB::commit();
            
            return redirect()->route('admin.services.packages')
                ->with('success', 'Service package created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create service package: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified service package.
     *
     * @param  \App\Models\ServicePackage  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(ServicePackage $package)
    {
        $package->load('services');
        $services = Service::orderBy('name')->get();
        $categories = ServiceCategory::orderBy('name')->get();
        
        return view('admin.services.packages.edit', compact('package', 'services', 'categories'));
    }

    /**
     * Update the specified service package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServicePackage  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ServicePackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:service_categories,id',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            $package->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'category_id' => $validated['category_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);
            
            $package->services()->sync($validated['services']);
            
            DB::commit();
            
            return redirect()->route('admin.services.packages')
                ->with('success', 'Service package updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update service package: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified service package from storage.
     *
     * @param  \App\Models\ServicePackage  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServicePackage $package)
    {
        try {
            $package->services()->detach();
            $package->delete();
            
            return redirect()->route('admin.services.packages')
                ->with('success', 'Service package deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete service package: ' . $e->getMessage());
        }
    }
}
