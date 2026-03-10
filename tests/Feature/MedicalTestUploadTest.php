<?php

use App\Models\User;
use App\Models\MedicalTest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedicalTestUploadTest extends TestCase
{
    public function test_medical_test_image_upload()
    {
        // Mock storage
        Storage::fake('public');

        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Prepare image
        $file = UploadedFile::fake()->image('test_image.jpg');

        // Send request
        $response = $this->postJson('/api/medical-tests', [
            'name' => 'Test Medical Exam',
            'user_id' => $user->id,
            'image' => $file,
        ]);

        // Assertions
        $response->assertStatus(201);
        $data = $response->json();

        $this->assertNotNull($data['image']);
        $this->assertStringContainsString('uploads/medical-tests/', $data['image']);

        // Verify file exists in public directory
        $filePath = public_path($data['image']);
        // Since we are using ->move in controller, we check public_path
        $this->assertTrue(file_exists($filePath), "File should exist at: " . $filePath);

        // Cleanup
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_medical_test_string_image_support()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/medical-tests', [
            'name' => 'Test Medical Exam String',
            'user_id' => $user->id,
            'image' => 'http://example.com/image.jpg',
        ]);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertEquals('http://example.com/image.jpg', $data['image']);
    }
}
