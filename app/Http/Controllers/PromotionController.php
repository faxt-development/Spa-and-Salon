<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Service;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
        
    }

    /**
     * Display a listing of the promotions.
     */
    public function index(Request $request)
    {
        $query = Promotion::withCount('usages')
            ->with('services')
            ->latest();
            
        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('ends_at')
                          ->orWhere('ends_at', '>=', now());
                    });
            } elseif ($status === 'scheduled') {
                $query->where('is_active', true)
                    ->where('starts_at', '>', now());
            } elseif ($status === 'expired') {
                $query->where(function($q) {
                    $q->where('is_active', false)
                      ->orWhere('ends_at', '<', now())
                      ->orWhere(function($q) {
                          $q->whereColumn('usage_limit', '<=', 
                              DB::raw('(SELECT COUNT(*) FROM promotion_usages WHERE promotion_usages.promotion_id = promotions.id)')
                          );
                      });
                });
            }
        }
        
        $promotions = $query->paginate(15)->withQueryString();
        
        return view('promotions.index', [
            'promotions' => $promotions,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
            'statusOptions' => [
                '' => 'All Statuses',
                'active' => 'Active',
                'scheduled' => 'Scheduled',
                'expired' => 'Expired',
            ],
        ]);
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create()
    {
        $services = Service::select(['id', 'name', 'price'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('promotions.form', [
            'promotion' => new Promotion([
                'is_active' => true,
                'is_public' => true,
            ]),
            'types' => $this->getPromotionTypes(),
            'services' => $services,
            'selectedServices' => collect(),
        ]);
    }

    /**
     * Store a newly created promotion in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,bogo,package',
            'value' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($request) {
                if ($request->type === 'percentage' && ($value <= 0 || $value > 100)) {
                    $fail('Percentage must be between 0 and 100');
                }
            }],
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'min_requirements' => 'nullable|array',
            'min_requirements.type' => 'required_with:min_requirements|in:none,min_amount,min_quantity',
            'min_requirements.value' => 'required_with:min_requirements|numeric|min:0',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        try {
            DB::beginTransaction();

            $promotion = new Promotion();
            $promotion->code = strtoupper($validated['code']);
            $promotion->name = $validated['name'];
            $promotion->description = $validated['description'] ?? null;
            $promotion->type = $validated['type'];
            $promotion->value = $validated['value'];
            $promotion->is_active = $validated['is_active'] ?? false;
            $promotion->is_public = $validated['is_public'] ?? false;
            $promotion->starts_at = $validated['starts_at'] ?? null;
            $promotion->ends_at = $validated['ends_at'] ?? null;
            $promotion->usage_limit = $validated['usage_limit'] ?? null;
            
            // Set restrictions
            $restrictions = [];
            if (isset($validated['min_requirements'])) {
                $restrictions['min_requirements'] = $validated['min_requirements'];
            }
            $promotion->restrictions = !empty($restrictions) ? $restrictions : null;
            
            $promotion->save();
            
            // Sync services if any
            if (isset($validated['service_ids'])) {
                $promotion->services()->sync($validated['service_ids']);
            }
            
            DB::commit();
            
            return redirect()->route('promotions.show', $promotion)
                ->with('success', 'Promotion created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating promotion: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create promotion. Please try again.');
        }
    }

    /**
     * Display the specified promotion.
     */
    public function show(Promotion $promotion)
    {
        $promotion->load([
            'usages' => function($query) {
                $query->latest()->take(10);
            },
            'services'
        ]);
        
        $usageStats = [
            'total' => $promotion->usages()->count(),
            'this_month' => $promotion->usages()
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'last_month' => $promotion->usages()
                ->whereBetween('created_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])
                ->count(),
        ];
        
        $usageByMonth = $promotion->usages()
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');
        
        return view('promotions.show', [
            'promotion' => $promotion,
            'usageStats' => $usageStats,
            'usageByMonth' => $usageByMonth,
        ]);
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion)
    {
        $services = Service::select(['id', 'name', 'price'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $promotion->load('services');
        
        return view('promotions.form', [
            'promotion' => $promotion,
            'types' => $this->getPromotionTypes(),
            'services' => $services,
            'selectedServices' => $promotion->services->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code,' . $promotion->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,bogo,package',
            'value' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($request) {
                if ($request->type === 'percentage' && ($value <= 0 || $value > 100)) {
                    $fail('Percentage must be between 0 and 100');
                }
            }],
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'min_requirements' => 'nullable|array',
            'min_requirements.type' => 'required_with:min_requirements|in:none,min_amount,min_quantity',
            'min_requirements.value' => 'required_with:min_requirements|numeric|min:0',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        try {
            DB::beginTransaction();

            $promotion->code = strtoupper($validated['code']);
            $promotion->name = $validated['name'];
            $promotion->description = $validated['description'] ?? null;
            $promotion->type = $validated['type'];
            $promotion->value = $validated['value'];
            $promotion->is_active = $validated['is_active'] ?? false;
            $promotion->is_public = $validated['is_public'] ?? false;
            $promotion->starts_at = $validated['starts_at'] ?? null;
            $promotion->ends_at = $validated['ends_at'] ?? null;
            $promotion->usage_limit = $validated['usage_limit'] ?? null;
            
            // Set restrictions
            $restrictions = [];
            if (isset($validated['min_requirements'])) {
                $restrictions['min_requirements'] = $validated['min_requirements'];
            }
            $promotion->restrictions = !empty($restrictions) ? $restrictions : null;
            
            $promotion->save();
            
            // Sync services
            $promotion->services()->sync($validated['service_ids'] ?? []);
            
            DB::commit();
            
            return redirect()->route('promotions.show', $promotion)
                ->with('success', 'Promotion updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating promotion: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update promotion. Please try again.');
        }
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(Promotion $promotion)
    {
        try {
            DB::beginTransaction();
            
            // Delete related records first
            $promotion->usages()->delete();
            $promotion->services()->detach();
            
            // Then delete the promotion
            $promotion->delete();
            
            DB::commit();
            
            return redirect()
                ->route('promotions.index')
                ->with('success', 'Promotion deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting promotion: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Failed to delete promotion. Please try again.');
        }
    }

    /**
     * Apply a promotion code to the current cart/booking.
     */
    public function applyCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'items' => 'required|array',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->promotionService->applyPromotion(
                $validated['code'],
                $validated['items'],
                $request->user()
            );

            return response()->json([
                'success' => true,
                'discount' => $result['discount'],
                'final_amount' => $result['final_amount'],
                'promotion' => $result['promotion'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get the promotion types configuration
     * 
     * @return array
     */
    private function getPromotionTypes()
    {
        return [
            'percentage' => [
                'label' => 'Percentage Off',
                'description' => 'Discount a percentage from the total or item price',
                'value_label' => 'Percentage',
                'value_suffix' => '%',
                'min' => 1,
                'max' => 100,
                'step' => 0.01,
            ],
            'fixed' => [
                'label' => 'Fixed Amount Off',
                'description' => 'Discount a fixed amount from the total or item price',
                'value_label' => 'Amount',
                'value_prefix' => '$',
                'min' => 0.01,
                'step' => 0.01,
            ],
            'bogo' => [
                'label' => 'Buy One Get One',
                'description' => 'Buy one item, get another of equal or lesser value free',
                'value_label' => 'Discount Value',
                'value_prefix' => '$',
                'min' => 0.01,
                'step' => 0.01,
            ],
            'package' => [
                'label' => 'Package Deal',
                'description' => 'Special pricing when purchasing multiple services together',
                'value_label' => 'Package Price',
                'value_prefix' => '$',
                'min' => 0.01,
                'step' => 0.01,
            ],
        ];
    }
}
