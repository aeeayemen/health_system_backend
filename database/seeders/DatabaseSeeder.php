<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles
// Create Roles safely
        $roleAdmin = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );
        $roleDoctor = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'doctor', 'guard_name' => 'web']
        );
        $rolePatient = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'patient', 'guard_name' => 'web']
        );

        // Create Admin User
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // Create Doctor User
        $doctorUser = \App\Models\User::updateOrCreate(
            ['email' => 'doctor@example.com'],
            [
                'name' => 'Dr. Smith',
                'password' => bcrypt('password'),
            ]
        );
        $doctorUser->assignRole($roleDoctor);

        // Create Doctor Profile
        $doctor = \App\Models\Doctor::updateOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'name' => 'Dr. Smith',
                'gender' => 'Male',
                'degree' => 'PhD in Nutrition',
                'bank_account' => '123-456-789',
                'phone_number' => '1234567890',
                'CV' => 'path/to/cv.pdf',
                'admin_id' => $admin->id,
            ]
        );

        // Create Patient User
        $patientUser = \App\Models\User::updateOrCreate(
            ['email' => 'patient@example.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('password'),
            ]
        );
        $patientUser->assignRole($rolePatient);

        // Create Patient Profile (SubscribedUser)
        $patient = \App\Models\Patient::updateOrCreate(
            ['id' => $patientUser->id],
            [
                'user_id' => $patientUser->id,
                'fullname' => 'John Doe',
                'gender' => 'Male',
                'height' => 175,
                'weight' => 85,
                'phone_number' => '0987654321',
                'image' => 'profile.jpg',
                'birthdate' => '1990-01-01',
                'physical_activity' => 'Moderate',
                'medical' => 'None',
            ]
        );

        // Create Diet Plan
        $dietPlan = \App\Models\DietPlan::updateOrCreate(
            ['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'title' => 'Weight Loss Plan'],
            [
                'description' => 'A balanced diet for weight loss.',
                'daily_calories' => 2000,
                'duration_days' => 30,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'status' => 'active',
            ]
        );

        // Create Measurement
        \App\Models\Measurement::firstOrCreate(
            ['patient_id' => $patient->id, 'measurement_date' => now()->toDateString()],
            [
                'weight' => 85.5,
            ]
        );

        // Create Consultation
        \App\Models\Consultation::firstOrCreate(
            ['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'consultation_type' => 'initial', 'scheduled_date' => now()->addDays(2)->toDateTimeString()],
            [
                'status' => 'pending',
                'notes' => 'Initial consultation',
            ]
        );

        // Create Subscription
        $subscription = \App\Models\Subscription::updateOrCreate(
            ['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'plan_type' => 'basic'],
            [
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'price' => 100.00,
                'status' => 'active',
            ]
        );

        // Create Invoice
        \App\Models\Invoice::firstOrCreate(
            ['subscription_id' => $subscription->id, 'amount' => 100.00],
            [
                'invoice_number' => 'INV-' . time(),
                'total_amount' => 100.00,
                'payment_status' => 'paid',
                'due_date' => now()->addDays(7),
            ]
        );

        // Create Tip
        \App\Models\Tip::firstOrCreate(
            ['describtion' => 'Drink plenty of water.', 'admin_id' => $admin->id],
            [
                'date' => now()->toDateString(),
            ]
        );

        // Create Diet (ERD Table)
        $diet = \App\Models\Diet::updateOrCreate(
            ['doctor_id' => $doctor->id, 'periods' => 'Morning, Evening'],
            [
                'price' => '50',
                'states_id' => 1,
            ]
        );

        // Create DietNote
        \App\Models\DietNote::firstOrCreate(
            ['user_id' => $patientUser->id, 'doctor_id' => $doctor->id, 'note' => 'Avoid sugar.'],
            []
        );

        // Create DietComponent
        \App\Models\DietComponent::firstOrCreate(
            ['diet_id' => $diet->id, 'period_name' => 'Breakfast'],
            [
                'periods_time' => '8:00 AM',
                'doctor_id' => $doctor->id,
            ]
        );

        // Create WeeklyCalculation
        \App\Models\WeeklyCalculation::firstOrCreate(
            ['user_id' => $patientUser->id, 'waist' => '80'],
            [
                'stomach' => '85',
                'arm' => '30',
                'chest' => '95',
                'thigh' => '55',
                'shoulder' => '110',
                'buttocks' => '100',
            ]
        );

        // Create Rate
        \App\Models\Rate::updateOrCreate(
            ['user_id' => $patientUser->id, 'doctor_id' => $doctor->id],
            [
                'rate' => '5',
            ]
        );

        // Create Forum
        $forum = \App\Models\Forum::firstOrCreate(
            ['name' => 'Healthy Living', 'doctor_id' => $doctor->id],
            []
        );

        // Create ForumMember
        \App\Models\ForumMember::firstOrCreate(
            ['user_id' => $patientUser->id, 'forum_id' => $forum->id],
            []
        );

        // Create MedicalTest
        \App\Models\MedicalTest::firstOrCreate(
            ['name' => 'Blood Test', 'user_id' => $patientUser->id],
            [
                'image' => 'path/to/image.jpg',
            ]
        );

        // Create Message
        \App\Models\Message::firstOrCreate(
            ['user_id' => $patientUser->id, 'doctor_id' => $doctor->id, 'message' => 'Hello doctor, I have a question.'],
            [
                'time' => now()->toTimeString(),
                'date' => now()->toDateString(),
                'read' => 'false',
            ]
        );

        // Create Meal (ERD Table)
        \App\Models\Meal::firstOrCreate(
            ['name' => 'Oatmeal', 'category' => 'Breakfast'],
            [
                'serving' => '1 bowl',
                'describtion' => 'Healthy breakfast',
                'carbo' => '30',
                'protin' => '5',
                'fat' => '2',
                'energy' => '150',
            ]
        );

        // Create Advertisement
        \App\Models\Advertisement::firstOrCreate(
            ['admin_id' => $admin->id, 'describtion' => 'Best Gym in Town'],
            [
                'date' => now()->toDateString(),
                'image' => 'ad.jpg',
                'phone_number' => '123456789',
                'type' => 'Banner',
                'GPS' => 'Location',
            ]
        );
    }
}
