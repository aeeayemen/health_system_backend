<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;

class DashboardTest extends TestCase
{
    public function test_dashboard_stats_endpoint()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $doctorUser = User::factory()->create();
        $doctor = \App\Models\Doctor::create([
            'user_id' => $doctorUser->id,
            'name' => 'Dr. Test',
            'specialization' => 'General',
            'license_number' => 'LIC-' . rand(1000, 9999),
        ]);

        $patient = \App\Models\Patient::create([
            'id' => $user->id,
            'user_id' => $user->id,
            'gender' => 'male',
        ]);

        $subscription = \App\Models\Subscription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'plan_type' => 'basic',
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'price' => 100
        ]);

        // Create some dummy data to ensure queries run
        Invoice::create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-TEST-001',
            'amount' => 100,
            'tax' => 0,
            'total_amount' => 100,
            'payment_status' => 'paid',
            'due_date' => now(),
            'payment_date' => now(),
        ]);

        $response = $this->getJson('/api/dashboard/stats?period=all');

        $response->assertStatus(200);
    }
}
