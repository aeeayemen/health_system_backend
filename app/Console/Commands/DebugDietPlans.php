<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DietPlan;
use App\Models\Diet;
use App\Models\Doctor;

class DebugDietPlans extends Command
{
    protected $signature = 'debug:diet-plans {user_id?}';
    protected $description = 'Debug diets and diet plans';

    public function handle()
    {
        $userId = $this->argument('user_id');

        $this->info("Total Diets (Old System): " . Diet::count());
        $this->info("Total DietPlans (New System): " . DietPlan::count());

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User $userId not found!");
            } else {
                $this->info("User Info: ID={$user->id}, Name={$user->name}, Type={$user->type}");
                $doctor = Doctor::where('user_id', $user->id)->first();
                if ($doctor) {
                    $this->info("Doctor Profile: ID={$doctor->id}");
                    $plans = DietPlan::where('doctor_id', $doctor->id)->get();
                    $this->info("Plans for this doctor: " . $plans->count());
                    $diets = Diet::where('doctor_id', $doctor->id)->get();
                    $this->info("Diets for this doctor: " . $diets->count());
                }
            }
        }

        $this->info("\n--- Recent Diets ---");
        foreach (Diet::orderBy('id', 'desc')->take(5)->get() as $d) {
            $this->line("Diet ID: {$d->id}, Doctor ID: {$d->doctor_id}, Created: {$d->created_at}");
        }

        $this->info("\n--- Recent DietPlans ---");
        foreach (DietPlan::orderBy('id', 'desc')->take(5)->get() as $dp) {
            $this->line("Plan ID: {$dp->id}, Doctor ID: {$dp->doctor_id}, Patient ID: {$dp->patient_id}, Title: {$dp->title}");
        }
    }
}
