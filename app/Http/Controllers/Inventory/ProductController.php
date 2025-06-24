<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // For AJAX requests, return the full tree
        if ($request->ajax() || $request->wantsJson()) {
            $products = Product::get();

            return response()->json($product);
        }

        
        $products = Product::get();

        return view('inventory.products.index', [
            'products' => $products,
            'search' => $search
        ]);
    }

    /**
     * Store a newly created Product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:product_$product,slug',
            'parent_id' => 'nullable|exists:product_$product,id',
            'is_active' => 'boolean',
            'image_path' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $product = new Product($validated);

            if (!empty($validated['parent_id'])) {
                $parent = Product::findOrFail($validated['parent_id']);
                $product->appendToNode($parent);
            }

            $product->save();

            DB::commit();

            return redirect()
                ->route('inventory.products.index')
                ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create Product: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified Product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_$product')->ignore($product->id),
            ],
            'parent_id' => [
                'nullable',
                'exists:product_$product,id',
                function ($attribute, $value, $fail) use ($product) {
                    // Prevent making a Product a child of itself or its descendants
                    if ($value && $product->id == $value) {
                        $fail('A Product cannot be a parent of itself.');
                    }

                    if ($value && $product->descendants->contains('id', $value)) {
                        $fail('A Product cannot be a child of its own descendant.');
                    }
                },
            ],
            'is_active' => 'boolean',
            'image_path' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $product->fill($validated);

            // Handle parent change if needed
            if ($product->isDirty('parent_id')) {
                if (!empty($validated['parent_id'])) {
                    $parent = Product::findOrFail($validated['parent_id']);
                    $product->appendToNode($parent);
                } else {
                    $product->makeRoot();
                }
            }

            $product->save();

            DB::commit();

            return redirect()
                ->route('inventory.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update Product: ' . $e->getMessage());
        }
    }


    /**
     * Handle reordering of $product via drag and drop.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'moved_id' => 'required|exists:product_$product,id',
            'target_id' => 'required|exists:product_$product,id',
            'position' => 'required|in:before,after,inside',
        ]);

        $movedNode = Product::findOrFail($request->moved_id);
        $targetNode = Product::findOrFail($request->target_id);

        DB::beginTransaction();
        try {
            switch ($request->position) {
                case 'before':
                    $movedNode->insertBeforeNode($targetNode);
                    break;
                case 'after':
                    $movedNode->insertAfterNode($targetNode);
                    break;
                case 'inside':
                    $movedNode->appendToNode($targetNode);
                    break;
            }

            $movedNode->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product order updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder $product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the Product tree for the sidebar or other hierarchical displays.
     */
    public function tree()
    {
        $product = Product::get();

        return response()->json($product);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $Product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $Product)
    {
        //
    }


    /**
     * Get the product count for a Product.
     */
    public function getProductCount(Product $product)
    {
        $count = $product->products()->count();
        return response()->json(['count' => $count]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Prevent deletion if Product has products
        if ($product->products()->exists()) {
            return back()->with('error', 'Cannot delete Product with associated products.');
        }

        try {
            // Delete the Product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
                'redirect' => route('inventory.products.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Product: ' . $e->getMessage()
            ], 500);
        }
    }

    }
