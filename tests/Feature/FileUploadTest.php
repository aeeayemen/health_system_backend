<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Advertisement;
use App\Models\Doctor;

class FileUploadTest extends TestCase
{
    use WithFaker;

    public function test_advertisement_image_upload()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('ad.jpg');

        $response = $this->postJson('/api/advertisements', [
            'describtion' => 'Test Ad',
            'image' => $file,
            'phone_number' => '1234567890',
            'type' => 'عرض',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('advertisements', ['describtion' => 'Test Ad']);

        $ad = Advertisement::where('describtion', 'Test Ad')->first();
        $this->assertStringContainsString('uploads/advertisements', $ad->image);
        $this->assertFileExists(public_path($ad->image));

        // Cleanup
        if (file_exists(public_path($ad->image))) {
            unlink(public_path($ad->image));
        }
        $ad->delete();
        $user->delete();
    }

    public function test_doctor_file_upload()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cv = UploadedFile::fake()->create('cv.pdf', 100);
        $profile = UploadedFile::fake()->image('profile.jpg');

        $response = $this->postJson('/api/doctors', [
            'user_id' => $user->id,
            'name' => 'Dr. Test',
            'specialization' => 'General',
            'license_number' => 'LIC-' . rand(1000, 9999),
            'CV' => $cv,
            'profile_image' => $profile,
        ]);

        $response->assertStatus(201);

        $doctor = Doctor::where('name', 'Dr. Test')->first();
        $this->assertNotNull($doctor->CV);
        $this->assertNotNull($doctor->profile_image);

        $this->assertFileExists(public_path($doctor->CV));
        $this->assertFileExists(public_path($doctor->profile_image));

        // Cleanup
        if ($doctor->CV && file_exists(public_path($doctor->CV)))
            unlink(public_path($doctor->CV));
        if ($doctor->profile_image && file_exists(public_path($doctor->profile_image)))
            unlink(public_path($doctor->profile_image));
        $doctor->delete();
        $user->delete();
    }

    public function test_medical_test_image_upload()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('test_result.jpg');

        $response = $this->postJson('/api/medical-tests', [
            'name' => 'Blood Test',
            'user_id' => $user->id,
            'image' => $image,
        ]);

        $response->assertStatus(201);

        $test = \App\Models\MedicalTest::where('name', 'Blood Test')->first();
        $this->assertNotNull($test->image);
        $this->assertFileExists(public_path($test->image));

        // Cleanup
        if ($test->image && file_exists(public_path($test->image)))
            unlink(public_path($test->image));
        $test->delete();
        $user->delete();
    }

    public function test_setting_logo_upload()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $logo = UploadedFile::fake()->image('logo.png');

        $response = $this->postJson('/api/settings', [
            'app_name' => 'Health App',
            'app_logo' => $logo,
        ]);

        $response->assertStatus(200);

        $setting = \App\Models\Setting::where('key', 'app_logo')->first();
        $this->assertNotNull($setting->value);
        $this->assertStringContainsString('uploads/settings', $setting->value);
        $this->assertFileExists(public_path($setting->value));

        // Cleanup
        if ($setting->value && file_exists(public_path($setting->value)))
            unlink(public_path($setting->value));
    }
}
