<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingChecklistController extends Controller
{
    /**
     * Show the admin onboarding checklist.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = auth()->user();
        $checklistItems = $user->onboarding_checklist_items ?? [];
        
        return view('admin.onboarding-checklist', [
            'title' => 'Admin Onboarding Checklist',
            'checklistItems' => $checklistItems
        ]);
    }
    
    /**
     * Toggle a checklist item's completion status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleItem(Request $request)
    {
        $user = auth()->user();
        $itemKey = $request->input('item_key');
        $isCompleted = $request->input('is_completed', false);
        
        if (!$itemKey) {
            return response()->json(['success' => false, 'message' => 'Item key is required'], 400);
        }
        
        // Get current checklist items or initialize empty array
        $onboardingItems = $user->onboarding_checklist_items ?? [];
        
        // Use dot notation to set nested array values
        $keys = explode('.', $itemKey);
        $current = &$onboardingItems;
        
        foreach ($keys as $key) {
            if (!isset($current[$key]) && next($keys) !== false) {
                $current[$key] = [];
            }
            
            if (next($keys) === false) {
                $current[$key] = $isCompleted;
                break;
            }
            
            $current = &$current[$key];
        }
        
        // Save the updated checklist items
        $user->onboarding_checklist_items = $onboardingItems;
        $user->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Checklist item updated successfully',
            'item_key' => $itemKey,
            'is_completed' => $isCompleted
        ]);
    }
}
