<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::role('admin')->first();
        $doctorUsers = \App\Models\User::role('doctor')->get();

        foreach ($doctorUsers as $user) {
            \App\Models\Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'admin_id' => $admin->id,
                ]
            );
        }
    }
}
