<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with(['category', 'supplier'])
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category?->name,
                    'quantity_in_stock' => $product->quantity_in_stock,
                    'minimum_stock_level' => $product->minimum_stock_level,
                    'selling_price' => $product->selling_price,
                    'is_active' => $product->is_active,
                    'is_low_stock' => $product->isLowInStock(),
                ];
            });

        $categories = ProductCategory::select('id', 'name')->get();
        $suppliers = Supplier::select('id', 'name')->get();

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'filters' => request()->only(['search', 'category', 'stock_status']),
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return Inertia::render('Inventory/Edit', [
            'categories' => ProductCategory::select('id', 'name')->get(),
            'suppliers' => Supplier::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'category_id' => 'nullable|exists:product_categories,id',
            'brand' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reorder_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'inventory']);
        
        return Inertia::render('Inventory/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return Inertia::render('Inventory/Edit', [
            'product' => $product->load(['category', 'supplier']),
            'categories' => ProductCategory::select('id', 'name')->get(),
            'suppliers' => Supplier::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:product_categories,id',
            'brand' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reorder_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Update inventory for a product.
     */
    public function updateInventory(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Calculate new quantity
        $adjustment = (int) $validated['adjustment'];
        $newQuantity = $product->quantity_in_stock + $adjustment;
        
        // Ensure quantity doesn't go below zero
        if ($newQuantity < 0) {
            return back()->with('error', 'Insufficient stock. Cannot adjust below zero.');
        }

        // Start database transaction
        DB::beginTransaction();
        
        try {
            // Create inventory record
            $inventory = new Inventory([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'adjustment' => $adjustment,
                'quantity_before' => $product->quantity_in_stock,
                'quantity_after' => $newQuantity,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);
            
            $inventory->save();
            
            // Update product quantity
            $product->quantity_in_stock = $newQuantity;
            $product->save();
            
            DB::commit();
            
            return back()->with('success', 'Inventory updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating inventory: ' . $e->getMessage());
        }
    }
    
    /**
     * Display low stock report.
     */
    public function lowStockReport()
    {
        $products = Product::with('category')
            ->where('quantity_in_stock', '<=', DB::raw('minimum_stock_level'))
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('quantity_in_stock')
            ->paginate(15)
            ->withQueryString();
            
        // Get categories for filter dropdown
        $categories = ProductCategory::select('id', 'name')
            ->orderBy('name')
            ->get();
            
        return view('inventory.reports.low-stock', [
            'products' => $products,
            'categories' => $categories,
            'filters' => request()->only(['search', 'category'])
        ]);
    }
    
    /**
     * Process bulk actions on products.
     */
    public function bulkActions(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete,adjust_inventory',
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:products,id',
            'adjustment' => 'required_if:action,adjust_inventory|integer|nullable',
            'reason' => 'required_if:action,adjust_inventory|string|max:255|nullable',
        ]);
        
        $selectedIds = $validated['selected_ids'];
        $count = 0;
        
        DB::beginTransaction();
        
        try {
            switch ($validated['action']) {
                case 'activate':
                    $count = Product::whereIn('id', $selectedIds)
                        ->update(['is_active' => true]);
                    $message = "$count products activated successfully.";
                    break;
                    
                case 'deactivate':
                    $count = Product::whereIn('id', $selectedIds)
                        ->update(['is_active' => false]);
                    $message = "$count products deactivated successfully.";
                    break;
                    
                case 'delete':
                    $products = Product::whereIn('id', $selectedIds)->get();
                    foreach ($products as $product) {
                        if ($product->image_url) {
                            Storage::disk('public')->delete($product->image_url);
                        }
                        $product->delete();
                    }
                    $count = count($products);
                    $message = "$count products deleted successfully.";
                    break;
                    
                case 'adjust_inventory':
                    $adjustment = (int) $validated['adjustment'];
                    $reason = $validated['reason'];
                    $now = now();
                    
                    // Get all products to adjust
                    $products = Product::whereIn('id', $selectedIds)->get();
                    $inventoryRecords = [];
                    
                    foreach ($products as $product) {
                        $newQuantity = $product->quantity_in_stock + $adjustment;
                        if ($newQuantity < 0) continue; // Skip if adjustment would make quantity negative
                        
                        $inventoryRecords[] = [
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'adjustment' => $adjustment,
                            'quantity_before' => $product->quantity_in_stock,
                            'quantity_after' => $newQuantity,
                            'reason' => $reason,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        
                        // Update product quantity
                        $product->quantity_in_stock = $newQuantity;
                        $product->save();
                    }
                    
                    // Bulk insert inventory records
                    if (!empty($inventoryRecords)) {
                        Inventory::insert($inventoryRecords);
                    }
                    
                    $count = count($products);
                    $message = "Inventory adjusted for $count products.";
                    break;
            }
            
            DB::commit();
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
    }
}
