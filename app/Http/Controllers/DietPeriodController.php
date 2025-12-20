<?php

namespace App\Http\Controllers;

use App\Models\DietPeriod;
use Illuminate\Http\Request;

class DietPeriodController extends Controller
{
    public function index(Request $request)
    {
        $query = DietPeriod::query();

        if ($request->has('diet_id')) {
            $query->where('diet_id', $request->diet_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'diet_id' => 'required|exists:diets,id',
            'name' => 'required|string',
            'start_day' => 'required|integer',
            'end_day' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $period = DietPeriod::create($validated);
        return response()->json($period, 201);
    }

    public function show($id)
    {
        return DietPeriod::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $period = DietPeriod::findOrFail($id);

        $validated = $request->validate([
            'diet_id' => 'sometimes|exists:diets,id',
            'name' => 'sometimes|string',
            'start_day' => 'sometimes|integer',
            'end_day' => 'sometimes|integer',
            'description' => 'nullable|string',
        ]);

        $period->update($validated);
        return response()->json($period);
    }

    public function destroy($id)
    {
        DietPeriod::destroy($id);
        return response()->json(null, 204);
    }
}
