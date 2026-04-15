<?php

namespace App\Http\Controllers;

use App\Models\Diet;
use Illuminate\Http\Request;

class DietController extends Controller
{
    public function index(Request $request)
    {
        $query = Diet::with('doctor');

        if ($request->user()->isPatient()) {
            $query->where('user_id', $request->user()->id);
        } elseif ($request->user()->isDoctor() && $request->user()->doctor) {
            $query->where('doctor_id', $request->user()->doctor->id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('patient_id')) {
            $query->where('user_id', $request->patient_id);
        }
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        return response()->json($query->latest()->paginate(20));
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
        $patientId = $user->patient ? $user->patient->id : null;

        // 1. First check the new DietPlan table (Prioritized)
        if ($user->isPatient()) {
            $dietPlan = \App\Models\DietPlan::with(['doctor', 'meals'])
                ->where(function($q) use ($user, $patientId) {
                    $q->where('patient_id', $user->id);
                    if ($patientId) {
                        $q->orWhere('patient_id', $patientId);
                    }
                })
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($dietPlan) {
                return response()->json([
                    'id' => $dietPlan->id,
                    'doctor_id' => $dietPlan->doctor_id,
                    'user_id' => $user->id,
                    'status' => $dietPlan->status,
                    'created_at' => $dietPlan->created_at,
                    'updated_at' => $dietPlan->updated_at,
                    'title' => $dietPlan->title,
                    'description' => $dietPlan->description,
                    'daily_calories' => (int)$dietPlan->daily_calories,
                    'start_date' => $dietPlan->start_date?->toDateString() ?? $dietPlan->start_date,
                    'end_date' => $dietPlan->end_date?->toDateString() ?? $dietPlan->end_date,
                    'duration_days' => (int)$dietPlan->duration_days,
                    'periods' => $dietPlan->notes, // meal_periods in notes
                    'doctor' => $dietPlan->doctor,
                    'components' => $dietPlan->meals->map(function ($meal) {
                        $calories = $meal->calories ?? $meal->energy ?? 0;
                        return [
                            'id' => $meal->id,
                            'diet_id' => $meal->diet_plan_id,
                            'meal' => $meal->meal_type . ' - ' . $meal->name,
                            'day' => 'Day ' . $meal->day_number,
                            'time' => null,
                            'quantity' => $meal->serving,
                            'notes' => 'Calories: ' . $calories . ', Carbs: ' . ($meal->carbo ?? 0) . 'g, Protein: ' . ($meal->protin ?? 0) . 'g, Fat: ' . ($meal->fat ?? 0) . 'g'
                        ];
                    }),
                    'notes' => []
                ]);
            }
        }

        // 2. Fallback to legacy Diet table
        $diet = Diet::with(['doctor', 'components', 'notes'])
            ->where('user_id', $user->id)
            ->orWhereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->first();

        if ($diet) {
            return response()->json($diet);
        }

        return response()->json(['message' => 'No active diet found'], 404);
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

        if ($diet) {
            $periods = json_decode($diet->periods, true) ?? [];
            return response()->json([
                'diet_id' => $diet->id,
                'periods' => $periods
            ]);
        }

        if ($user->isPatient()) {
            $dietPlan = \App\Models\DietPlan::where(function($q) use ($user, $patientId) {
                    $q->where('patient_id', $user->id);
                    if ($patientId) {
                        $q->orWhere('patient_id', $patientId);
                    }
                })
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($dietPlan) {
                $periods = json_decode($dietPlan->notes, true) ?? [];
                return response()->json([
                    'diet_id' => $dietPlan->id,
                    'periods' => $periods
                ]);
            }
        }

        return response()->json(['message' => 'No active diet found'], 404);
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

        if ($diet) {
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

        if ($user->isPatient()) {
            $dietPlan = \App\Models\DietPlan::with('meals')
                ->where(function($q) use ($user, $patientId) {
                    $q->where('patient_id', $user->id);
                    if ($patientId) {
                        $q->orWhere('patient_id', $patientId);
                    }
                })
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($dietPlan) {
                $meals = $dietPlan->meals->map(function ($meal) {
                    return [
                        'id' => $meal->id,
                        'meal' => $meal->meal_type . ' - ' . $meal->name,
                        'day' => 'Day ' . $meal->day_number,
                        'time' => null,
                        'quantity' => $meal->serving,
                        'notes' => 'Calories: ' . $meal->calories . ', Carbs: ' . $meal->carbo . 'g, Protein: ' . $meal->protin . 'g, Fat: ' . $meal->fat . 'g'
                    ];
                });

                return response()->json([
                    'diet_id' => $dietPlan->id,
                    'meals' => $meals
                ]);
            }
        }

        return response()->json(['message' => 'No active diet found'], 404);
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

        if ($diet) {
            return response()->json([
                'diet' => $diet,
                'total_meals' => $diet->components->count(),
                'total_notes' => $diet->notes->count(),
                'doctor' => $diet->doctor,
                'start_date' => $diet->created_at,
                'status' => $diet->status ?? 'active'
            ]);
        }

        if ($user->isPatient()) {
            $dietPlan = \App\Models\DietPlan::with(['doctor', 'meals'])
                ->where(function($q) use ($user, $patientId) {
                    $q->where('patient_id', $user->id);
                    if ($patientId) {
                        $q->orWhere('patient_id', $patientId);
                    }
                })
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($dietPlan) {
                return response()->json([
                    'diet' => [
                        'id' => $dietPlan->id,
                        'title' => $dietPlan->title,
                        'description' => $dietPlan->description,
                        'daily_calories' => $dietPlan->daily_calories,
                        'status' => $dietPlan->status,
                    ],
                    'total_meals' => $dietPlan->meals->count(),
                    'total_notes' => 0, // No specific notes table in diet_plan structure directly
                    'doctor' => $dietPlan->doctor,
                    'start_date' => $dietPlan->created_at,
                    'status' => $dietPlan->status
                ]);
            }
        }

        return response()->json(['message' => 'No diet found'], 404);
    }
}
