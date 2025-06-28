<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommissionStructureRequest;
use App\Http\Resources\CommissionStructureResource;
use App\Models\CommissionRule;
use App\Models\CommissionStructure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CommissionStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CommissionStructure::query()
            ->with('rules')
            ->latest();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $structures = $query->paginate();

        return CommissionStructureResource::collection($structures);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommissionStructureRequest $request): JsonResponse
    {
        $structure = DB::transaction(function () use ($request) {
            $structure = CommissionStructure::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'default_rate' => $request->default_rate,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->has('rules')) {
                $this->syncRules($structure, $request->rules);
            }

            return $structure->load('rules');
        });

        return response()->json([
            'message' => 'Commission structure created successfully',
            'data' => new CommissionStructureResource($structure),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CommissionStructure $commissionStructure): CommissionStructureResource
    {
        return new CommissionStructureResource($commissionStructure->load('rules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCommissionStructureRequest $request, CommissionStructure $commissionStructure): JsonResponse
    {
        $updatedStructure = DB::transaction(function () use ($request, $commissionStructure) {
            $commissionStructure->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'default_rate' => $request->default_rate,
                'is_active' => $request->boolean('is_active', $commissionStructure->is_active),
            ]);

            if ($request->has('rules')) {
                $this->syncRules($commissionStructure, $request->rules);
            }

            return $commissionStructure->load('rules');
        });

        return response()->json([
            'message' => 'Commission structure updated successfully',
            'data' => new CommissionStructureResource($updatedStructure),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommissionStructure $commissionStructure): JsonResponse
    {
        // Prevent deletion if the structure is in use
        if ($commissionStructure->staff()->exists()) {
            return response()->json([
                'message' => 'Cannot delete commission structure that is assigned to staff members',
            ], 422);
        }

        $commissionStructure->delete();

        return response()->json([
            'message' => 'Commission structure deleted successfully',
        ]);
    }

    /**
     * Sync commission rules for a structure
     */
    protected function syncRules(CommissionStructure $structure, array $rules): void
    {
        $ruleIds = [];
        
        foreach ($rules as $ruleData) {
            $rule = $structure->rules()->updateOrCreate(
                ['id' => $ruleData['id'] ?? null],
                [
                    'name' => $ruleData['name'],
                    'description' => $ruleData['description'] ?? null,
                    'condition_type' => $ruleData['condition_type'],
                    'min_value' => $ruleData['min_value'],
                    'max_value' => $ruleData['max_value'] ?? null,
                    'rate' => $ruleData['rate'],
                    'is_active' => $ruleData['is_active'] ?? true,
                    'priority' => $ruleData['priority'],
                ]
            );
            
            $ruleIds[] = $rule->id;
        }
        
        // Delete rules not in the updated set
        $structure->rules()->whereNotIn('id', $ruleIds)->delete();
    }
}
