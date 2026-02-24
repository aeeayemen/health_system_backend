<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DietPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = \App\Models\Patient::all();

        foreach ($patients as $patient) {
            // Only create if patient doesn't have a diet plan yet
            if (!$patient->dietPlans()->exists()) {
                $dietPlan = \App\Models\DietPlan::factory()->create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $patient->current_doctor_id,
                ]);

                \App\Models\Meal::factory(21)->create([
                    'diet_plan_id' => $dietPlan->id,
                ]);
            }
        }
    }
}
