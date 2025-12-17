<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request)
    {
        $query = Meal::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_id' => 'nullable|integer',
            'name' => 'nullable|string',
            'serving' => 'nullable|string',
            'describtion' => 'nullable|string',
            'carbo' => 'nullable|string',
            'protin' => 'nullable|string',
            'fat' => 'nullable|string',
            'energy' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $meal = Meal::create($validated);
        return response()->json($meal, 201);
    }

    public function show($id)
    {
        return Meal::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $meal = Meal::findOrFail($id);
        $meal->update($request->all());
        return response()->json($meal);
    }

    public function destroy($id)
    {
        Meal::destroy($id);
        return response()->json(null, 204);
    }
}
