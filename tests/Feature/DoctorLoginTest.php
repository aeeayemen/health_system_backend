<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Doctor;

class DoctorLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_doctor_can_login()
    {
        $user = User::factory()->create(['type' => 'doctor', 'password' => bcrypt('password123')]);
        Doctor::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'application_status' => 'approved',
            'specialization' => 'General',
            'license_number' => 'APP-123'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_pending_doctor_can_login()
    {
        $user = User::factory()->create(['type' => 'doctor', 'password' => bcrypt('password123')]);
        Doctor::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'application_status' => 'pending',
            'specialization' => 'General',
            'license_number' => 'PEN-123'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $this->assertEquals('pending', $response->json('user.doctor.application_status'));
    }

    public function test_rejected_doctor_can_login()
    {
        $user = User::factory()->create(['type' => 'doctor', 'password' => bcrypt('password123')]);
        Doctor::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'application_status' => 'rejected',
            'specialization' => 'General',
            'license_number' => 'REJ-123'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $this->assertEquals('rejected', $response->json('user.doctor.application_status'));
    }
}
