<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patientUsers = \App\Models\User::role('patient')->get();
        $doctors = \App\Models\Doctor::all();

        foreach ($patientUsers as $user) {
            \App\Models\Patient::factory()->create([
                'id' => $user->id,
                'user_id' => $user->id,
                'fullname' => $user->name,
                'current_doctor_id' => $doctors->random()->id,
            ]);
        }
    }
}
