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

    /**
     * Rate a doctor (by current user)
     */
    public function rateDoctor(Request $request, $doctorId)
    {
        $user = $request->user();

        $validated = $request->validate([
            'rate' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Check if user already rated this doctor
        $existingRate = Rate::where('user_id', $user->id)
            ->where('doctor_id', $doctorId)
            ->first();

        if ($existingRate) {
            // Update existing rate
            $existingRate->update([
                'rate' => $validated['rate'],
                'comment' => $validated['comment'] ?? null,
            ]);

            return response()->json([
                'message' => 'Rating updated successfully',
                'rate' => $existingRate->fresh()
            ]);
        }

        // Create new rate
        $rate = Rate::create([
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
            'rate' => $validated['rate'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Doctor rated successfully',
            'rate' => $rate
        ], 201);
    }

    /**
     * Get all ratings for a specific doctor
     */
    public function getDoctorRates($doctorId)
    {
        $rates = Rate::with('user:id,name')
            ->where('doctor_id', $doctorId)
            ->orderBy('created_at', 'desc')
            ->get();

        $average = $rates->avg('rate');
        $count = $rates->count();

        return response()->json([
            'doctor_id' => $doctorId,
            'average_rating' => round($average, 2),
            'total_ratings' => $count,
            'ratings' => $rates
        ]);
    }

    /**
     * Get current user's ratings history
     */
    public function myRates(Request $request)
    {
        $user = $request->user();

        $rates = Rate::with('doctor:id,name,specialization')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($rates);
    }
}
