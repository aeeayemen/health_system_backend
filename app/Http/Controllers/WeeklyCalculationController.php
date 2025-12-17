<?php

namespace App\Http\Controllers;

use App\Models\WeeklyCalculation;
use Illuminate\Http\Request;

class WeeklyCalculationController extends Controller
{
    public function index(Request $request)
    {
        $query = WeeklyCalculation::with('user');
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waist' => 'nullable|numeric',
            'stomach' => 'nullable|numeric',
            'arm' => 'nullable|numeric',
            'chest' => 'nullable|numeric',
            'thigh' => 'nullable|numeric',
            'shoulder' => 'nullable|numeric',
            'buttocks' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
        ]);

        $calculation = WeeklyCalculation::create($validated);
        return response()->json($calculation, 201);
    }

    public function show($id)
    {
        return WeeklyCalculation::with('user')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $calculation = WeeklyCalculation::findOrFail($id);
        $calculation->update($request->all());
        return response()->json($calculation);
    }

    public function destroy($id)
    {
        WeeklyCalculation::destroy($id);
        return response()->json(null, 204);
    }
}
