<?php

namespace App\Http\Controllers;

use App\Models\MainCalculation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Health Calculations Controller - BMI, BMR, Calories
 */
class CalculationController extends Controller
{
    /**
     * Calculate BMI (Body Mass Index)
     * Formula: weight (kg) / height (m)²
     */
    public function calculateBMI(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1', // in cm
            'save' => 'boolean'
        ]);

        $heightInMeters = $validated['height'] / 100;
        $bmi = $validated['weight'] / ($heightInMeters * $heightInMeters);
        $bmi = round($bmi, 2);

        // Determine BMI category
        $category = $this->getBMICategory($bmi);

        $result = [
            'bmi' => $bmi,
            'category' => $category,
            'weight' => $validated['weight'],
            'height' => $validated['height']
        ];

        // Optionally save to history
        if ($request->input('save', false)) {
            $this->saveCalculation('bmi', $result);
        }

        return response()->json($result);
    }

    /**
     * Calculate BMR (Basal Metabolic Rate)
     * Mifflin-St Jeor Equation
     */
    public function calculateBMR(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1', // in cm
            'age' => 'required|integer|min:1',
            'gender' => 'required|in:male,female',
            'save' => 'boolean'
        ]);

        // Mifflin-St Jeor Equation
        if ($validated['gender'] === 'male') {
            $bmr = (10 * $validated['weight']) + (6.25 * $validated['height']) - (5 * $validated['age']) + 5;
        } else {
            $bmr = (10 * $validated['weight']) + (6.25 * $validated['height']) - (5 * $validated['age']) - 161;
        }

        $bmr = round($bmr, 2);

        $result = [
            'bmr' => $bmr,
            'weight' => $validated['weight'],
            'height' => $validated['height'],
            'age' => $validated['age'],
            'gender' => $validated['gender']
        ];

        if ($request->input('save', false)) {
            $this->saveCalculation('bmr', $result);
        }

        return response()->json($result);
    }

    /**
     * Calculate Daily Calorie Needs
     * Based on BMR and activity level
     */
    public function calculateCalories(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'age' => 'required|integer|min:1',
            'gender' => 'required|in:male,female',
            'activity_level' => 'required|in:sedentary,light,moderate,active,very_active',
            'goal' => 'nullable|in:maintain,lose,gain',
            'save' => 'boolean'
        ]);

        // Calculate BMR first
        if ($validated['gender'] === 'male') {
            $bmr = (10 * $validated['weight']) + (6.25 * $validated['height']) - (5 * $validated['age']) + 5;
        } else {
            $bmr = (10 * $validated['weight']) + (6.25 * $validated['height']) - (5 * $validated['age']) - 161;
        }

        // Activity multipliers
        $multipliers = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9
        ];

        $tdee = $bmr * $multipliers[$validated['activity_level']];

        // Adjust for goal
        $goal = $validated['goal'] ?? 'maintain';
        $targetCalories = $tdee;
        if ($goal === 'lose') {
            $targetCalories = $tdee - 500; // 0.5 kg/week loss
        } elseif ($goal === 'gain') {
            $targetCalories = $tdee + 500; // 0.5 kg/week gain
        }

        $result = [
            'bmr' => round($bmr, 2),
            'tdee' => round($tdee, 2),
            'target_calories' => round($targetCalories, 2),
            'activity_level' => $validated['activity_level'],
            'goal' => $goal,
            'macros' => [
                'protein' => round($targetCalories * 0.3 / 4, 0), // 30% protein
                'carbs' => round($targetCalories * 0.4 / 4, 0),   // 40% carbs
                'fat' => round($targetCalories * 0.3 / 9, 0)      // 30% fat
            ]
        ];

        if ($request->input('save', false)) {
            $this->saveCalculation('calories', $result);
        }

        return response()->json($result);
    }

    /**
     * Get calculation history for current user
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $query = MainCalculation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('type')) {
            $query->where('calculation_type', $request->type);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Get BMI category
     */
    private function getBMICategory($bmi)
    {
        if ($bmi < 18.5)
            return 'underweight';
        if ($bmi < 25)
            return 'normal';
        if ($bmi < 30)
            return 'overweight';
        return 'obese';
    }

    /**
     * Save calculation to history
     */
    private function saveCalculation($type, $data)
    {
        $user = Auth::user();
        if (!$user)
            return;

        MainCalculation::create([
            'user_id' => $user->id,
            'calculation_type' => $type,
            'bmi' => $data['bmi'] ?? null,
            'bmr' => $data['bmr'] ?? null,
            'calories' => $data['target_calories'] ?? null,
            'weight' => $data['weight'] ?? null,
            'height' => $data['height'] ?? null,
        ]);
    }
}
