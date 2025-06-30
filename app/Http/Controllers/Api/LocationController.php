<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $locations = Location::active()->orderBy('name')->get();
        
        return response()->json([
            'data' => $locations->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->formatted_address,
                    'is_primary' => $location->is_primary,
                ];
            })
        ]);
    }

    /**
     * Display the specified location.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $location = Location::findOrFail($id);
        
        return response()->json([
            'data' => [
                'id' => $location->id,
                'name' => $location->name,
                'address_line_1' => $location->address_line_1,
                'address_line_2' => $location->address_line_2,
                'city' => $location->city,
                'state' => $location->state,
                'postal_code' => $location->postal_code,
                'country' => $location->country,
                'phone' => $location->phone,
                'email' => $location->email,
                'is_primary' => $location->is_primary,
                'is_active' => $location->is_active,
                'formatted_address' => $location->formatted_address,
                'created_at' => $location->created_at,
                'updated_at' => $location->updated_at,
            ]
        ]);
    }
}
