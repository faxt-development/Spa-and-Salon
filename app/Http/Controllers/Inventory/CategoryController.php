<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // For AJAX requests, return the full tree
        if ($request->ajax() || $request->wantsJson()) {
            $categories = ProductCategory::withCount('products')
                ->defaultOrder()
                ->get()
                ->toTree();

            return response()->json($categories);
        }

        // For regular requests, return the paginated list
        $query = ProductCategory::withCount('products')
            ->with(['parent'])
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $categories = $query->paginate(15)->withQueryString();

        return view('inventory.categories.index', [
            'categories' => $categories,
            'search' => $search
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:product_categories,slug',
            'parent_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
            'image_path' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $category = new ProductCategory($validated);

            if (!empty($validated['parent_id'])) {
                $parent = ProductCategory::findOrFail($validated['parent_id']);
                $category->appendToNode($parent);
            }

            $category->save();

            DB::commit();

            return redirect()
                ->route('inventory.categories.index')
                ->with('success', 'Category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories')->ignore($category->id),
            ],
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    // Prevent making a category a child of itself or its descendants
                    if ($value && $category->id == $value) {
                        $fail('A category cannot be a parent of itself.');
                    }

                    if ($value && $category->descendants->contains('id', $value)) {
                        $fail('A category cannot be a child of its own descendant.');
                    }
                },
            ],
            'is_active' => 'boolean',
            'image_path' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $category->fill($validated);

            // Handle parent change if needed
            if ($category->isDirty('parent_id')) {
                if (!empty($validated['parent_id'])) {
                    $parent = ProductCategory::findOrFail($validated['parent_id']);
                    $category->appendToNode($parent);
                } else {
                    $category->makeRoot();
                }
            }

            $category->save();

            DB::commit();

            return redirect()
                ->route('inventory.categories.index')
                ->with('success', 'Category updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }


    /**
     * Handle reordering of categories via drag and drop.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'moved_id' => 'required|exists:product_categories,id',
            'target_id' => 'required|exists:product_categories,id',
            'position' => 'required|in:before,after,inside',
        ]);

        $movedNode = ProductCategory::findOrFail($request->moved_id);
        $targetNode = ProductCategory::findOrFail($request->target_id);

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
                'message' => 'Category order updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the category tree for the sidebar or other hierarchical displays.
     */
    public function tree()
    {
        $categories = ProductCategory::withCount('products')
            ->defaultOrder()
            ->get()
            ->toTree();

        return response()->json($categories);
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
    public function show(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        //
    }


    /**
     * Get the product count for a category.
     */
    public function getProductCount(ProductCategory $category)
    {
        $count = $category->products()->count();
        return response()->json(['count' => $count]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        // Prevent deletion if category has products
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete category with associated products.');
        }

        try {
            // Move all products to the uncategorized category
            $category->products()->update(['category_id' => $uncategorized->id]);

            // Delete the category image if it exists
            if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }

            // Delete the category
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
                'redirect' => route('inventory.categories.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk actions for categories
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
            'action' => 'required|in:activate,deactivate,delete'
        ]);

        $categories = ProductCategory::whereIn('id', $request->categories)->get();
        $action = $request->action;
        $count = 0;

        DB::beginTransaction();

        try {
            foreach ($categories as $category) {
                switch ($action) {
                    case 'activate':
                        $category->update(['is_active' => true]);
                        $count++;
                        break;

                    case 'deactivate':
                        $category->update(['is_active' => false]);
                        $count++;
                        break;

                    case 'delete':
                        // Get or create the uncategorized category
                        $uncategorized = ProductCategory::firstOrCreate(
                            ['name' => 'Uncategorized'],
                            [
                                'description' => 'Default category for uncategorized products',
                                'is_active' => true
                            ]
                        );

                        // Move products to uncategorized
                        $category->products()->update(['category_id' => $uncategorized->id]);

                        // Delete the category image if it exists
                        if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                            Storage::disk('public')->delete($category->image_path);
                        }

                        $category->delete();
                        $count++;
                        break;
                }
            }

            DB::commit();

            $message = match($action) {
                'activate' => "$count categories activated successfully.",
                'deactivate' => "$count categories deactivated successfully.",
                'delete' => "$count categories deleted successfully.",
                default => 'Action completed successfully.'
            };

            return redirect()
                ->route('inventory.categories.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk action failed: ' . $e->getMessage());

            return redirect()
                ->route('inventory.categories.index')
                ->with('error', 'Failed to complete bulk action: ' . $e->getMessage());
        }
    }
}
