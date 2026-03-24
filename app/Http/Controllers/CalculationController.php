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
     * Specialized Nutrition Calculation based on Physician Formulas
     * Includes BMR, Activity Level, TEF (10%), Goal (+/- 500), and Macros (50/20/30)
     */
    public function calculateNutrition(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'age' => 'required|integer|min:1',
            'gender' => 'required|in:male,female',
            'activity_level' => 'required|in:sedentary,low,moderate,active,very_active',
            'goal' => 'required|in:maintain,lose,gain',
            'save' => 'boolean'
        ]);

        // 1. Calculate BMR (Mifflin-St Jeor)
        if ($validated['gender'] === 'male') {
            $bmr = (10 * $validated['weight']) + (6.25 * $validated['height']) - (5 * $validated['age']) + 5;
        } else {
            $bmr = (10 * $validated['weight']) + (6.25 * $validated['height']) - (5 * $validated['age']) - 161;
        }

        // 2. Activity Multiplier
        $multipliers = [
            'sedentary' => 1.2,
            'low' => 1.3,
            'moderate' => 1.5,
            'active' => 1.7,
            'very_active' => 1.9,
        ];
        $activity_multiplier = $multipliers[$validated['activity_level']];
        $tdee_base = $bmr * $activity_multiplier;

        // 3. TEF (10%)
        $tef = $tdee_base * 0.10;
        $total_needs = $tdee_base + $tef;

        // 4. Goal Adjustment
        $target_calories = $total_needs;
        if ($validated['goal'] === 'lose') {
            $target_calories -= 500;
        } elseif ($validated['goal'] === 'gain') {
            $target_calories += 500;
        }

        // 5. BMI
        $heightInMeters = $validated['height'] / 100;
        $bmi = $validated['weight'] / ($heightInMeters * $heightInMeters);

        // 6. Macros (50% Carbs, 20% Protein, 30% Fat)
        $macros = [
            'carbs_g' => round(($target_calories * 0.50) / 4, 1),
            'protein_g' => round(($target_calories * 0.20) / 4, 1),
            'fat_g' => round(($target_calories * 0.30) / 9, 1),
        ];

        $result = [
            'bmi' => round($bmi, 2),
            'bmr' => round($bmr, 2),
            'total_calories' => round($target_calories, 0),
            'macros' => $macros,
            'inputs' => $validated
        ];

        // Save to history if logged in
        if ($request->input('save', false) && Auth::check()) {
            MainCalculation::create([
                'user_id' => Auth::id(),
                'calories' => (string) round($target_calories, 0),
                'protin' => (string) $macros['protein_g'],
                'fat' => (string) $macros['fat_g'],
                'carbo' => (string) $macros['carbs_g'],
                'BMR' => (string) round($bmr, 2),
                'BMI' => (string) round($bmi, 2),
            ]);
        }

        return response()->json($result);
    }

    /**
     * Calculate diet macros using the Exchange List System (جدول الحصص)
     *
     * The doctor enters the number of servings from each food group.
     * The system calculates total carbs, protein, fat, calories, and macro percentages.
     *
     * Standard Exchange Values (per serving):
     * ┌────────────┬────────┬─────────┬──────┐
     * │ Group      │ Carbs  │ Protein │ Fat  │
     * ├────────────┼────────┼─────────┼──────┤
     * │ Fruits     │ 15g    │ 0g      │ 0g   │
     * │ Milk       │ 12g    │ 8g      │ 5g   │
     * │ Vegetables │ 5g     │ 2g      │ 0g   │
     * │ Starch     │ 15g    │ 3g      │ 0g   │
     * │ Protein    │ 0g     │ 7g      │ 5g   │
     * │ Fat        │ 0g     │ 0g      │ 5g   │
     * └────────────┴────────┴─────────┴──────┘
     */
    public function calculateExchangeList(Request $request)
    {
        $validated = $request->validate([
            'fruits_servings' => 'required|numeric|min:0',
            'milk_servings' => 'required|numeric|min:0',
            'vegetables_servings' => 'required|numeric|min:0',
            'starch_servings' => 'required|numeric|min:0',
            'protein_servings' => 'required|numeric|min:0',
            'fat_servings' => 'required|numeric|min:0',
        ]);

        // ── Standard Exchange Values per serving ────────────────────────────
        $exchangeTable = [
            'fruits' => ['carbs' => 15, 'protein' => 0, 'fat' => 0],
            'milk' => ['carbs' => 12, 'protein' => 8, 'fat' => 5],
            'vegetables' => ['carbs' => 5, 'protein' => 2, 'fat' => 0],
            'starch' => ['carbs' => 15, 'protein' => 3, 'fat' => 0],
            'protein' => ['carbs' => 0, 'protein' => 7, 'fat' => 5],
            'fat' => ['carbs' => 0, 'protein' => 0, 'fat' => 5],
        ];

        $servings = [
            'fruits' => $validated['fruits_servings'],
            'milk' => $validated['milk_servings'],
            'vegetables' => $validated['vegetables_servings'],
            'starch' => $validated['starch_servings'],
            'protein' => $validated['protein_servings'],
            'fat' => $validated['fat_servings'],
        ];

        // ── Step 1: Calculate macros per group ──────────────────────────────
        $breakdown = [];
        $totalCarbs = 0;
        $totalProtein = 0;
        $totalFat = 0;

        foreach ($exchangeTable as $group => $values) {
            $s = $servings[$group];
            $carbs = $s * $values['carbs'];
            $protein = $s * $values['protein'];
            $fat = $s * $values['fat'];

            $breakdown[$group] = [
                'servings' => $s,
                'carbs_g' => $carbs,
                'protein_g' => $protein,
                'fat_g' => $fat,
            ];

            $totalCarbs += $carbs;
            $totalProtein += $protein;
            $totalFat += $fat;
        }

        // ── Step 2: Calories from macros ────────────────────────────────────
        $caloriesFromCarbs = $totalCarbs * 4;
        $caloriesFromProtein = $totalProtein * 4;
        $caloriesFromFat = $totalFat * 9;
        $totalCalories = $caloriesFromCarbs + $caloriesFromProtein + $caloriesFromFat;

        // ── Step 3: Macro percentages ────────────────────────────────────────
        $carbsPercent = $totalCalories > 0 ? round(($caloriesFromCarbs / $totalCalories) * 100, 1) : 0;
        $proteinPercent = $totalCalories > 0 ? round(($caloriesFromProtein / $totalCalories) * 100, 1) : 0;
        $fatPercent = $totalCalories > 0 ? round(($caloriesFromFat / $totalCalories) * 100, 1) : 0;

        // ── Step 4: Serving equivalents (as described by client) ─────────────
        // Free groups carbs (fruits + milk + vegetables) ÷ 15 → carb servings needed
        $freeGroupCarbs = $breakdown['fruits']['carbs_g']
            + $breakdown['milk']['carbs_g']
            + $breakdown['vegetables']['carbs_g'];

        $carbServingsFromFreeGroups = round($freeGroupCarbs / 15, 2);

        // Free groups protein (fruits + vegetables) ÷ 2
        $proteinFromFreeGroups = round(
            ($breakdown['fruits']['protein_g'] + $breakdown['vegetables']['protein_g']) / 2,
            2
        );

        // Fat servings ÷ 5
        $fatServingsEquivalent = round($totalFat / 5, 2);

        return response()->json([
            'summary' => [
                'total_carbs_g' => round($totalCarbs, 1),
                'total_protein_g' => round($totalProtein, 1),
                'total_fat_g' => round($totalFat, 1),
                'total_calories' => round($totalCalories, 0),
                'carbs_percent' => $carbsPercent,
                'protein_percent' => $proteinPercent,
                'fat_percent' => $fatPercent,
            ],
            'servings_equivalents' => [
                'carb_servings_from_free_groups' => $carbServingsFromFreeGroups,
                'protein_adjustment_from_free_groups' => $proteinFromFreeGroups,
                'fat_servings_equivalent' => $fatServingsEquivalent,
            ],
            'breakdown_per_group' => $breakdown,
            'inputs' => $validated,
        ]);
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
