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
        // Admin
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Doctors
        \App\Models\User::factory(10)->create()->each(function ($user) {
            $user->assignRole('doctor');
        });

        // Patients
        \App\Models\User::factory(50)->create()->each(function ($user) {
            $user->assignRole('patient');
        });
    }
}
