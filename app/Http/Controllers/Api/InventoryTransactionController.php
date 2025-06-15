<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryTransactionController extends Controller
{
    /**
     * Display a listing of inventory transactions with filtering options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'staff', 'supplier']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by transaction type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by staff member
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort transactions
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $transactions = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'transaction_types' => [
                InventoryTransaction::TYPE_PURCHASE,
                InventoryTransaction::TYPE_SALE,
                InventoryTransaction::TYPE_ADJUSTMENT,
                InventoryTransaction::TYPE_RETURN,
                InventoryTransaction::TYPE_WASTE,
                InventoryTransaction::TYPE_TRANSFER,
            ],
        ]);
    }

    /**
     * Display the specified inventory transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = InventoryTransaction::with(['product', 'staff', 'supplier'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Get inventory transaction history for a specific product.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\Response
     */
    public function productHistory($productId)
    {
        $product = Product::findOrFail($productId);
        
        $transactions = InventoryTransaction::where('product_id', $productId)
            ->with(['staff', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return response()->json([
            'success' => true,
            'product' => $product,
            'data' => $transactions,
        ]);
    }

    /**
     * Get inventory transaction summary statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = InventoryTransaction::query();

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Get summary statistics
        $summary = [
            'total_transactions' => $query->count(),
            'by_type' => [
                'purchase' => $query->clone()->where('type', InventoryTransaction::TYPE_PURCHASE)->count(),
                'sale' => $query->clone()->where('type', InventoryTransaction::TYPE_SALE)->count(),
                'adjustment' => $query->clone()->where('type', InventoryTransaction::TYPE_ADJUSTMENT)->count(),
                'return' => $query->clone()->where('type', InventoryTransaction::TYPE_RETURN)->count(),
                'waste' => $query->clone()->where('type', InventoryTransaction::TYPE_WASTE)->count(),
                'transfer' => $query->clone()->where('type', InventoryTransaction::TYPE_TRANSFER)->count(),
            ],
            'total_quantity_change' => $query->sum('quantity'),
            'products_with_most_transactions' => InventoryTransaction::select('product_id')
                ->selectRaw('COUNT(*) as transaction_count')
                ->with('product:id,name,sku')
                ->groupBy('product_id')
                ->orderByDesc('transaction_count')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
