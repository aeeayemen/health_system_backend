<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = \App\Models\Patient::all();

        $fallbackDoctorId = \App\Models\Doctor::inRandomOrder()->first()?->id;

        foreach ($patients as $patient) {
            $doctorIdForConsultation = $patient->current_doctor_id ?? $fallbackDoctorId;

            // Consultations - only create if none exist for this patient
            if ($patient->consultations()->count() == 0 && $doctorIdForConsultation) {
                \App\Models\Consultation::factory(2)->create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctorIdForConsultation,
                ]);
            }

            // Messages - only create if none exist between this patient and their doctor
            $doctorId = $patient->current_doctor_id;
            $hasMessages = \App\Models\Message::where('user_id', $patient->user_id)
                ->where('doctor_id', $doctorId)
                ->exists();

            if (!$hasMessages && $doctorId) {
                \App\Models\Message::factory(10)->create([
                    'user_id' => $patient->user_id,
                    'doctor_id' => $doctorId,
                    'sender_type' => 'user',
                ]);

                \App\Models\Message::factory(5)->create([
                    'user_id' => $patient->user_id,
                    'doctor_id' => $doctorId,
                    'sender_type' => 'doctor',
                ]);
            }
        }
    }
}
