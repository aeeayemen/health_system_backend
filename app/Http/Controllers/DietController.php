<?php

namespace App\Http\Controllers;

use App\Models\Diet;
use Illuminate\Http\Request;

class DietController extends Controller
{
    public function index(Request $request)
    {
        $query = Diet::with('doctor');
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'price' => 'nullable|string',
            'doctor_id' => 'required|exists:doctors,id',
            'periods' => 'nullable|string',
            'states_id' => 'nullable|integer',
        ]);

        $diet = Diet::create($validated);
        return response()->json($diet, 201);
    }

    public function show($id)
    {
        return Diet::with('doctor')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $diet = Diet::findOrFail($id);
        $diet->update($request->all());
        return response()->json($diet);
    }

    public function destroy($id)
    {
        Diet::destroy($id);
        return response()->json(null, 204);
    }
}
