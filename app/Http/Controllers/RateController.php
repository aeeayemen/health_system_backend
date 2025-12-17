<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function index(Request $request)
    {
        $query = Rate::with(['user', 'doctor']);
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'rate' => 'nullable|string',
        ]);

        $rate = Rate::create($validated);
        return response()->json($rate, 201);
    }

    public function show($id)
    {
        return Rate::with(['user', 'doctor'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $rate = Rate::findOrFail($id);
        $rate->update($request->all());
        return response()->json($rate);
    }

    public function destroy($id)
    {
        Rate::destroy($id);
        return response()->json(null, 204);
    }
}
