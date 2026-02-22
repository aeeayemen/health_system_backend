<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($roleAdmin);

        // Create Doctor User
        $doctorUser = \App\Models\User::factory()->create([
            'name' => 'Dr. Smith',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
        ]);
        $doctorUser->assignRole($roleDoctor);

        // Create Doctor Profile
        $doctor = \App\Models\Doctor::create([
            'user_id' => $doctorUser->id,
            'name' => 'Dr. Smith',
            'gender' => 'Male',
            'degree' => 'PhD in Nutrition',
            'bank_account' => '123-456-789',
            'phone_number' => '1234567890',
            'CV' => 'path/to/cv.pdf',
            'admin_id' => $admin->id,
        ]);

        // Create Patient User
        $patientUser = \App\Models\User::factory()->create([
            'name' => 'John Doe',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
        ]);
        $patientUser->assignRole($rolePatient);

        // Create Patient Profile (SubscribedUser)
        // Note: Patient model is mapped to subscribed_users table now.
        $patient = \App\Models\Patient::create([
            'id' => $patientUser->id, // PK is FK to users.id
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
        ]);

        // Create Diet Plan
        $dietPlan = \App\Models\DietPlan::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'title' => 'Weight Loss Plan',
            'description' => 'A balanced diet for weight loss.',
            'daily_calories' => 2000,
            'duration_days' => 30,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // Create Measurement
        \App\Models\Measurement::create([
            'patient_id' => $patient->id,
            'weight' => 85.5,
            'measurement_date' => now(),
        ]);

        // Create Consultation
        \App\Models\Consultation::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'consultation_type' => 'initial',
            'scheduled_date' => now()->addDays(2),
            'status' => 'pending',
            'notes' => 'Initial consultation',
        ]);

        // Create Subscription
        $subscription = \App\Models\Subscription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'plan_type' => 'basic',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'price' => 100.00,
            'status' => 'active',
        ]);

        // ... (other seeders) ...

        // Create Invoice
        \App\Models\Invoice::create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-' . time(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'payment_status' => 'paid',
            'due_date' => now()->addDays(7),
        ]);

        // === NEW SEEDERS (Restored) ===

        // Create Tip
        \App\Models\Tip::create([
            'describtion' => 'Drink plenty of water.',
            'admin_id' => $admin->id,
            'date' => now()->toDateString(),
        ]);

        // Create Diet (ERD Table)
        $diet = \App\Models\Diet::create([
            'price' => '50',
            'doctor_id' => $doctor->id,
            'periods' => 'Morning, Evening',
            'states_id' => 1,
        ]);

        // Create DietNote
        \App\Models\DietNote::create([
            'user_id' => $patientUser->id,
            'doctor_id' => $doctor->id,
            'note' => 'Avoid sugar.',
        ]);

        // Create DietComponent
        \App\Models\DietComponent::create([
            'periods_time' => '8:00 AM',
            'period_name' => 'Breakfast',
            'doctor_id' => $doctor->id,
            'diet_id' => $diet->id,
        ]);

        // Create WeeklyCalculation
        \App\Models\WeeklyCalculation::create([
            'waist' => '80',
            'stomach' => '85',
            'arm' => '30',
            'chest' => '95',
            'thigh' => '55',
            'shoulder' => '110',
            'buttocks' => '100',
            'user_id' => $patientUser->id,
        ]);

        // Create Rate
        \App\Models\Rate::create([
            'user_id' => $patientUser->id,
            'doctor_id' => $doctor->id,
            'rate' => '5',
        ]);

        // Create Forum
        $forum = \App\Models\Forum::create([
            'name' => 'Healthy Living',
            'doctor_id' => $doctor->id,
        ]);

        // Create ForumMember
        \App\Models\ForumMember::create([
            'user_id' => $patientUser->id,
            'forum_id' => $forum->id,
        ]);

        // Create MedicalTest
        \App\Models\MedicalTest::create([
            'name' => 'Blood Test',
            'user_id' => $patientUser->id,
            'image' => 'path/to/image.jpg',
        ]);

        // Create Message
        \App\Models\Message::create([
            'user_id' => $patientUser->id,
            'doctor_id' => $doctor->id,
            'message' => 'Hello doctor, I have a question.',
            'time' => now()->toTimeString(),
            'date' => now()->toDateString(),
            'read' => 'false',
        ]);

        // Create Meal (ERD Table)
        \App\Models\Meal::create([
            'name' => 'Oatmeal',
            'serving' => '1 bowl',
            'describtion' => 'Healthy breakfast',
            'carbo' => '30',
            'protin' => '5',
            'fat' => '2',
            'energy' => '150',
            'category' => 'Breakfast',
        ]);

        // Create Advertisement
        \App\Models\Advertisement::create([
            'admin_id' => $admin->id,
            'date' => now()->toDateString(),
            'image' => 'ad.jpg',
            'describtion' => 'Best Gym in Town',
            'phone_number' => '123456789',
            'type' => 'Banner',
            'GPS' => 'Location',
        ]);
    }
}
