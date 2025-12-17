<?php

namespace App\Http\Controllers;

use App\Models\MainCalculation;
use Illuminate\Http\Request;

class MainCalculationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(MainCalculation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'calories' => 'nullable|numeric',
            'protin' => 'nullable|numeric',
            'fat' => 'nullable|numeric',
            'carbo' => 'nullable|numeric',
            'BMR' => 'nullable|numeric',
            'BMI' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
        ]);

        $mainCalculation = MainCalculation::create($validated);

        return response()->json($mainCalculation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MainCalculation $mainCalculation)
    {
        return response()->json($mainCalculation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MainCalculation $mainCalculation)
    {
        $validated = $request->validate([
            'calories' => 'nullable|numeric',
            'protin' => 'nullable|numeric',
            'fat' => 'nullable|numeric',
            'carbo' => 'nullable|numeric',
            'BMR' => 'nullable|numeric',
            'BMI' => 'nullable|numeric',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $mainCalculation->update($validated);

        return response()->json($mainCalculation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MainCalculation $mainCalculation)
    {
        $mainCalculation->delete();

        return response()->json(['message' => 'Main calculation deleted successfully']);
    }
}
