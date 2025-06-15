<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with optional filtering and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Filter by name or SKU
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by stock level
        if ($request->has('in_stock')) {
            if ($request->boolean('in_stock')) {
                $query->where('quantity', '>', 0);
            } else {
                $query->where('quantity', '<=', 0);
            }
        }

        // Sort products
        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
            'categories' => ProductCategory::select('id', 'name')->get(),
            'suppliers' => Supplier::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:50|unique:products',
            'barcode' => 'nullable|string|max:50|unique:products',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'tax_rate' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $productData = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image_path'] = $imagePath;
        }

        try {
            DB::beginTransaction();
            
            $product = Product::create($productData);
            
            // Log initial inventory if quantity > 0
            if ($product->quantity_in_stock > 0) {
                $this->logInventoryTransaction(
                    $product->id,
                    InventoryTransaction::TYPE_PURCHASE,
                    $product->quantity_in_stock,
                    0,
                    $product->quantity_in_stock,
                    null,
                    null,
                    'Initial inventory on product creation'
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'supplier']),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::with(['category', 'supplier'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $id,
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'tax_rate' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $productData = $request->except(['image']);

        // Handle image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Delete old image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image_path'] = $imagePath;
        }

        $product->update($productData);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->fresh(['category', 'supplier']),
        ]);
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete product image if exists
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Update product inventory quantity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateInventory(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_id' => 'nullable|integer',
            'reference_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $oldQuantity = $product->quantity_in_stock;
            $adjustmentQuantity = $request->quantity;
            $transactionType = InventoryTransaction::TYPE_ADJUSTMENT;
            
            switch ($request->adjustment_type) {
                case 'add':
                    $product->quantity_in_stock += $adjustmentQuantity;
                    if ($request->has('supplier_id')) {
                        $transactionType = InventoryTransaction::TYPE_PURCHASE;
                    }
                    break;
                case 'subtract':
                    $product->quantity_in_stock = max(0, $product->quantity_in_stock - $adjustmentQuantity);
                    $adjustmentQuantity = -$adjustmentQuantity; // Negative for reduction
                    $transactionType = InventoryTransaction::TYPE_WASTE; // Default to waste if no other reason
                    break;
                case 'set':
                    $adjustmentQuantity = $request->quantity - $oldQuantity; // Calculate the difference
                    $product->quantity_in_stock = $request->quantity;
                    break;
            }
            
            $product->save();
            
            // Log the inventory transaction
            $transaction = $this->logInventoryTransaction(
                $product->id,
                $transactionType,
                $adjustmentQuantity,
                $oldQuantity,
                $product->quantity_in_stock,
                $request->reference_type ?? null,
                $request->reference_id ?? null,
                $request->reason ?? 'Manual inventory adjustment',
                $request->supplier_id ?? null
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully',
                'data' => [
                    'product' => $product->fresh(),
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $product->quantity_in_stock,
                    'adjustment' => $request->adjustment_type,
                    'transaction' => $transaction,
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Log an inventory transaction.
     *
     * @param int $productId
     * @param string $type
     * @param int $quantity
     * @param int $previousQuantity
     * @param int $newQuantity
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     * @param int|null $supplierId
     * @return \App\Models\InventoryTransaction
     */
    private function logInventoryTransaction(
        $productId,
        $type,
        $quantity,
        $previousQuantity,
        $newQuantity,
        $referenceType = null,
        $referenceId = null,
        $notes = null,
        $supplierId = null
    ) {
        // Get the current authenticated staff member if available
        $staffId = null;
        if (Auth::check() && Auth::user()->staff) {
            $staffId = Auth::user()->staff->id;
        }
        
        $transaction = InventoryTransaction::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'staff_id' => $staffId,
            'supplier_id' => $supplierId,
        ]);
        
        return $transaction;
    }
}
