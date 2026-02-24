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

        foreach ($patients as $patient) {
            // Consultations
            \App\Models\Consultation::factory(2)->create([
                'patient_id' => $patient->id,
                'doctor_id' => $patient->current_doctor_id,
            ]);

            // Messages
            \App\Models\Message::factory(10)->create([
                'sender_id' => $patient->user_id,
                'receiver_id' => $patient->doctor->user_id,
                'sender_type' => 'user',
            ]);

            \App\Models\Message::factory(5)->create([
                'sender_id' => $patient->doctor->user_id,
                'receiver_id' => $patient->user_id,
                'sender_type' => 'doctor',
            ]);
        }
    }
}
