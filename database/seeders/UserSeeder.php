<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin - use updateOrCreate to avoid unique constraint violations
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // Doctors - check if we already have enough doctors
        if (\App\Models\User::role('doctor')->count() < 10) {
            \App\Models\User::factory(10)->create()->each(function ($user) {
                $user->assignRole('doctor');
            });
        }

        // Patients - check if we already have enough patients
        if (\App\Models\User::role('patient')->count() < 50) {
            \App\Models\User::factory(50)->create()->each(function ($user) {
                $user->assignRole('patient');
            });
        }
    }
}
