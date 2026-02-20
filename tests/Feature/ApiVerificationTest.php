<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Forum;
use App\Models\Diet;
use App\Models\Message;

class ApiVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $patientUser;
    protected $doctorUser;
    protected $forum;
    protected $doctor;
    protected $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling(); // Uncomment to debug

        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);

        // Setup Patient
        $this->patientUser = User::factory()->create();
        $this->patientUser->assignRole('patient');
        $this->patient = Patient::create([
            'id' => $this->patientUser->id,
            'user_id' => $this->patientUser->id
        ]);

        // Setup Doctor
        $this->doctorUser = User::factory()->create();
        $this->doctorUser->assignRole('doctor');
        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'name' => $this->doctorUser->name,
            'specialization' => 'General',
            'status' => 'approved'
        ]);

        // Setup Forum
        $this->forum = Forum::create([
            'name' => 'General Health',
            'doctor_id' => $this->doctor->id
        ]);
    }

    public function test_patient_can_publish_post()
    {
        $response = $this->actingAs($this->patientUser)
            ->postJson("/api/forums/{$this->forum->id}/posts", [
                'title' => 'My First Post',
                'content' => 'This is a test post content.'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'My First Post');
    }

    public function test_patient_can_request_session_consultation()
    {
        $response = $this->actingAs($this->patientUser)
            ->postJson('/api/consultations', [
                'doctor_id' => $this->doctor->id,
                'consultation_type' => 'initial',
                'scheduled_date' => now()->addDay()->format('Y-m-d H:i:s'),
                'notes' => 'I need advice.'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'pending');
    }

    public function test_doctor_can_create_diet()
    {
        // For DietPlanController (Diets/Diet Plans)
        $response = $this->actingAs($this->doctorUser)
            ->postJson('/api/diet-plans', [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'title' => 'Weight Loss Plan',
                'daily_calories' => 2000,
                'duration_days' => 30,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'meals' => [
                    [
                        'day_number' => 1,
                        'meal_type' => 'breakfast',
                        'meal_name' => 'Oats',
                        'calories' => 300
                    ]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Weight Loss Plan');
    }

    public function test_patient_can_get_my_diet()
    {
        // Manually create a diet first (legacy Diet model)
        $diet = Diet::create([
            'doctor_id' => $this->doctor->id,
            'user_id' => $this->patientUser->id,
            'status' => 'active'
        ]);

        // Link via subscription normally, but assuming direct relation or checks
        // The code checks: where('user_id', $user->id) or whereHas subscription

        $response = $this->actingAs($this->patientUser)
            ->getJson('/api/my-diet');

        $response->assertStatus(200)
            ->assertJsonPath('id', $diet->id);
    }
}
