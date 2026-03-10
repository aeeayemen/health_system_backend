<?php

namespace App\Http\Controllers;

use App\Models\DietPlan;
use App\Models\Meal;
use App\Http\Resources\DietPlanResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DietPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DietPlan::with(['doctor', 'patient']);

        if ($request->user()->isPatient()) {
            $query->where('patient_id', $request->user()->patient->id);
        } elseif ($request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->doctor->id);
        }

        return DietPlanResource::collection($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'daily_calories' => 'required|integer',
            'duration_days' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            // Accept meals in both old and new format
            'meals' => 'nullable|array',
            'meals.*.meal_type' => 'required|string',
            // Old format fields
            'meals.*.meal_name' => 'nullable|string',
            'meals.*.day_number' => 'nullable|integer',
            'meals.*.carbo' => 'nullable|numeric',
            'meals.*.protin' => 'nullable|numeric',
            'meals.*.fat' => 'nullable|numeric',
            'meals.*.serving' => 'nullable|string',
            // New format fields (mobile app)
            'meals.*.name' => 'nullable|string',
            'meals.*.carbs_g' => 'nullable|numeric',
            'meals.*.protein_g' => 'nullable|numeric',
            'meals.*.fat_g' => 'nullable|numeric',
            'meals.*.serving_summary' => 'nullable|string',
            'meals.*.calories' => 'nullable|integer',
            // Meal periods (new)
            'meal_periods' => 'nullable|array',
            'meal_periods.*.meal_type' => 'required|string',
            'meal_periods.*.hour' => 'required|integer',
            'meal_periods.*.minute' => 'required|integer',
            'meal_periods.*.custom_name' => 'nullable|string',
            // Doctor notes
            'doctor_notes' => 'nullable|array',
            'doctor_notes.*' => 'string'
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $dietPlan = DietPlan::create([
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => $validated['patient_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'daily_calories' => $validated['daily_calories'],
                'duration_days' => $validated['duration_days'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                // Store meal_periods as JSON in notes if provided
                'notes' => isset($validated['meal_periods']) ? json_encode($validated['meal_periods']) : null,
            ]);

            // Add meals - map new format fields to DB columns
            if (isset($validated['meals'])) {
                foreach ($validated['meals'] as $mealData) {
                    $dietPlan->meals()->create([
                        'meal_type' => $mealData['meal_type'],
                        'meal_name' => $mealData['meal_name'] ?? $mealData['name'] ?? $mealData['meal_type'],
                        'name' => $mealData['name'] ?? $mealData['meal_name'] ?? $mealData['meal_type'],
                        'day_number' => $mealData['day_number'] ?? 1,
                        'calories' => $mealData['calories'] ?? null,
                        'carbo' => $mealData['carbo'] ?? $mealData['carbs_g'] ?? null,
                        'protin' => $mealData['protin'] ?? $mealData['protein_g'] ?? null,
                        'fat' => $mealData['fat'] ?? $mealData['fat_g'] ?? null,
                        'serving' => $mealData['serving'] ?? $mealData['serving_summary'] ?? null,
                    ]);
                }
            }

            // Add clinical notes
            if (isset($validated['doctor_notes'])) {
                foreach ($validated['doctor_notes'] as $noteText) {
                    \App\Models\DietNote::create([
                        'diet_id' => $dietPlan->id,
                        'doctor_id' => $validated['doctor_id'],
                        'user_id' => \App\Models\Patient::find($validated['patient_id'])->user_id,
                        'note' => $noteText,
                    ]);
                }
            }

            // Notify the patient about new diet plan
            try {
                $patient = \App\Models\Patient::find($validated['patient_id']);
                if ($patient && $patient->user) {
                    $patient->user->notify(new \App\Notifications\DietPlanAssigned($dietPlan));
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send diet plan notification: ' . $e->getMessage());
            }

            return (new DietPlanResource($dietPlan->load(['meals', 'doctor', 'patient'])))
                ->response()
                ->setStatusCode(201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(DietPlan $dietPlan)
    {
        return new DietPlanResource($dietPlan->load(['doctor', 'patient', 'meals']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DietPlan $dietPlan)
    {
        $validated = $request->validate([
            'patient_id' => 'exists:patients,id',
            'doctor_id' => 'exists:doctors,id',
            'title' => 'string',
            'description' => 'nullable|string',
            'daily_calories' => 'integer',
            'duration_days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => 'in:active,completed,cancelled',
        ]);

        $dietPlan->update($validated);

        return new DietPlanResource($dietPlan->load(['doctor', 'patient', 'meals']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DietPlan $dietPlan)
    {
        $dietPlan->delete();
        return response()->json(['message' => 'Diet plan deleted']);
    }
}
