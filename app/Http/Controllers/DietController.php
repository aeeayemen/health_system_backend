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

    /**
     * Update diet status.
     */
    public function updateStatus(Request $request, $id)
    {
        $diet = Diet::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $diet->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Diet status updated successfully',
            'diet' => $diet
        ]);
    }

    /**
     * Get current user's diet
     */
    public function myDiet(Request $request)
    {
        $user = $request->user();

        // Find active diet for user (through subscription or direct assignment)
        $diet = Diet::with(['doctor', 'components', 'notes'])
            ->where('user_id', $user->id)
            ->orWhereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->first();

        if (!$diet) {
            return response()->json(['message' => 'No active diet found'], 404);
        }

        return response()->json($diet);
    }

    /**
     * Get current user's diet periods
     */
    public function myDietPeriods(Request $request)
    {
        $user = $request->user();

        $diet = Diet::where('user_id', $user->id)
            ->orWhereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->first();

        if (!$diet) {
            return response()->json(['message' => 'No active diet found'], 404);
        }

        // Return diet periods
        $periods = json_decode($diet->periods, true) ?? [];

        return response()->json([
            'diet_id' => $diet->id,
            'periods' => $periods
        ]);
    }

    /**
     * Get current user's diet meals
     */
    public function myDietMeals(Request $request)
    {
        $user = $request->user();

        $diet = Diet::with('components.meal')
            ->where('user_id', $user->id)
            ->orWhereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->first();

        if (!$diet) {
            return response()->json(['message' => 'No active diet found'], 404);
        }

        $meals = $diet->components->map(function ($component) {
            return [
                'id' => $component->id,
                'meal' => $component->meal,
                'day' => $component->day,
                'time' => $component->time,
                'quantity' => $component->quantity,
                'notes' => $component->notes
            ];
        });

        return response()->json([
            'diet_id' => $diet->id,
            'meals' => $meals
        ]);
    }

    /**
     * Get current user's diet report
     */
    public function myDietReport(Request $request)
    {
        $user = $request->user();

        $diet = Diet::with(['doctor', 'components', 'notes'])
            ->where('user_id', $user->id)
            ->orWhereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->first();

        if (!$diet) {
            return response()->json(['message' => 'No diet found'], 404);
        }

        return response()->json([
            'diet' => $diet,
            'total_meals' => $diet->components->count(),
            'total_notes' => $diet->notes->count(),
            'doctor' => $diet->doctor,
            'start_date' => $diet->created_at,
            'status' => $diet->status ?? 'active'
        ]);
    }
}
