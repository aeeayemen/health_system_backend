<?php

namespace App\Http\Controllers;

use App\Models\DietComponent;
use Illuminate\Http\Request;

class DietComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = DietComponent::with(['doctor', 'diet']);
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periods_time' => 'nullable|string',
            'period_name' => 'nullable|string',
            'doctor_id' => 'required|exists:doctors,id',
            'diet_id' => 'required|exists:diets,id',
        ]);

        $component = DietComponent::create($validated);
        return response()->json($component, 201);
    }

    public function show($id)
    {
        return DietComponent::with(['doctor', 'diet'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $component = DietComponent::findOrFail($id);
        $component->update($request->all());
        return response()->json($component);
    }

    public function destroy($id)
    {
        DietComponent::destroy($id);
        return response()->json(null, 204);
    }
}
